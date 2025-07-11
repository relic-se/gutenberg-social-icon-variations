/**
 * @package gutenberg-social-icon-variations
 * @author Cooper Dalrymple
 * @license gplv3-or-later
 * @version 1.0.0
 * @since 1.0.0
 */

const gulp = require('gulp'),
	clean = require('gulp-clean'),
	zip = require('gulp-zip').default;

const NAME = __dirname.split('/').reverse()[0];

gulp.task('clean-package', () => {
	return gulp.src(`${NAME}.zip`, {
		read: false,
		allowEmpty: true,
	}).pipe(clean());
});

gulp.task(
	'clean',
	gulp.series(
		'clean-package'
	)
);

gulp.task('package-compress', () => {
	return gulp.src([
        `${NAME}.php`,
        'block-editor.js',
        'icons.json',
        'index.php',
        'LICENSE',
        'readme.txt'
	], { base: './' })
		.pipe(zip(`${NAME}.zip`))
		.pipe(gulp.dest('./'));
});

gulp.task(
	'package',
	gulp.series(
		'clean',
		'package-compress'
	)
);

gulp.task(
	'default',
	gulp.series(
		'package'
	)
);
