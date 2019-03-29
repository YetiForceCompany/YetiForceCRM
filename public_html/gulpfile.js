/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
const gulp = require('gulp')
const browserSync = require('browser-sync').create()
const terser = require('gulp-terser')
const rename = require('gulp-rename')
const gap = require('gulp-append-prepend')
const path = require('path')
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
    const importMinConfig = {
      extension: 'vue.js'
    }
    if (dev) {
      importMinConfig.postfix = '?dev=' + new Date().getTime()
    }
    return gulp
      .src(src)
      .pipe(vueEsCompiler())
      .pipe(importAliases({ map: aliases }))
      .pipe(importMin(importMinConfig))
      .pipe(
        terser({
          module: true
        })
      )
      .pipe(gap.prependText(license, '\n'))
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
    if (generatedSrc.indexOf(src) === -1) {
      ModuleLoader.saveModuleConfig(ModuleLoader.loadModules(sourceDir))
    }
    if (src !== modulesConfigSrc) {
      return done()
    }
    return gulp
      .src(src)
      .pipe(importMin(importMinConfig))
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
      .src(src)
      .pipe(importAliases({ map: aliases }))
      .pipe(importMin(importMinConfig))
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
      .pipe(gulp.dest(dest, { sourcemaps: true }))
  }
}
gulp.task('min', getMinTask())

/**
 * Build task
 */
gulp.task('build', gulp.series(['vue', 'modules.js', 'min']))

/**
 * Start dev environment with browser-sync
 */
gulp.task('dev', function() {
  ModuleLoader.log = true
  ModuleLoader.dev = true
  gulp.series([getVueTask(vueSrc, true), getModulesTask(modulesConfigSrc, true), getMinTask(minSrc, true)])(() => {
    ModuleLoader.log = false
    browserSync.init({
      proxy: process.env.LOCAL_URL,
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
})
