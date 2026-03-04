/**
 * @package social-icon-block-variations
 * @author Cooper Dalrymple
 * @license gplv3-or-later
 * @version 1.1.2
 * @since 1.0.0
 */

const fs = require('fs'),
	gulp = require('gulp'),
	clean = require('gulp-clean'),
	zip = require('gulp-zip').default,
	path = require('path'),
	through = require('through'),
	util = require('gulp-util'),
	{ parseFromString } = require('dom-parser');

const PACKAGE = require('./package.json');
const NAME = PACKAGE.name;

// Clean Tasks

gulp.task('clean-package-files', (done) => {
	if (!fs.existsSync('./dist')) return done();
	return gulp.src(`./dist/${NAME}`, {
		read: false,
		allowEmpty: true,
	}).pipe(clean());
});

gulp.task('clean-package-zip', (done) => {
	if (!fs.existsSync('./dist')) return done();
	return gulp.src('./dist/*.zip', {
		read: false,
		allowEmpty: true,
	}).pipe(clean());
});

gulp.task(
	'clean-package',
	gulp.series(
		'clean-package-files',
		'clean-package-zip'
	)
);

gulp.task('clean-icons', () => {
	return gulp.src('icons.json', {
		read: false,
		allowEmpty: true,
	}).pipe(clean());
});

gulp.task(
	'clean',
	gulp.series(
		'clean-package',
		'clean-icons'
	)
);

gulp.task('clean', () => {
	return gulp.src([
		`${NAME}.zip`,
		'icons.json'
	], {
		read: false,
		allowEmpty: true,
	}).pipe(clean());
});

// Compile Tasks

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

gulp.task('compile-icons', () => {
	return gulp.src('icons/*.svg')
		.pipe(svg2icon('icons.json'))
		.pipe(gulp.dest('./'));
});

gulp.task(
	'compile',
	gulp.series(
		'compile-icons'
	)
);

// Package Tasks

gulp.task('package-copy', () => {
	return gulp.src([
        `${NAME}.php`,
        'block-editor.js',
        'icons.json',
        'index.php',
		'lang',
		'lang/**/*',
        'LICENSE',
        'readme.txt'
	], {
		base: './',
		encoding: false,
	})
		.pipe(gulp.dest(`./dist/${NAME}`));
});

gulp.task('package-compress', () => {
	return gulp.src(`./dist/${NAME}/**/*`, {
        base: './dist',
        encoding: false,
    })
		.pipe(zip(`${NAME}.zip`))
		.pipe(gulp.dest('./dist'));
});

gulp.task(
	'package',
	gulp.series(
		'clean',
		'compile',
		'package-copy',
		'package-compress',
		'clean-package-files'
	)
);

// Default Tasks

gulp.task(
	'default',
	gulp.series(
		'package'
	)
);
