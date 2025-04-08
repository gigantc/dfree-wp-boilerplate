const { src, dest, watch, series, parallel } = require('gulp');
const sass = require('gulp-sass')(require('sass')),
    browserSync = require('browser-sync').create(),
    postcss = require('gulp-postcss'),
    sourcemaps = require('gulp-sourcemaps'),
    babel = require('gulp-babel'),
    cssnano = require('cssnano'),
    autoprefixer = require('autoprefixer'),
    concat = require('gulp-concat'),
    terser = require('gulp-terser'),
    plumber = require('gulp-plumber'),
    del = require('del');

//for auto updating blocks.scss
const fs = require('fs');
const path = require('path');



//////////////////////////////////////
// FILE PATHS
const files = {
  scssPath: ['src/scss/**/*.scss', 'blocks/**/*.scss'],
  scriptsPath: 'src/js/*.js',
  libsPath: 'src/js/libs/*.js'
};


//////////////////////////////////////
// BABEL CONFIG
const babelConfig = {
  presets: ['@babel/env']
};
/* CLEAN TASK */
function clean() {
  return del(['css/*', 'js/*']);
}


//////////////////////////////////////
// STYLES TASK
function scssTask() {
  return src('src/scss/main.scss', { sourcemaps: true })
    .pipe(plumber())
    .pipe(sass({ includePaths: ['src/scss'] }))
    .pipe(postcss([autoprefixer(), cssnano()]))
    .pipe(dest('css', { sourcemaps: '.' }))
    .pipe(browserSync.stream());
}


//////////////////////////////////////
// LIBS TASK
function libsTask() {
  return src(files.libsPath)
    .pipe(sourcemaps.init())
    .pipe(babel(babelConfig))
    .pipe(concat('libs.min.js'))
    .pipe(terser())
    .pipe(sourcemaps.write('.'))
    .pipe(dest('js/libs/'));
}


//////////////////////////////////////
// SCRIPTS TASK
function scriptsTask() {
  return src(files.scriptsPath)
    .pipe(plumber())
    .pipe(sourcemaps.init())
    .pipe(babel(babelConfig))
    .pipe(concat('main.min.js'))
    .pipe(terser())
    .pipe(sourcemaps.write('.'))
    .pipe(dest('js/'))
    .pipe(browserSync.stream());
}


//////////////////////////////////////
// BLOCK.SCSS CREATION TASK
// this will auto generate @forwards in _block.scss
function generateBlocksScssTask(done) {
  const blocksDir = path.join(__dirname, 'blocks');
  const outputFile = path.join(__dirname, 'src/scss/_blocks.scss');

  function findScssPartials(dir) {
    let forwards = [];

    fs.readdirSync(dir, { withFileTypes: true }).forEach((entry) => {
      const entryPath = path.join(dir, entry.name);
      if (entry.isDirectory()) {
        forwards.push(...findScssPartials(entryPath));
      } else if (
        entry.isFile() &&
        entry.name.startsWith('_') &&
        entry.name.endsWith('.scss')
      ) {
        const relative = path.relative(path.join(__dirname, 'src/scss'), entryPath);
        const cleaned = relative
          .replace(/^_/, '')         // Remove underscore
          .replace(/\.scss$/, '')    // Remove .scss
          .replace(/\\/g, '/');      // Normalize Windows slashes

        forwards.push(`@forward '${cleaned}';`);
      }
    });

    return forwards;
  }

  const lines = findScssPartials(blocksDir);
  const headerComment = `// This file is auto-generated by Gulp.
// Do not edit this file directly—any changes will be overwritten.\n\n`;
fs.writeFileSync(outputFile, headerComment + lines.join('\n') + '\n');
  console.log(`🤖 Updated _blocks.scss with ${lines.length} entries. You're Welcome.`);
  done();
}



//////////////////////////////////////
// BROWSER SYNC RELOAD
function reload(done) {
  browserSync.reload();
  done();
}


//////////////////////////////////////
// WATCH TASK
//change the proxy to your local domain
function watchTask() {
  browserSync.init({
    proxy: 'dfreeboilerplate.local'
  });
  watch('blocks/**/*.scss', series(generateBlocksScssTask, scssTask));
  watch(files.scssPath, scssTask);
  watch(files.libsPath, libsTask);
  watch(files.scriptsPath, scriptsTask);
  // Reload when PHP files change
  watch('**/*.php', reload); 
}

//////////////////////////////////////
// EXPORT TASKS
exports.default = series(
  generateBlocksScssTask,
  parallel(scssTask, libsTask, scriptsTask),
  watchTask
);

exports.build = series(
  clean,
  generateBlocksScssTask,
  parallel(scssTask, libsTask, scriptsTask)
);



