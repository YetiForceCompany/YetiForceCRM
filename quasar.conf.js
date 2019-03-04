// Configuration for your app
const ModuleLoader = require('./ModuleLoader.server.js')
const webpack = require('webpack')
const exec = require('child_process').exec
const axios = require('axios')

module.exports = function (ctx) {
  ModuleLoader.loadModules()
  return {
    // app boot file (/src/boot)
    // --> boot files are part of "main.js"
    boot: ['i18n', 'axios'],

    css: ['app.styl'],

    htmlVariables: {
      modulesFile: ctx.dev ? '/statics/modules.js' : '/dist/statics/modules.js',
      dev: ctx.dev
    },

    extras: [
      'roboto-font',
      'material-icons' // optional, you are not bound to it
      // 'ionicons-v4',
      // 'mdi-v3',
      // 'fontawesome-v5',
      // 'eva-icons'
    ],

    framework: 'all', // --- includes everything; for dev only!
    /*framework: {
      components: [
        'QLayout',
        'QHeader',
        'QDrawer',
        'QPageContainer',
        'QPage',
        'QToolbar',
        'QToolbarTitle',
        'QBtn',
        'QIcon',
        'QList',
        'QItem',
        'QItemSection',
        'QItemLabel',
        'QInput'
      ],

      directives: ['Ripple'],

      // Quasar plugins
      plugins: ['Notify']

      // iconSet: 'ionicons-v4'
      // lang: 'de' // Quasar language
    },*/

    supportIE: true,

    preFetch: true,

    sourceFiles: {
      indexHtmlTemplate: ctx.dev ? 'src/index.template.dev.html' : 'src/index.template.php'
      //rootComponent: 'src/App.vue',
      //router: 'src/router',
      //store: 'src/store',
      //registerServiceWorker: 'src-pwa/register-service-worker.js',
      //serviceWorker: 'src-pwa/custom-service-worker.js',
      //electronMainDev: 'src-electron/main-process/electron-main.dev.js',
      //electronMainProd: 'src-electron/main-process/electron-main.js'
    },

    build: {
      htmlFilename: ctx.dev ? '' : 'index.php',
      scopeHoisting: true,
      publicPath: 'dist',
      distDir: 'public_html/dist',
      vueRouterMode: 'hash',
      // vueCompiler: true,
      // gzip: true,
      // analyze: true,
      // extractCSS: false,
      extendWebpack(cfg) {
        if (typeof cfg.output === 'object') {
          cfg.output.filename = 'js/[name].js'
          cfg.output.chunkFilename = 'js/[name].js'
        }
        if (process.argv.indexOf('--watch') >= 0) {
          cfg.watch = true
          cfg.watchOptions = {
            ignored: [/node_modules/, /public_html/]
          }
        }
      }
    },

    devServer: {
      // https: true,
      // port: 8080,
      open: true, // opens browser window automatically
      headers: {
        'Access-Control-Allow-Origin': '*',
        'Access-Control-Allow-Headers': '*',
      },
      before(app, server) {
        let baseURL = ''
        // get configuration by running index.php from command line
        // which will in return get dev server template with configuration from php
        app.all('/', (req, res) => {
          exec('php ' + __dirname + '/index.php --dev', function (error, stdout, stderr) {
            res.append('access-control-allow-origin', '*')
            res.append('access-control-allow-headers', '*')
            res.append('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, DELETE, OPTIONS')
            if (stderr) {
              console.error('PHP Server error', stderr)
              return res.end(stderr)
            }
            const matches = /data\-config\-url\=\"([^\"]+)\"/gi.exec(stdout)
            if (matches && matches.length > 1) {
              baseURL = matches[1]
            } else {
              console.error('No baseURL inside template', stdout)
            }
            res.end(stdout)
          })
        })
        // catch login request and forward it to php server
        app.all('/login.php', (req, res) => {
          axios
            .post(baseURL + '/login.php', req.body)
            .then(function (response) {
              for (let headerName in response.headers) {
                res.append(headerName, response.headers[headerName])
              }
              if (response.status === 200) {
                res.json(response.data)
              } else {
                res.status(response.status).end(response.statusText)
              }
            })
            .catch(function (error) {
              res.end(error)
            })
        })
      }
    },

    // animations: 'all' --- includes all animations
    animations: [],

    ssr: {
      pwa: false
    },

    pwa: {
      // workboxPluginMode: 'InjectManifest',
      // workboxOptions: {},
      manifest: {
        // name: 'Quasar App',
        // short_name: 'Quasar-PWA',
        // description: 'Best PWA App in town!',
        display: 'standalone',
        orientation: 'portrait',
        background_color: '#ffffff',
        theme_color: '#027be3',
        icons: [
          {
            src: 'statics/icons/icon-128x128.png',
            sizes: '128x128',
            type: 'image/png'
          },
          {
            src: 'statics/icons/icon-192x192.png',
            sizes: '192x192',
            type: 'image/png'
          },
          {
            src: 'statics/icons/icon-256x256.png',
            sizes: '256x256',
            type: 'image/png'
          },
          {
            src: 'statics/icons/icon-384x384.png',
            sizes: '384x384',
            type: 'image/png'
          },
          {
            src: 'statics/icons/icon-512x512.png',
            sizes: '512x512',
            type: 'image/png'
          }
        ]
      }
    },

    cordova: {
      // id: 'org.cordova.quasar.app'
    },

    electron: {
      // bundler: 'builder', // or 'packager'
      extendWebpack(cfg) {
        // do something with Electron process Webpack cfg
      },
      packager: {
        // https://github.com/electron-userland/electron-packager/blob/master/docs/api.md#options
        // OS X / Mac App Store
        // appBundleId: '',
        // appCategoryType: '',
        // osxSign: '',
        // protocol: 'myapp://path',
        // Window only
        // win32metadata: { ... }
      },
      builder: {
        // https://www.electron.build/configuration/configuration
        // appId: 'quasar-app'
      }
    }
  }
}
