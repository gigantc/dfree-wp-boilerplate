// Include gulp
var gulp = require('gulp'),
    plumber = require('gulp-plumber'),
    rename = require('gulp-rename');

// Include Our Plugins
var sass = require('gulp-sass'),
    sourcemaps = require('gulp-sourcemaps'),
    cleancss = require('gulp-clean-css'),
    autoprefixer = require('gulp-autoprefixer'),
    concat = require('gulp-concat'),
    rename = require('gulp-rename'),
    uglify = require('gulp-uglify');

// Set up compression, prefixing, sourcemaps and destination
gulp.task('sass', function(){
  gulp.src(['src/scss/**/*.scss'])
    .pipe(plumber({
      errorHandler: function (error) {
        console.log(error.message);
        this.emit('end');
    }}))
    .pipe(sass())
    .pipe(autoprefixer('last 2 versions'))
    .pipe(cleancss())
    .pipe(gulp.dest('css/'))
});

gulp.task('libsjs', function(){
  return gulp.src('src/js/libs/*.js')
        .pipe(concat('libs.min.js'))
        .pipe(uglify())
        .pipe(gulp.dest('js/libs/'));
});

gulp.task('page_scripts', function(){
  return gulp.src('src/js/*.js')
    .pipe(plumber({
      errorHandler: function (error) {
        console.log(error.message);
        this.emit('end');
    }}))
    .pipe(rename({suffix: '.min'}))
    .pipe(uglify())
    .pipe(gulp.dest('js/'))
});

gulp.task('scripts', function(){
  return gulp.src(['src/js/main.js'])
    .pipe(plumber({
      errorHandler: function (error) {
        console.log(error.message);
        this.emit('end');
    }}))
    .pipe(concat('main.min.js'))
    .pipe(uglify())
    .pipe(gulp.dest('js/'))
});


// Watch Files For Changes
gulp.task('watch', function() {
    gulp.watch('src/scss/**/*.scss', ['sass']),
    gulp.watch('src/js/*.js', ['scripts', 'page_scripts']),
    gulp.watch('src/js/vendor/*.js', ['vendorjs']);
});

// Default Task
gulp.task('default', ['sass', 'libsjs', 'page_scripts', 'scripts', 'watch']);