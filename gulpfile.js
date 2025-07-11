/**
 * @package gutenberg-social-icon-variations
 * @author Cooper Dalrymple
 * @license gplv3-or-later
 * @version 1.0.1
 * @since 1.0.0
 */

const gulp = require('gulp'),
	clean = require('gulp-clean'),
	zip = require('gulp-zip').default,
	path = require('path'),
	through = require('through'),
	util = require('gulp-util'),
	{ parseFromString } = require('dom-parser');

const NAME = __dirname.split('/').reverse()[0];

const svg2icon = (dest) => {
	const getName = (filepath) => {
		return path.basename(filepath, '.svg');
	};

	const getTitle = (filepath) => {
		return getName(filepath).replace('-', ' ').split(' ').map((word) => word[0].toUpperCase() + word.substr(1)).join(' ');
	};

	const data = [];
	let first = null;

	const process = function (file) {
		first = first || file;

		if (file.isNull()) return;
		if (file.isStream()) {
			return this.emit('error', new util.PluginError(NAME, 'Streaming not supported.'));
		}

		const dom = parseFromString('<html>' + file.contents + '</html>');
		const svg = dom.getElementsByTagName('svg')[0];
		data.push({
			name: getName(file.path),
			title: getTitle(file.path),
			viewBox: svg.getAttribute('viewBox'),
			path: svg.firstChild.getAttribute('d'),
		});
	};

	const concat = function () {
		this.emit('data', new util.File({
			cwd: first.cwd,
			base: first.base,
			path: path.join(first.base, dest),
			contents: Buffer.from(JSON.stringify(data, null, 4)),
		}));
		this.emit('end');
	};

	return through(process, concat);
};

gulp.task('clean', () => {
	return gulp.src([
		`${NAME}.zip`,
		'icons.json'
	], {
		read: false,
		allowEmpty: true,
	}).pipe(clean());
});

gulp.task('compile', () => {
	return gulp.src('icons/*.svg')
		.pipe(svg2icon('icons.json'))
		.pipe(gulp.dest('./'));
});

gulp.task('package', () => {
	return gulp.src([
        `${NAME}.php`,
		'assets/*.png',
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
	'default',
	gulp.series(
		'clean',
		'compile',
		'package'
	)
);
