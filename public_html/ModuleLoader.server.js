/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
/**
 * Module loader
 *
 * @author Rafal Pospiech <r.pospiech@yetiforce.com>
 */
require = require('esm')(module)
const moduleAlias = require('module-alias')
const aliases = {
  src: `${__dirname}/src`,
  store: `${__dirname}/src/store`,
  components: `${__dirname}/src/components`,
  layouts: `${__dirname}/src/layouts`,
  modules: `${__dirname}/src/modules`,
  assets: `${__dirname}/src/assets`,
  statics: `${__dirname}/src/statics`,
  utilities: `${__dirname}/src/utilities`,
  services: `${__dirname}/src/services`,
  pages: `${__dirname}/src/pages`,
  Core: `${__dirname}/src/modules/Core}`,
  Base: `${__dirname}/src/modules/Base`,
  Settings: `${__dirname}/src/modules/Setting`,
  node_modules: `${__dirname}/node_modules`,
  '/src': `${__dirname}/src`,
  '/store': `${__dirname}/src/store`,
  '/components': `${__dirname}/src/components`,
  '/layouts': `${__dirname}/src/layouts`,
  '/modules': `${__dirname}/src/modules`,
  '/assets': `${__dirname}/src/assets`,
  '/statics': `${__dirname}/src/statics`,
  '/utilities': `${__dirname}/src/utilities`,
  '/services': `${__dirname}/src/services`,
  '/pages': `${__dirname}/src/pages`,
  '/Core': `${__dirname}/src/modules/Core}`,
  '/Base': `${__dirname}/src/modules/Base`,
  '/Settings': `${__dirname}/src/modules/Setting`,
  '/node_modules': `${__dirname}/node_modules`
}
moduleAlias.addAliases(aliases)
const { lstatSync, readdirSync, readFileSync, writeFileSync, watch } = require('fs')
const { join, resolve, sep, basename, normalize, parse } = require('path')

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

const isNotMin = source => {
  const path = parse(source)
  return (
    ['routes.min', 'actions.min', 'state.min', 'mutations.min', 'getters.min', 'index.min'].indexOf(path.name) === -1
  )
}

function addGlobals() {
  const globalLibs = {
    Vue: 'vue',
    Vuex: 'vuex',
    VuexClass: 'vuex-class.js',
    VueRouter: 'vue-router',
    VueI18n: 'vue-i18n',
    Quasar: 'quasar',
    axios: 'axios'
  }
  for (let globalLib in globalLibs) {
    global[globalLib] = require(globalLibs[globalLib])
  }
}
addGlobals()

global.moduleLoaderModules = []
global.addModuleLoaderModule = function(moduleName) {
  const sourceDir = normalize(join(__dirname, 'src'))
  if (moduleLoaderModules.indexOf(moduleName) === -1 && moduleName.substring(0, sourceDir.length) === sourceDir) {
    moduleLoaderModules.push(moduleName)
  }
}
/**
 * Helper function for reading esm modules in nodejs - because esm module is not perfect
 *
 * @param {string} moduleName
 */
const appRequire = (moduleName, getDefault = true, parent = null) => {
  const parts = moduleName.split('/')
  if (isFile(moduleName) || isFile('.' + moduleName)) {
    moduleName = resolve(moduleName)
  } else if (/\/node_modules\//gi.match(moduleName)) {
    moduleName = moduleName.replace(/\/node_modules\//gi, './node_modules/')
  } else if (isFile(join(__dirname, moduleName))) {
    moduleName = join(__dirname, moduleName)
  } else if (isFile(join(__dirname, 'node_modules', moduleName))) {
    moduleName = join(__dirname, 'node_modules', moduleName)
  } else if (isFile(join(__dirname, 'node_modules', moduleName, 'index.js'))) {
    moduleName = join(__dirname, 'node_modules', moduleName, 'index.js')
  } else if (parts.length > 1 && isFile(join('node_modules', parts[0], parts[1] + '.js'))) {
    moduleName = join(__dirname, 'node_modules', parts[0], parts[1] + '.js')
  } else if (parts.length > 1 && isFile(join('node_modules', parts[0], parts[1]))) {
    moduleName = join(__dirname, 'node_modules', parts[0], parts[1])
  } else if (isFile(join(__dirname, 'node_modules', '@' + moduleName, 'index.js'))) {
    moduleName = join(__dirname, 'node_modules', moduleName, 'index.js')
  } else if (isFile(join(__dirname, 'node_modules', moduleName, 'package.json'))) {
    const pkg = JSON.parse(
      readFileSync(join(__dirname, 'node_modules', moduleName, 'package.json'), { encoding: 'utf8' })
    )
    if (typeof pkg.main !== 'undefined') {
      moduleName = join(__dirname, 'node_modules', moduleName, pkg.main)
    } else if (typeof pkg.module !== 'undefined') {
      moduleName = join(__dirname, 'node_modules', moduleName, pkg.module)
    }
  }
  for (let alias in aliases) {
    if (moduleName.substring(0, alias.length) === alias) {
      moduleName = aliases[alias] + moduleName.substring(alias.length)
      break
    }
  }
  let file = readFileSync(moduleName, { encoding: 'utf8' })
    .replace(/export\sdefault\s/gi, 'module.exports = ')
    .replace(/export\s([^\s]+)\s/gi, 'module.exports.$1 = ')
    .replace(/import\s(.*)\sfrom\s(\'|\")([^\n]+)(\'|\")/gi, `const $1 = appRequire('$3')`)
    .replace(/export\s/gi, 'module.exports = ')
    .replace(
      /const\s(\{[^\}]+\})\s\=\sappRequire\([\'\"']{1}([^\'\"]+)[\'\"']{1}\)/gi,
      `const $1 = appRequire('$2', false)`
    )
  try {
    addModuleLoaderModule(normalize(moduleName))
    return eval(file)
  } catch (e) {
    const loaded = require(moduleName)
    if (typeof loaded.default !== 'undefined' && getDefault) {
      addModuleLoaderModule(normalize(moduleName))
      return loaded.default
    }
    addModuleLoaderModule(normalize(moduleName))
    return loaded
  }
}
// appRequire is a global function available from every js inside nodejs

global.appRequire = appRequire

const glob = require('glob')
const path = require('path')

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
 * ModuleLoader class
 */
class ModuleLoader {
  constructor() {
    this.log = true
    this.license =
      '/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */'
  }

  /**
   * Helper function which will merge objects recursively - creating brand new one - like clone
   *
   * @param {object} target
   * @params {object} sources
   * @returns {object}
   */
  mergeDeep(target, ...sources) {
    if (!sources.length) {
      return target
    }
    const source = sources.shift()
    if (this.isObject(target) && this.isObject(source)) {
      for (const key in source) {
        if (this.isObject(source[key])) {
          if (typeof target[key] === 'undefined') {
            Object.assign(target, { [key]: {} })
          }
          this.mergeDeep(target[key], source[key])
        } else if (Array.isArray(source[key])) {
          target[key] = source[key].map(item => {
            if (this.isObject(item)) {
              return this.mergeDeep({}, item)
            }
            return item
          })
        } else if (typeof source[key] === 'function') {
          if (source[key].toString().indexOf('[native code]') === -1) {
            target[key] = source[key]
          }
        } else {
          Object.assign(target, { [key]: source[key] })
        }
      }
    }
    return this.mergeDeep(target, ...sources)
  }

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
  }

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
      .filter(isNotMin)
      .map(name => name.substr(source.length + 1))
  }

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
  }

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
  }

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
  }

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
  }

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
    writeFileSync(`${dir}${sep}actions.js`, `${this.license}\nexport default ${JSON.stringify(store.actions, null, 2)}`)
    writeFileSync(
      `${dir}${sep}mutations.js`,
      `${this.license}\nexport default ${JSON.stringify(store.mutations, null, 2)}`
    )
    writeFileSync(`${dir}${sep}getters.js`, `${this.license}\nexport default ${JSON.stringify(store.getters, null, 2)}`)
  }

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
      this.getFiles(dir).forEach(file => {
        const which = basename(file, '.js')
        if (which === 'index') {
          return
        }
        const storeLib = appRequire(`./${dir}${sep}${file}`)
        if (which !== 'state') {
          moduleConf.store[which] = {}
          Object.keys(storeLib).forEach(key => {
            moduleConf.store[which][key] = `${moduleConf.fullName.replace(/\./g, '/')}/${key}`
          })
        }
      })
    }
    return moduleConf
  }

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
    if (this.log) {
      console.info(`${getSpacing(level)} \u25FC Loading modules from ${currentPath}.`)
    }
    return this.getDirectories(currentPath).map(moduleName => {
      if (this.log) {
        console.info(`${getSpacing(level)}  \u25B6 Module ${moduleName} found.`)
      }
      const moduleConf = {}
      moduleConf.parentHierarchy = parentHierarchy.replace(/^\.|\.$/i, '')
      moduleConf.fullName = (moduleConf.parentHierarchy + '.' + moduleName).replace(/^\.|\.$/i, '')
      moduleConf.name = moduleName
      moduleConf.path = `${baseDir}${sep}${RESERVED_DIRECTORIES.modules}${sep}${moduleName}`
      moduleConf.level = level
      moduleConf.parent = ''
      moduleConf.priority = 0
      moduleConf.autoLoad = true
      const configFile = join(__dirname, moduleConf.path, 'module.config.json')
      if (isFile(configFile)) {
        if (this.log) {
          console.log(`${getSpacing(level + 1)} - module config found`)
        }
        const conf = JSON.parse(readFileSync(configFile, { encoding: 'utf8' }))
        for (let prop in conf) {
          moduleConf[prop] = conf[prop]
        }
      }
      if (parent) {
        moduleConf.parent = parent.moduleName
        if (moduleConf.priority === 0 && typeof parent.childrenPriority !== 'undefined') {
          moduleConf.priority = parent.childrenPriority
        }
      }
      const entry = `${moduleConf.path}${sep}${moduleName}`
      if (isFile(resolve(entry + '.vue'))) {
        moduleConf.entry = entry + '.vue.js'
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
  }

  /**
   * Create object with route name as key recursively
   *
   * @param   {array}  routes
   *
   * @return  {object}
   */
  prepareRoutes(routes, currentPath = '', output = {}) {
    for (let route of routes) {
      const split = route.name.split('.')
      const shortName = split[split.length - 1]
      if (typeof output[shortName] === 'undefined') {
        output[shortName] = {
          path: '',
          name: route.name,
          routes: {}
        }
        if (route.path.substring(0, 1) === '/') {
          output[shortName].path = route.path
        } else {
          output[shortName].path = `${currentPath}/${route.path}`
        }
      }
      if (this.log) {
        console.log(output[shortName].path)
      }
      if (typeof route.children !== 'undefined') {
        this.mergeDeep(output[shortName], {
          routes: this.prepareRoutes(route.children, output[shortName].path, output[shortName].routes)
        })
      }
    }
    return output
  }

  /**
   * Prepare routes for saving
   *
   * @param   {array}  moduleConf
   *
   * @return  {object}
   */
  prepareModuleRoutes(moduleConf, routes = {}) {
    for (let module of moduleConf) {
      routes[module.name] = { routes: {} }
      if (typeof module.routes !== 'undefined') {
        routes[module.name].routes = this.prepareRoutes(module.routes, module.routes.path)
        if (typeof module.modules !== 'undefined') {
          routes[module.name].routes = this.prepareModuleRoutes(module.modules, routes[module.name].routes)
        }
      }
    }
    return routes
  }

  /**
   * Save module configuration file
   *
   * @param   {object}  moduleConf
   *
   * @return  {object}  moduleConf
   */
  saveModuleConfig(moduleConf) {
    const moduleConfiguration = `${this.license}\nwindow.modules = ${JSON.stringify(moduleConf, null, 2)}`
    writeFileSync(`./src/statics/modules.js`, moduleConfiguration)
    this.saveStoreNames('src/store', moduleConf)
    return moduleConf
  }
}

module.exports = new ModuleLoader()
