/**
 * gulpfile
 *
 * @description contains css tasks
 * @license YetiForce Public License 3.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */

const gulp = require('gulp')
const stylus = require('gulp-stylus')
const cleanCSS = require('gulp-clean-css')
const autoprefixer = require('gulp-autoprefixer')
const sourcemaps = require('gulp-sourcemaps')
const rename = require('gulp-rename')

/**
 * Compile quasar.css file
 *
 * @returns {function} task
 */
function getCompileCssTask() {
	return function compileCssTask() {
		const quasarCssPath = 'css/quasar.styl'
		return gulp
			.src(quasarCssPath, { sourcemaps: true })
			.pipe(stylus())
			.pipe(autoprefixer())
			.pipe(
				cleanCSS({}, details => {
					console.log(`${details.name}: ${details.stats.originalSize}`)
					console.log(`${details.name}: ${details.stats.minifiedSize}`)
				})
			)
			.pipe(gulp.dest('./css'), { sourcemaps: true })
	}
}
/**
 * Compile Main.min.css file
 *
 * @returns {function} task
 */
function getMinifyCssTask() {
	return function minifyCssTask() {
		const stylesPath = '../layouts/basic/styles/'
		return gulp
			.src(`${stylesPath}Main.css`)
			.pipe(sourcemaps.init())
			.pipe(
				rename({
					suffix: '.min'
				})
			)
			.pipe(autoprefixer())
			.pipe(
				cleanCSS({}, details => {
					console.log(`${details.name}: ${details.stats.originalSize}`)
					console.log(`${details.name}: ${details.stats.minifiedSize}`)
				})
			)
			.pipe(sourcemaps.write())
			.pipe(gulp.dest(stylesPath))
	}
}

gulp.task('compile-quasar-css', getCompileCssTask())
gulp.task('minify-css', getMinifyCssTask())
