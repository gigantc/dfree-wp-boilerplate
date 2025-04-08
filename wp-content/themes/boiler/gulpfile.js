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
// BROWSER SYNC RELOAD
function reload(done) {
  browserSync.reload();
  done();
}


//////////////////////////////////////
// WATCH TASK
function watchTask() {
  browserSync.init({
    proxy: 'dfreeboilerplate.local'
  });
  watch(files.scssPath, scssTask);
  watch(files.libsPath, libsTask);
  watch(files.scriptsPath, scriptsTask);
  watch('**/*.php', reload); // Reload when PHP files change
}

//////////////////////////////////////
// EXPORT TASKS
exports.default = series(
  parallel(scssTask, libsTask, scriptsTask),
  watchTask
);

exports.build = series(
  clean,
  parallel(scssTask, libsTask, scriptsTask)
);