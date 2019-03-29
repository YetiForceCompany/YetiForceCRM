/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
const gulp = require('gulp')
const browserSync = require('browser-sync').create()
const terser = require('gulp-terser')
const rename = require('gulp-rename')
const gap = require('gulp-append-prepend')
require('dotenv').config()

const importAliases = require('./gulp/gulp-import-aliases')
const importMin = require('./gulp/gulp-import-min')
const vueEsCompiler = require('./gulp/gulp-vue-es-compiler')
const logger = require('./gulp/gulp-log')
const ModuleLoader = require('./ModuleLoader.server')

const aliases = {
  '/?src/': '/src/',
  '/?store/': '/src/store/',
  '/?components/': '/src/components/',
  '/?layouts/': '/scr/layouts/',
  '/?modules/': '/src/modules/',
  '/?assets/': '/src/assets/',
  '/?statics/': '/src/statics/',
  '/?utilities/': '/src/utilities/',
  '/?services/': '/src/services/',
  '/?pages/': '/src/pages/',
  '/?Core/': '/src/modules/Core/',
  '/?Base/': '/src/modules/Base/',
  '/?Settings/': '/src/modules/Setting/'
}

const license =
  '/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */'

const sourceDir = 'src'
const vueSrc = 'src/**/*.vue'
const minSrc = [
  'src/**/*.js',
  '!src/**/*.min.js',
  '!src/statics/modules.js',
  '!src/statics/modules.min.js',
  '!src/store/mutations.js',
  '!src/store/mutations.min.js',
  '!src/store/getters.js',
  '!src/store/getters.min.js',
  '!src/store/actions.js',
  '!src/store/actions.min.js'
]
const modulesSrc = 'src/statics/modules.js'

/**
 * Compile vue files into .min.js, replace directory aliases and internal imports to .min.js
 *
 * @param {string|array} src
 *
 * @returns {function} task function
 */
function getVueTask(src) {
  console.log('Getting vue Task', src)
  return function vueTask() {
    return gulp
      .src(src)
      .pipe(vueEsCompiler())
      .pipe(importAliases({ map: aliases }))
      .pipe(importMin())
      .pipe(
        terser({
          module: true
        })
      )
      .pipe(gap.prependText(license, '\n'))
      .pipe(
        rename({
          extname: '.min.js'
        })
      )
      .pipe(gulp.dest('./src/'))
  }
}
gulp.task('vue', getVueTask('src/**/*.vue'))

/**
 * Minify module.js config file and replace .js internal paths to .min.js
 */
gulp.task('modules.js', function() {
  ModuleLoader.saveModuleConfig(ModuleLoader.loadModules(sourceDir))
  return gulp
    .src(modulesSrc)
    .pipe(importMin([{ regexp: /("componentPath"\s?:\s?")(.+)(\.js")/gim, replace: '$1$2.min$3' }]))
    .pipe(
      terser({
        module: false,
        mangle: {
          properties: {
            keep_quoted: true
          }
        },
        output: {
          keep_quoted_props: true
        },
        compress: {
          booleans_as_integers: false,
          booleans: false
        }
      })
    )
    .pipe(gap.prependText(license, '\n'))
    .pipe(
      rename({
        extname: '.min.js'
      })
    )
    .pipe(gulp.dest(sourceDir + '/statics/'))
})

/**
 * Minify .js files and replace directory aliases
 *
 * @param {string|array} src
 *
 * @returns {function} task
 */
function getMinTask(src) {
  return function minTask() {
    return gulp
      .src(src)
      .pipe(importAliases({ map: aliases }))
      .pipe(importMin())
      .pipe(
        terser({
          module: true
        })
      )
      .pipe(gap.prependText(license, '\n'))
      .pipe(
        rename({
          extname: '.min.js'
        })
      )
      .pipe(gulp.dest(sourceDir))
  }
}
gulp.task('min', getMinTask(minSrc))

/**
 * Build task
 */
gulp.task('build', gulp.series(['vue', 'modules.js', 'min']))

/**
 * Start dev environment with browser-sync
 */
gulp.task('dev', function() {
  browserSync.init({
    proxy: process.env.LOCAL_URL
  })
  ModuleLoader.log = false
  gulp.watch(vueSrc).on('all', (eventName, fileName) => {
    fileName = fileName.replace('\\', '/')
    console.log(eventName, fileName)
    gulp.series([getVueTask(fileName)])(() => {
      console.log(eventName, fileName, 'done')
      browserSync.reload()
    })
  })
  gulp.watch(minSrc).on('all', (eventName, fileName) => {
    fileName = fileName.replace('\\', '/')
    console.log(eventName, fileName)
    ModuleLoader.saveModuleConfig(ModuleLoader.loadModules(sourceDir))
    gulp.series([getMinTask(fileName), 'modules.js'])(() => {
      console.log(eventName, fileName, 'done')
      browserSync.reload(fileName)
    })
  })
})
