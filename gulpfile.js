'use strict';

var gulp         = require( 'gulp' ),
	sass         = require( 'gulp-sass' ),
	postcss      = require( 'gulp-postcss' ),
	autoprefixer = require( 'autoprefixer' ),
	minify       = require( 'gulp-csso' );

gulp.task( 'style', function() {
	gulp.src( 'assets/sass/public.scss' )
		.pipe( sass() )
		.pipe( postcss( [
			autoprefixer( {
				browsers: [
					'last 2 versions'
				]
			} )
		] ) )
		.pipe( minify() )
		.pipe( gulp.dest( 'assets/css/' ) );
} );

gulp.task( 'watch', function() {
	gulp.watch( 'assets/sass/**', ['style'] )
} );

gulp.task( 'default', function() {
	console.log( 'default gulp task...' )
} );
