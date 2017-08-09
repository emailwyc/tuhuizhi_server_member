var gulp = require('gulp');
var gutil = require('gulp-util');
var ftp = require('gulp-ftp');

// 分析页面图片
// 上传js、css 和 img 到正式环境
gulp.task('pub_static', function () {
    return gulp.src('./static/dist/**/*.*')
        .pipe(ftp({
            host: '123.56.109.162',
            user: 'ftpuser',
            pass: 'e9b0c3cd9c6d43f612234f0deb28d915',
			      remotePath: '/home/ftpuser/static/dist'
        }))
        // you need to have some kind of stream after gulp-ftp to make sure it's flushed
        // this can be a gulp plugin, gulp.dest, or any kind of stream
        // here we use a passthrough stream
        .pipe(gutil.noop());
});

// 上传js、css 和 img 到日常环境
gulp.task('pub_static_daily', function () {
    return gulp.src('./static/dist/**/*.*')
        .pipe(ftp({
            host: '123.56.109.162',
            user: 'ftpuser',
            pass: 'e9b0c3cd9c6d43f612234f0deb28d915',
			      remotePath: '/home/ftpuser/static/daily'
        }))
        // you need to have some kind of stream after gulp-ftp to make sure it's flushed
        // this can be a gulp plugin, gulp.dest, or any kind of stream
        // here we use a passthrough stream
        .pipe(gutil.noop());
});
