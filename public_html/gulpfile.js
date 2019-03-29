/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
const gulp = require('gulp')
const browserSync = require('browser-sync').create()
const terser = require('gulp-terser')
const rename = require('gulp-rename')
const gap = require('gulp-append-prepend')

const importAliases = require('./gulp/gulp-import-aliases')
const importMin = require('./gulp/gulp-import-min')
const vueEsCompiler = require('./gulp/gulp-vue-es-compiler')
const logger = require('./gulp/gulp-log')
const ModuleLoader = require('./ModuleLoader.server')
const modules = ModuleLoader.loadModules('src')
ModuleLoader.saveModuleConfig(modules)

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

gulp.task('vue', function() {
  return gulp
    .src('src/**/*.vue')
    .pipe(vueEsCompiler())
    .pipe(importAliases({ map: aliases }))
    .pipe(importMin())
    .pipe(
      terser({
        module: true
      })
    )
    .pipe(gap.prependText(license,'\n'))
    .pipe(
      rename({
        extname: '.min.js'
      })
    )
    .pipe(gulp.dest('./src/'))
})

gulp.task('modules.js', function() {
  return gulp
    .src('src/statics/modules.js')
    .pipe(importMin([{ regexp: /(\"componentPath\"\s?\:\s?\")(.+)(\.js\")/gim, replace: '$1$2.min$3' }]))
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
    .pipe(gulp.dest('src/statics/'))
})

gulp.task('min', function() {
  return gulp
    .src(['src/**/*.js', '!src/**/*.min.js', '!src/statics/modules.js'])
    .pipe(importAliases({ map: aliases }))
    .pipe(importMin())
    .pipe(
      terser({
        module: true
      })
    )
    .pipe(gap.prependText(license,'\n'))
    .pipe(
      rename({
        extname: '.min.js'
      })
    )
    .pipe(gulp.dest('src'))
})

gulp.task('build', gulp.series(['modules.js', 'vue', 'min']))

gulp.task('default', function() {
  browserSync.init({
    proxy: 'http://yeti:80'
  })
  if (process.env.dev) {
    ModuleLoader.watchDir('./src')
  }
  gulp.watch(['./src/**/*.vue'], gulp.series('vue'))
  gulp.watch(['./src/**/*.js'], done => {
    browserSync.reload()
    done()
  })
})
