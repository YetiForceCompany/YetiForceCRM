/**
 * Module loader
 *
 * @author Rafal Pospiech <r.pospiech@yetiforce.com>
 */
require = require('esm')(module)
const { lstatSync, readdirSync, writeFileSync, watch } = require('fs')
const { join, resolve, sep, basename } = require('path')
const Objects = require('./src/utilities/Objects.js').default

const isDirectory = source => {
  try {
    return lstatSync(source).isDirectory()
  } catch (e) {
    return false
  }
}
const isFile = source => {
  try {
    return lstatSync(source).isFile()
  } catch (e) {
    return false
  }
}
const getDirectories = source =>
  readdirSync(source)
    .map(name => join(source, name))
    .filter(isDirectory)
    .map(name => name.substr(source.length + 1))
const getFiles = source =>
  readdirSync(source)
    .map(name => join(source, name))
    .filter(isFile)
    .map(name => name.substr(source.length + 1))

function getSpacing(level) {
  let levelSpacing = ''
  for (let i = 0; i < level; i++) {
    levelSpacing += ' '
  }
  return levelSpacing
}

const RESERVED_DIRECTORIES = {
  router: 'router',
  store: 'store',
  assets: 'assets',
  statics: 'statics',
  pages: 'pages',
  css: 'css',
  components: 'components',
  boot: 'boot',
  i18n: 'i18n',
  layouts: 'layouts',
  utilities: 'utilities',
  services: 'services',
  modules: 'modules'
}

module.exports = {
  /**
   * Load routes from module
   *
   * @param   {object}  moduleConf
   *
   * @returns  {moduleConf} moduleConf
   */
  loadRoutes(moduleConf) {
    if (moduleConf.directories.indexOf(RESERVED_DIRECTORIES.router) !== -1) {
      moduleConf.routes = []
      const dir = `${moduleConf.path}${sep}${RESERVED_DIRECTORIES.router}`
      getFiles(dir).forEach(file => {
        const path = `./${dir}${sep}${file}`
        const routes = require(path).default
        if (typeof routes === 'function') {
          routes = routes(moduleConf)
        }
        if (Array.isArray(routes)) {
          routes.forEach(route => {
            moduleConf.routes.push(route)
          })
        } else {
          console.error(`  \u26D4  TypeError: routes should be an array, ${typeof routes} given.`)
        }
      })
    }
    return moduleConf
  },

  /**
   * Load store from module
   *
   * @param   {object}  moduleConf
   *
   * @returns  {moduleConf} moduleConf
   */
  loadStore(moduleConf) {
    if (moduleConf.directories.indexOf(RESERVED_DIRECTORIES.store) !== -1) {
      const dir = `${moduleConf.path}${sep}${RESERVED_DIRECTORIES.store}`
      moduleConf.store = {}
      moduleConf.storeFiles = {}
      getFiles(dir).forEach(file => {
        const which = basename(file, '.js')
        const storeLib = require(`./${dir}${sep}${file}`).default
        let lib = {}
        if (which !== 'state') {
          Object.keys(storeLib).forEach(key => {
            lib[`${moduleConf.fullName.replace(/\./, '/')}/${key}`] = storeLib[key]
          })
        } else {
          lib = storeLib
        }
        moduleConf.storeFiles[which] = lib
        if (which !== 'state') {
          moduleConf.store[which] = {}
          Object.keys(storeLib).forEach(key => {
            moduleConf.store[which][key] = `${moduleConf.fullName.replace(/\./g, '/')}/${key}`
          })
        }
      })
    }
    return moduleConf
  },

  /**
   * Load modules, generate and save configuration file
   *
   * @param {string} baseDir
   * @param {object} modules
   *
   * @return  {object}  modules structure
   */
  loadModules(baseDir, level = 0, parentHierarchy = '', parent = '') {
    const currentPath = join(__dirname, baseDir, RESERVED_DIRECTORIES.modules)
    console.info(`${getSpacing(level)} \u25FC Loading modules from ${currentPath}.`)
    return getDirectories(currentPath).map(moduleName => {
      console.info(`${getSpacing(level)}  \u25B6 Module ${moduleName} found.`)
      const moduleConf = {}
      moduleConf.parentHierarchy = parentHierarchy.replace(/^\.|\.$/i, '')
      moduleConf.fullName = (moduleConf.parentHierarchy + '.' + moduleName).replace(/^\.|\.$/i, '')
      moduleConf.name = moduleName
      moduleConf.path = `${baseDir}${sep}${RESERVED_DIRECTORIES.modules}${sep}${moduleName}`
      moduleConf.level = level
      moduleConf.parent = parent
      const entry = `${moduleConf.path}${sep}${moduleName}`
      if (isFile(resolve(entry + '.vue'))) {
        moduleConf.entry = entry + '.vue'
      } else if (isFile(resolve(entry + '.js'))) {
        moduleConf.entry = entry + '.js'
      }
      moduleConf.directories = getDirectories(moduleConf.path)
      this.loadRoutes(moduleConf)
      this.loadStore(moduleConf)
      if (moduleConf.directories.indexOf(RESERVED_DIRECTORIES.modules) !== -1) {
        moduleConf.modules = this.loadModules(
          moduleConf.path,
          level + 1,
          parentHierarchy + '.' + moduleName,
          moduleName
        )
      }
      return moduleConf
    })
  },

  /**
   * Save module configuration file
   *
   * @param   {object}  moduleConf
   *
   * @return  {object}  moduleConf
   */
  saveModuleConfig(moduleConf) {
    const moduleConfiguration = `window.modules = ${Objects.serialize(moduleConf, { space: 2, unsafe: true })}`
    writeFileSync(`src/statics/modules.js`, moduleConfiguration)
    return moduleConf
  },

  /**
   * Watch directory for changes and update modules.js configuration file
   *
   * @param   {string}  dir
   */
  watchDir(dir = './src') {
    watch(dir, { recursive: true }, (eventType, fileName) => {
      if (eventType === 'change' && fileName !== `statics${sep}modules.js`) {
        this.saveModuleConfig(this.loadModules('src'))
        console.log('Module configuration file updated.')
      }
    })
  }
}
