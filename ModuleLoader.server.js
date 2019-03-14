/**
 * Module loader
 *
 * @author Rafal Pospiech <r.pospiech@yetiforce.com>
 */
require = require('esm')(module)
const moduleAlias = require('module-alias')
moduleAlias.addAliases({
  src: `${__dirname}/src`,
  store: `${__dirname}/src/store`,
  components: `${__dirname}/src/components`,
  layouts: `${__dirname}/src/layouts`,
  modules: `${__dirname}/src/modules`,
  assets: `${__dirname}/src/assets`,
  statics: `${__dirname}/src/statics`,
  utilities: `${__dirname}/src/utilities`,
  services: `${__dirname}/src/services`,
  pages: `${__dirname}/src/pages`
})
const { lstatSync, readdirSync, readFileSync, writeFileSync, watch } = require('fs')
const { join, resolve, sep, basename } = require('path')

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

/**
 * Helper function for reading esm modules in nodejs - because esm module is not perfect
 *
 * @param {string} moduleName
 */
const appRequire = moduleName => {
  if (isFile(resolve(moduleName))) {
    moduleName = resolve(moduleName)
  } else if (isFile(resolve(['node_modules', moduleName]))) {
    moduleName = resolve(['node_modules', moduleName])
  } else if (isFile(resolve(['node_modules', moduleName, 'index.js']))) {
    moduleName = resolve(['node_modules', moduleName, 'index.js'])
  } else if (isFile(resolve(['node_modules', '@' + moduleName, 'index.js']))) {
    moduleName = resolve(['node_modules', moduleName, 'index.js'])
  } else if (isFile(resolve(['node_modules', moduleName, 'package.json']))) {
    const pkg = JSON.parse(readFileSync(resolve(['node_modules', moduleName, 'package.json']), { encoding: 'utf8' }))
    if (typeof pkg.main !== 'undefined') {
      moduleName = resolve(['node_modules', moduleName, pkg.main])
    } else if (typeof pkg.module !== 'undefined') {
      moduleName = resolve(['node_modules', moduleName, pkg.module])
    }
  }
  const file = readFileSync(moduleName, { encoding: 'utf8' })
    .replace(/export\sdefault\s/, 'module.exports = ')
    .replace(/import\s(.*)\sfrom\s(\'|\")([^\n]+)(\'|\")/gi, `const $1 = appRequire('$3')`)
    .replace(/export\s/gi, 'module.exports = ')
  try {
    return eval(file)
  } catch (e) {
    const loaded = require(moduleName)
    if (typeof loaded.default !== 'undefined') {
      return loaded.default
    }
    return loaded
  }
}
// appRequire is a global function available from every js inside nodejs
global.appRequire = appRequire

const Objects = appRequire('src/utilities/Objects.js')

function getSpacing(level) {
  let levelSpacing = ''
  for (let i = 0; i < level; i++) {
    levelSpacing += ' '
  }
  return levelSpacing
}

/**
 * Reserved directories inside modules
 */
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

/**
 * Main object
 */
module.exports = {
  /**
   * Get directories from specified dir
   *
   * @param   {string}  source
   *
   * @return  {array}
   */
  getDirectories(source) {
    return readdirSync(source)
      .map(name => join(source, name))
      .filter(isDirectory)
      .map(name => name.substr(source.length + 1))
  },

  /**
   * Get files from specified dir
   *
   * @param   {string}  source
   *
   * @return  {array}
   */
  getFiles(source) {
    return readdirSync(source)
      .map(name => join(source, name))
      .filter(isFile)
      .map(name => name.substr(source.length + 1))
  },

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
      this.getFiles(dir).forEach(file => {
        const path = `./${dir}${sep}${file}`
        let routes = appRequire(path)
        if (typeof routes === 'function') {
          routes = routes(moduleConf)
        }
        if (Array.isArray(routes)) {
          routes.forEach(route => {
            const routeProps = {}
            for (let routeProp in route) {
              if (typeof route[routeProp] !== 'function') {
                routeProps[routeProp] = route[routeProp]
              }
            }
            moduleConf.routes.push(routeProps)
          })
        } else {
          console.error(`  \u26D4  TypeError: routes should be an array, ${typeof routes} given.`)
        }
      })
    }
    return moduleConf
  },

  /**
   * Generate store names
   *
   * @param   {array}  modules
   * @param   {string}  type
   *
   * @return  {object}
   */
  generateStoreNames(modules, type, result = {}) {
    for (let module of modules) {
      if (typeof module.store !== 'undefined' && typeof module.store[type] !== 'undefined') {
        result[module.name] = module.store[type]
      } else {
        result[module.name] = {}
      }
      if (typeof module.modules !== 'undefined') {
        this.generateStoreNames(module.modules, type, result[module.name])
      }
    }
    return result
  },

  /**
   * Get names for getters / setters / actions
   *
   * @param   {object}  obj
   * @param   {object}  names
   *
   * @return  {object}
   */
  getNames(obj, moduleName, names = {}) {
    for (let name in obj) {
      let shortName = name
      if (shortName.indexOf('/') >= 0) {
        shortName = name.substring(name.lastIndexOf('/') + 1)
      }
      if (typeof obj[name] === 'function') {
        names[shortName] = `${moduleName}/${name}`
      } else if (typeof obj[name] === 'object') {
        names[shortName] = this.getNames(obj[name])
      } else {
        names[shortName] = obj[name]
      }
    }
    return names
  },

  /**
   * Get basic store names (from store dir)
   *
   * @param   {string}  dir
   *
   * @return  {object}
   */
  getBasicStoreNames(dir, result = { getters: {}, mutations: {}, actions: {} }) {
    this.getDirectories(join(__dirname, dir)).forEach(currentDir => {
      const gettersFileName = `${dir}/${currentDir}/getters.js`
      if (isFile(join(__dirname, gettersFileName))) {
        result['getters'][currentDir] = this.getNames(appRequire(gettersFileName), currentDir)
      }
      const mutationsFileName = `${dir}/${currentDir}/mutations.js`
      if (isFile(join(__dirname, mutationsFileName))) {
        result['mutations'][currentDir] = this.getNames(appRequire(mutationsFileName), currentDir)
      }
      const actionsFileName = `${dir}/${currentDir}/actions.js`
      if (isFile(join(__dirname, actionsFileName))) {
        result['actions'][currentDir] = this.getNames(appRequire(actionsFileName), currentDir)
      }
    })
    return result
  },

  /**
   * Save store names in getters, mutations, actions
   *
   * @param   {string}  dir
   * @param   {array}  modules
   */
  saveStoreNames(dir, modules) {
    const store = this.getBasicStoreNames(dir)
    store.getters = this.generateStoreNames(modules, 'getters', store.getters)
    store.mutations = this.generateStoreNames(modules, 'mutations', store.mutations)
    store.actions = this.generateStoreNames(modules, 'actions', store.actions)
    writeFileSync(`${dir}${sep}actions.js`, `export default ${JSON.stringify(store.actions, null, 2)}`)
    writeFileSync(`${dir}${sep}mutations.js`, `export default ${JSON.stringify(store.mutations, null, 2)}`)
    writeFileSync(`${dir}${sep}getters.js`, `export default ${JSON.stringify(store.getters, null, 2)}`)
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
      this.getFiles(dir).forEach(file => {
        const which = basename(file, '.js')
        if (which === 'index') {
          return
        }
        const storeLib = appRequire(`./${dir}${sep}${file}`)
        moduleConf.storeFiles[which] = `${dir}${sep}${file}`
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
  loadModules(baseDir, level = 0, parentHierarchy = '', parent = null) {
    const currentPath = join(__dirname, baseDir, RESERVED_DIRECTORIES.modules)
    console.info(`${getSpacing(level)} \u25FC Loading modules from ${currentPath}.`)
    return this.getDirectories(currentPath).map(moduleName => {
      console.info(`${getSpacing(level)}  \u25B6 Module ${moduleName} found.`)
      const moduleConf = {}
      moduleConf.parentHierarchy = parentHierarchy.replace(/^\.|\.$/i, '')
      moduleConf.fullName = (moduleConf.parentHierarchy + '.' + moduleName).replace(/^\.|\.$/i, '')
      moduleConf.name = moduleName
      moduleConf.path = `${baseDir}${sep}${RESERVED_DIRECTORIES.modules}${sep}${moduleName}`
      moduleConf.level = level
      moduleConf.parent = ''
      moduleConf.priority = 0
      const configFile = join(__dirname, moduleConf.path, 'module.config.json')
      if (isFile(configFile)) {
        console.log(`${getSpacing(level + 1)} - module config found`)
        const conf = JSON.parse(readFileSync(configFile, { encoding: 'utf8' }))
        for (let prop in conf) {
          moduleConf[prop] = conf[prop]
        }
      }
      if (parent) {
        moduleConf.parent = parent.moduleName
        moduleConf.priority = parent.priority
      }
      const entry = `${moduleConf.path}${sep}${moduleName}`
      if (isFile(resolve(entry + '.vue'))) {
        moduleConf.entry = entry + '.vue'
      } else if (isFile(resolve(entry + '.js'))) {
        moduleConf.entry = entry + '.js'
      }
      moduleConf.directories = this.getDirectories(moduleConf.path)
      this.loadRoutes(moduleConf)
      this.loadStore(moduleConf)
      if (moduleConf.directories.indexOf(RESERVED_DIRECTORIES.modules) !== -1) {
        moduleConf.modules = this.loadModules(
          moduleConf.path,
          level + 1,
          parentHierarchy + '.' + moduleName,
          moduleConf
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
    writeFileSync(`./src/statics/modules.js`, moduleConfiguration)
    this.saveStoreNames('src/store', moduleConf)
    return moduleConf
  },

  /**
   * Watch directory for changes and update modules.js configuration file
   *
   * @param   {string}  dir
   */
  watchDir(dir = './src') {
    const exclude = [
      `statics${sep}modules.js`,
      `store${sep}getters.js`,
      `store${sep}mutations.js`,
      `store${sep}actions.js`
    ]
    watch(dir, { recursive: true }, (eventType, fileName) => {
      if (eventType === 'change' && exclude.indexOf(fileName) === -1) {
        this.saveModuleConfig(this.loadModules('src'))
        console.log('Module configuration file updated.')
      }
    })
  }
}
