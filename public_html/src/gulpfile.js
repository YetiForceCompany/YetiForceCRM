/**
 * gulpfile
 *
 * @description contains css tasks
 * @license YetiForce Public License 3.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */

const gulp = require('gulp')
const stylus = require('gulp-stylus')

const license =
	'/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */\n'

const stylusSrc = 'css/**/*.styl'

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
			.src(src, {sourcemaps: true})
			.pipe(stylus())
			.pipe(
				gulp.dest('./css'),
				{sourcemaps: true}
			)
	}
}

/**
 * Compile css task
 */
gulp.task('compileCss', getCompileCssTask())
