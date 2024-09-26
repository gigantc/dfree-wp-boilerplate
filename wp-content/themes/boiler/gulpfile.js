const { src, dest, watch, series, parallel } = require('gulp');

/* PLUGINS */
const sass = require('gulp-sass')(require('sass')),
    browserSync = require("browser-sync").create(),
    postcss = require("gulp-postcss"),
    sourcemaps = require('gulp-sourcemaps'),
    babel = require('gulp-babel'),
    cssnano = require("cssnano"),
    autoprefixer = require("autoprefixer"),
    concat = require('gulp-concat'),
    terser = require('gulp-terser'),
    plumber = require('gulp-plumber');

/* FILE PATHS */
const files = { 
    scssPath: 'src/scss/*.scss',
    scriptsPath: 'src/js/*.js',
    libsPath: 'src/js/libs/*.js',
    blocksPath: 'src/scss/blocks/*.scss'
};

/* STYLES TASK */
function scssTask() {    
    return src(files.scssPath)
        .pipe(plumber({
            errorHandler: function (error) {
                console.log(error.message);
                this.emit('end');
            }
        }))
        .pipe(sourcemaps.init())
        .pipe(sass())
        .pipe(postcss([autoprefixer(), cssnano()]))
        .pipe(sourcemaps.write('.'))
        .pipe(dest('css/'))
        .pipe(browserSync.stream());
}

/* BLOCKS SCSS TASK */
function blocksScssTask() {    
    return src(files.blocksPath)
        .pipe(plumber({
            errorHandler: function (error) {
                console.log(error.message);
                this.emit('end');
            }
        }))
        .pipe(concat('_blocks_combined.scss'))
        .pipe(dest('src/scss/'));
}

/* LIBS TASK */
function libsTask() {    
    return src(files.libsPath)
        .pipe(sourcemaps.init())
        .pipe(babel({
            presets: ['@babel/env']
        }))
        .pipe(concat('libs.min.js'))
        .pipe(terser())
        .pipe(sourcemaps.write('.'))
        .pipe(dest('js/libs/'));
}

/* SCRIPTS TASK */
function scriptsTask() {    
    return src(files.scriptsPath)
        .pipe(plumber({
            errorHandler: function (error) {
                console.log(error.message);
                this.emit('end');
            }
        }))
        .pipe(sourcemaps.init())
        .pipe(babel({
            presets: ['@babel/env']
        }))
        .pipe(concat('main.min.js'))
        .pipe(terser())
        .pipe(sourcemaps.write('.'))
        .pipe(dest('js/'))
        .pipe(browserSync.stream());
}

/* BROWSER SYNC TASK */
function reload(done) {
    browserSync.reload();
    done();
}

/* WATCH TASK */
function watchTask() {
    browserSync.init({
        proxy: "boiler.local"
    });
    watch(files.blocksPath, blocksScssTask);
    watch(files.scssPath, scssTask);
    watch(files.libsPath, libsTask);
    watch(files.scriptsPath, scriptsTask);
    watch([files.scssPath, files.scriptsPath], reload);
}

/* DEFAULT TASK */
exports.default = series(
    parallel(blocksScssTask, scssTask, libsTask, scriptsTask), 
    watchTask
);
