/**
 * gulpfile.js
 *
 * @description contains css tasks
 * @license YetiForce Public License 5.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */

const gulp = require('gulp')
const stylus = require('gulp-stylus')
const cleanCSS = require('gulp-clean-css')
const autoprefixer = require('gulp-autoprefixer')
const sourcemaps = require('gulp-sourcemaps')
const rename = require('gulp-rename')
const sass = require('gulp-sass')(require('sass'))

const mainCssPath = '../layouts/basic/styles/'
const mainScss = 'Main.scss'
const graySkinPath = '../layouts/basic/skins/gray/'
const grayScss = 'style.scss'
const quasarCssPath = './css/'
const quasarStyl = 'quasar.styl'

/**
 * Compile quasar.css file
 *
 * @returns {function} task
 */
function getCompileQuasarCssTask() {
	return function compileCssTask() {
		return gulp
			.src(quasarCssPath + quasarStyl)
			.pipe(sourcemaps.init())
			.pipe(stylus())
			.pipe(autoprefixer())
			.pipe(
				cleanCSS({}, details => {
					console.log(`${details.name}: ${details.stats.originalSize}`)
					console.log(`${details.name}: ${details.stats.minifiedSize}`)
				})
			)
			.pipe(sourcemaps.write('./'))
			.pipe(gulp.dest(quasarCssPath))
	}
}

/**
 * Compile *.min.css file
 *
 * @param   {string}  path  css path
 * @param   {string}  name  scss file name
 *
 * @return  {function} minifyCssTask
 */
function getMinifyCssTask(path, name) {
	return function minifyCssTask() {
		return gulp
			.src(path + name)
			.pipe(
				rename({
					suffix: '.min'
				})
			)
			.pipe(sourcemaps.init())
			.pipe(sass({ style: 'compressed' }).on('error', sass.logError))
			.pipe(autoprefixer())
			.pipe(
				cleanCSS({}, details => {
					console.log(`${details.name}: ${details.stats.originalSize}`)
					console.log(`${details.name}: ${details.stats.minifiedSize}`)
				})
			)
			.pipe(sourcemaps.write('./'))
			.pipe(gulp.dest(path))
	}
}

/**
 * Compile *.css file
 *
 * @param   {string}  path  css path
 * @param   {string}  name  scss file name
 *
 * @return  {function} compileCss
 */
function getCompileCss(path, name) {
	return function compileCss() {
		return gulp
			.src(path + name)
			.pipe(sourcemaps.init())
			.pipe(sass({ style: 'compressed' }).on('error', sass.logError))
			.pipe(sourcemaps.write('./'))
			.pipe(gulp.dest(path))
	}
}

gulp.task('compile-quasar-css', getCompileQuasarCssTask())
gulp.task('compile-main-css', getCompileCss(mainCssPath, mainScss))
gulp.task('compile-gray-css', getCompileCss(graySkinPath, grayScss))
gulp.task('compile-css', gulp.series('compile-main-css', 'compile-gray-css'))
gulp.task('minify-main-css', getMinifyCssTask(mainCssPath, mainScss))
gulp.task('minify-gray-css', getMinifyCssTask(graySkinPath, grayScss))
gulp.task('minify-css', gulp.series('minify-main-css', 'minify-gray-css'))

gulp.task('watch-css', function() {
	gulp.watch(`${mainCssPath}**/*.scss`, gulp.series('compile-main-css'))
	gulp.watch(`${graySkinPath}**/*.scss`, gulp.series('compile-gray-css'))
})
