// Configuration for your app
const ModuleLoader = require('./ModuleLoader.server.js')
const exec = require('child_process').exec
const axios = require('axios')
const path = require('path')
module.exports = function(ctx) {
  ModuleLoader.saveModuleConfig(ModuleLoader.loadModules('src'))
  if (ctx.dev) {
    ModuleLoader.watchDir('./src')
  }
  return {
    // app boot file (/src/boot)
    // --> boot files are part of "main.js"
    boot: ['i18n', 'axios'],

    css: ['app.styl'],

    htmlVariables: {
      modulesFile: ctx.dev ? '/statics/modules.js' : '/dist/statics/modules.js'
    },

    extras: [
      'roboto-font',
      'material-icons', // optional, you are not bound to it
      // 'ionicons-v4',
      'mdi-v3'
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
      indexHtmlTemplate: ctx.dev ? 'src/index.template.dev.html' : 'src/index.template.php',
      rootComponent: 'src/Main.vue'
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
        if (typeof cfg.output === 'object' && !ctx.dev) {
          cfg.output.filename = 'js/[name].js'
          cfg.output.chunkFilename = 'js/[name].js'
        }
        if (process.argv.indexOf('--watch') >= 0) {
          cfg.watch = true
          cfg.watchOptions = {
            ignored: [/node_modules/, /public_html/]
          }
        }
        cfg.devtool = 'source-map' // for debugging purposes
        //overwriting css-loader localIdentName
        for (let i = 0; i < cfg.module.rules.length; i++) {
          const moduleRule = cfg.module.rules[i]
          if (moduleRule.oneOf !== undefined) {
            for (let i = 0; i < moduleRule.oneOf.length; i++) {
              const rule = moduleRule.oneOf[i]
              for (let i = 0; i < rule.use.length; i++) {
                const ruleLoader = rule.use[i]
                if (ruleLoader.loader === 'css-loader') {
                  ruleLoader.options.localIdentName = '[path][name]__[local]'
                }
              }
            }
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
        'Access-Control-Allow-Headers': '*'
      },
      before(app, server) {
        let baseURL = ''
        // get configuration by running index.php from command line
        // which will in return get dev server template with configuration from php
        app.all('/', (req, res) => {
          exec('php ' + __dirname + '/dev.php', function(error, stdout, stderr) {
            res.append('access-control-allow-origin', '*')
            res.append('access-control-allow-headers', '*')
            res.append('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, DELETE, OPTIONS')
            if (stderr) {
              console.error('PHP Server error', stderr)
              return res.end(stderr)
            }
            const matches = /data\-env\-url\=\"([^\"]+)\"/gi.exec(stdout)
            if (matches && matches.length > 1) {
              baseURL = matches[1]
            } else {
              console.error('No baseURL inside template', stdout)
            }
            res.end(stdout)
          })
        })
        // catch login and api.php request and forward it to php server
        app.all('/login.php', (req, res) => {
          axios
            .post(baseURL + '/login.php', req.body)
            .then(function(response) {
              for (let headerName in response.headers) {
                res.append(headerName, response.headers[headerName])
              }
              if (response.status === 200) {
                res.json(response.data)
              } else {
                res.status(response.status).end(response.statusText)
              }
            })
            .catch(function(error) {
              res.end(error)
            })
        })
        app.all('/api.php', (req, res) => {
          axios
            .post(baseURL + '/api.php', req.body)
            .then(function(response) {
              for (let headerName in response.headers) {
                res.append(headerName, response.headers[headerName])
              }
              if (response.status === 200) {
                res.json(response.data)
              } else {
                res.status(response.status).end(response.statusText)
              }
            })
            .catch(function(error) {
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
