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

const license =
	'/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */\n'

const stylusSrc = 'css/quasar.styl'

/**
 * Compile .css file
 *
 * @param {string|array} src
 *
 * @returns {function} task
 */
function getCompileCssTask(src = stylusSrc) {
	return function compileCssTask() {
		return gulp
			.src(src, { sourcemaps: true })
			.pipe(stylus())
			.pipe(
				autoprefixer(
					'safari 6',
					'ios 7',
					'ie 11',
					'last 2 Chrome versions',
					'last 2 Firefox versions',
					'Explorer >= 11',
					'last 1 Edge versions'
				)
			)
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
 * Compile css task
 */
gulp.task('compileCss', getCompileCssTask())
