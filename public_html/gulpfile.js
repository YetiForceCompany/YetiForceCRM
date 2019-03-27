const gulp = require('gulp')
const browserSync = require('browser-sync').create()
const babel = require('gulp-babel')

const vueEsCompiler = require('./gulp-vue-es-compiler')
const ModuleLoader = require('./ModuleLoader.server')
const modules = ModuleLoader.loadModules('src')
ModuleLoader.saveModuleConfig(modules)

gulp.task('vue', function() {
  return gulp
    .src('src/**/*.vue')
    .pipe(vueEsCompiler())
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
    .pipe(gulp.dest('./src/'))
})

gulp.task('default', function() {
  browserSync.init({
    server: {
      baseDir: './public_html',
      index: './src/index.html',
      routes: {
        src: 'src'
      }
    }
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
