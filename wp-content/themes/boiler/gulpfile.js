const { src, dest, watch, series, parallel } = require('gulp');
const gulp = require('gulp');


/* PLUGINS */
const sass = require('gulp-sass'),
    browserSync = require("browser-sync").create(),
    postcss = require("gulp-postcss"),
    sourcemaps = require('gulp-sourcemaps'),
    cssnano = require("cssnano"),
    autoprefixer = require("autoprefixer"),
    concat = require('gulp-concat'),
    terser = require('gulp-terser');
    plumber = require('gulp-plumber');


/* FILE PATHS */
const files = { 
    scssPath: 'src/scss/*.scss',
    scriptsPath: 'src/js/*.js',
    libsPath: 'src/js/libs/*.js',
    blocksPath: 'src/scss/blocks/*.scss'
}

/* STYLES */
function scssTask(){    
    return src([files.scssPath])
    .pipe(plumber({
      errorHandler: function (error) {
        console.log(error.message);
        this.emit('end');
    }}))
    .pipe(sass())
    .pipe(postcss([autoprefixer(), cssnano()]))
    .pipe(gulp.dest('css/'))
    .pipe(browserSync.stream());
}

function blocksScssTask(){    
    return src([files.blocksPath])
    .pipe(plumber({
      errorHandler: function (error) {
        console.log(error.message);
        this.emit('end');
    }}))
    .pipe(concat('_blocks_combined.scss'))
    .pipe(gulp.dest('src/scss/'))
}


/* SCRIPTS */
function libsTask(){    
    return src([files.libsPath])
    .pipe(concat('libs.min.js'))
    .pipe(terser())
    .pipe(gulp.dest('js/libs/'));
}


function scriptsTask(){    
    return src([files.scriptsPath])
    .pipe(plumber({
      errorHandler: function (error) {
        console.log(error.message);
        this.emit('end');
    }}))
    .pipe(concat('main.min.js'))
    .pipe(terser())
    .pipe(gulp.dest('js/'))
    .pipe(browserSync.stream());
}


/* BROWSER SYNC */
function reload(done) {
  browserSync.reload();
  done();
}


/* WATCH */
function watchTask(){
    browserSync.init({
        proxy: "playground.test"
    });
    watch(
        [files.blocksPath],
        parallel(blocksScssTask)
    );
    watch(
        [files.scssPath, files.scriptsPath, files.libsPath],
        parallel(scssTask, libsTask, scriptsTask)
    );
}

/* DEFAULT TASK */
exports.default = series(
    parallel(blocksScssTask, scssTask, libsTask, scriptsTask), 
    watchTask);
