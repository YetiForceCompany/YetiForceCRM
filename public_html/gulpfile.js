/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

const gulp = require('gulp')
const browserSync = require('browser-sync').create()
const babel = require('gulp-babel')
const terser = require('gulp-terser')
const rename = require('gulp-rename')
const replace = require('gulp-replace')
const gap = require('gulp-append-prepend')

const vueEsCompiler = require('./gulp-vue-es-compiler')
const ModuleLoader = require('./ModuleLoader.server')
const modules = ModuleLoader.loadModules('src')
ModuleLoader.saveModuleConfig(modules)

gulp.task('vue', function() {
  return gulp
    .src('src/**/*.vue')
    .pipe(vueEsCompiler())
    .pipe(
      gap.prependText(
        '/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */',
        '\n'
      )
    )
    .pipe(
      babel({
        sourceMap: 'both',
        presets: [['@babel/preset-env', { modules: false }]],
        plugins: [
          ['@babel/plugin-syntax-dynamic-import'],
          [
            'module-resolver',
            {
              root: ['./src/**'],
              alias: {
                '^src/(.+)': '/src/\\1',
                '^store/(.+)': '/src/store/\\1',
                '^components/(.+)': '/src/components/\\1',
                '^layouts/(.+)': '/src/layouts/\\1',
                '^modules/(.+)': '/src/modules/\\1',
                '^assets/(.+)': '/src/assets/\\1',
                '^statics/(.+)': '/src/statics/\\1',
                '^utilities/(.+)': '/src/utilities/\\1',
                '^services/(.+)': '/src/services/\\1',
                '^pages/(.+)': '/src/pages/\\1',
                '^Core/(.+)': '/src/modules/Core/\\1',
                '^Base/(.+)': '/src/modules/Base/\\1',
                '^Settings/(.+)': '/src/modules/Setting/\\1',
                '^/src/(.+)': '/src/\\1',
                '^/store/(.+)': '/src/store/\\1',
                '^/components/(.+)': '/src/components/\\1',
                '^/layouts/(.+)': '/src/layouts/\\1',
                '^/modules/(.+)': '/src/modules/\\1',
                '^/assets/(.+)': '/src/assets/\\1',
                '^/statics/(.+)': '/src/statics/\\1',
                '^/utilities/(.+)': '/src/utilities/\\1',
                '^/services/(.+)': '/src/services/\\1',
                '^/pages/(.+)': '/src/pages/\\1',
                '^/Core/(.+)': '/src/modules/Core/\\1',
                '^/Base/(.+)': '/src/modules/Base/\\1',
                '^/Settings/(.+)': '/src/modules/Setting/\\1'
              }
            }
          ]
        ]
      })
    )
    .pipe(gulp.dest('./src/'))
})

gulp.task('min', function() {
  return gulp
    .src(['src/**/*.js', '!src/**/*.min.js'])
    .pipe(
      babel({
        presets: [['@babel/preset-env', { modules: false }]],
        plugins: [
          ['@babel/plugin-syntax-dynamic-import'],
          [
            'module-resolver',
            {
              root: ['./src/**'],
              alias: {
                '^src/(.+)': '/src/\\1',
                '^store/(.+)': '/src/store/\\1',
                '^components/(.+)': '/src/components/\\1',
                '^layouts/(.+)': '/src/layouts/\\1',
                '^modules/(.+)': '/src/modules/\\1',
                '^assets/(.+)': '/src/assets/\\1',
                '^statics/(.+)': '/src/statics/\\1',
                '^utilities/(.+)': '/src/utilities/\\1',
                '^services/(.+)': '/src/services/\\1',
                '^pages/(.+)': '/src/pages/\\1',
                '^Core/(.+)': '/src/modules/Core/\\1',
                '^Base/(.+)': '/src/modules/Base/\\1',
                '^Settings/(.+)': '/src/modules/Setting/\\1',
                '^/src/(.+)': '/src/\\1',
                '^/store/(.+)': '/src/store/\\1',
                '^/components/(.+)': '/src/components/\\1',
                '^/layouts/(.+)': '/src/layouts/\\1',
                '^/modules/(.+)': '/src/modules/\\1',
                '^/assets/(.+)': '/src/assets/\\1',
                '^/statics/(.+)': '/src/statics/\\1',
                '^/utilities/(.+)': '/src/utilities/\\1',
                '^/services/(.+)': '/src/services/\\1',
                '^/pages/(.+)': '/src/pages/\\1',
                '^/Core/(.+)': '/src/modules/Core/\\1',
                '^/Base/(.+)': '/src/modules/Base/\\1',
                '^/Settings/(.+)': '/src/modules/Setting/\\1'
              }
            }
          ]
        ]
      })
    )
    .pipe(replace(/(import\s.+\s([\'\"\`]){1}(?!\/?node_modules)\.?\.?[^\.]+\.)js[\'\"\`]/gim, '$1min.js$2'))
    .pipe(replace(/import\([\'\"\`]?(?!.*\/?node_modules)(.+)\.js([\'\"\`]?)\)/gim, 'import($1.min.js$2)'))
    .pipe(
      terser({
        module: true,
        output: {
          ascii_only: true
        }
      })
    )
    .pipe(
      rename({
        extname: '.min.js'
      })
    )
    .pipe(gulp.dest('src'))
})

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
