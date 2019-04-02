/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
const gulp = require('gulp')
const stylus = require('gulp-stylus')
const browserSync = require('browser-sync').create()
const rename = require('gulp-rename')
const header = require('gulp-header')
const path = require('path')
require('dotenv').config()

const importAliases = require('./gulp/gulp-import-aliases')
const importMin = require('./gulp/gulp-import-min')
const terser = require('./gulp/gulp-terser')
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
  '/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */\n'

const sourceDir = 'src'
const vueSrc = 'src/**/*.vue'
const stylusSrc = 'src/css/**/*.styl'
const modulesConfigSrc = 'src/statics/modules.js'
const minSrc = ['src/**/*.js', '!src/**/*.min.js', '!src/**/*.vue.js']
const generatedSrc = [
  modulesConfigSrc,
  'src/store/mutations.js',
  'src/store/getters.js',
  'src/store/actions.js',
  'src/store/state.js'
]
/**
 * Compile vue files into .min.js, replace directory aliases and internal imports to .min.js
 *
 * @param {string|array} src
 *
 * @returns {function} task function
 */
function getVueTask(src = vueSrc, dev = false) {
  return function vueTask() {
    let dest = `./${sourceDir}/`
    if (src !== vueSrc) {
      dest = './' + src.slice(0, src.lastIndexOf('/') + 1)
    }
    const importMinOptions = { extension: 'vue.js' }
    return gulp
      .src(src, { sourcemaps: true })
      .pipe(vueEsCompiler())
      .pipe(importAliases({ map: aliases }))
      .pipe(importMin(importMinOptions))
      .pipe(
        terser({
          module: true
        })
      )
      .pipe(header(license))
      .pipe(
        rename({
          extname: '.vue.js'
        })
      )
      .pipe(gulp.dest(dest, { sourcemaps: true }))
  }
}
gulp.task('vue', getVueTask())

/**
 * Minify module.js config file and replace .js internal paths to .min.js
 */
function getModulesTask(src = modulesConfigSrc, dev = false) {
  return function modulesTask(done) {
    const importMinConfig = {}
    if (!generatedSrc.includes(src)) {
      ModuleLoader.saveModuleConfig(ModuleLoader.loadModules(sourceDir))
    } else {
      return done()
    }
    return gulp
      .src(src, { sourcemaps: true })
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
      .pipe(importMin(importMinConfig))
      .pipe(header(license))
      .pipe(
        rename({
          extname: '.min.js'
        })
      )
      .pipe(gulp.dest(sourceDir + '/statics/', { sourcemaps: true }))
  }
}
gulp.task('modules.js', getModulesTask())

/**
 * Minify .js files and replace directory aliases
 *
 * @param {string|array} src
 *
 * @returns {function} task
 */
function getMinTask(src = minSrc, dev = false) {
  return function minTask() {
    let dest = `./${sourceDir}/`
    if (src !== minSrc) {
      dest = './' + src.slice(0, src.lastIndexOf('/') + 1)
    }
    const importMinConfig = {}
    if (dev) {
      importMinConfig.postfix = '?dev=' + new Date().getTime()
    }
    return gulp
      .src(src, { sourcemaps: true })
      .pipe(
        terser({
          module: true
        })
      )
      .pipe(importAliases({ map: aliases }))
      .pipe(importMin(importMinConfig))
      .pipe(header(license))
      .pipe(
        rename({
          extname: '.min.js'
        })
      )
      .pipe(gulp.dest(dest, { sourcemaps: true }))
  }
}
gulp.task('min', getMinTask())

/**
 * Compile .css file
 *
 * @param {string|array} src
 *
 * @returns {function} task
 */
function getCompileCssTask() {
  return function compileCssTask() {
    return gulp
      .src('./src/css/app.styl', { sourcemaps: true })
      .pipe(stylus())
      .pipe(
        gulp.dest('./src/css'),
        { sourcemaps: true }
      )
  }
}

/**
 * Compile css task
 */
gulp.task('compileCss', getCompileCssTask())

/**
 * Build task
 */
gulp.task('build', gulp.series(['vue', 'modules.js', 'min']))

/**
 * Start dev environment with browser-sync
 */
gulp.task('dev', function() {
  ModuleLoader.log = true
  gulp.series([getVueTask(vueSrc, true), getModulesTask(modulesConfigSrc, true), getMinTask(minSrc, true)])(() => {
    ModuleLoader.log = false
    browserSync.init({
      proxy: typeof process.env.LOCAL_URL !== 'undefined' ? process.env.LOCAL_URL : 'http://yeti',
      browser: 'chrome'
    })
    gulp.watch(vueSrc).on('all', (eventName, fileName) => {
      fileName = fileName.replace(/\\/gim, '/')
      console.log(eventName, fileName)
      gulp.series([getVueTask(fileName, true)])(() => {
        console.log(eventName, fileName, 'done')
        browserSync.reload()
      })
    })
    gulp.watch(minSrc).on('all', (eventName, fileName) => {
      fileName = fileName.replace(/\\/gim, '/')
      console.log(eventName, fileName)
      gulp.series([getMinTask(fileName, true), getModulesTask(fileName, true)])(() => {
        console.log(eventName, fileName, 'done')
        browserSync.reload()
      })
    })
  })

  gulp.watch(stylusSrc).on('all', (eventName, fileName) => {
    fileName = fileName.replace('\\', '/')
    console.log(eventName, fileName)
    gulp.series([getCompileCssTask()])(() => {
      console.log(eventName, fileName, 'done')
      browserSync.reload(fileName)
    })
  })
})
