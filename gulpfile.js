'use strict';

var gulp = require('gulp');
var sass = require('gulp-sass')(require('node-sass'));

gulp.task('sass', function () {
    return gulp.src('./theme/edumy/scss/*.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(gulp.dest('./theme/edumy/style'));
});

gulp.task('sass:watch', function () {
    gulp.watch('./scss/*.scss', gulp.series('sass'));
});
