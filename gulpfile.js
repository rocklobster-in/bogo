'use strict';

// Load plugins
const gulp = require('gulp');
const rename = require('gulp-rename');
const uglify = require('gulp-uglify');
const ts = require('gulp-typescript');

// JS task
function js() {
  return gulp.src([
    './admin/includes/ts/*.ts',
  ]).pipe(ts({
    noImplicitAny: true,
  })).pipe(uglify()).pipe(rename({
    suffix: '.min',
  })).pipe(gulp.dest('./admin/includes/js'));
}

// Watch files
function watchFiles() {
  gulp.watch(['./admin/includes/ts/*'], js);
}

// Define complex tasks
const build = gulp.series(gulp.parallel(js));
const watch = gulp.series(build, gulp.parallel(watchFiles));

exports.watch = watch;
