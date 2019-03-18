/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
const ModuleLoader = {
  /**
   * Get path without 'src/' prefix
   *
   * @param   {string}  path
   *
   * @return  {string}
   */
  getPath(path) {
    if (path.substring(0, 3) === 'src') {
      return path.substring(4).replace(/\\/gi, '/')
    } else if (path.substring(2, 5) === 'src') {
      return path.substring(6).replace(/\\/gi, '/')
    }
    if (typeof path === 'string') {
      return path.replace(/\\/gi, '/')
    }
    console.error('unknown path', path)
  },

  /**
   * Attach route - recursive
   *
   * @param   {array}  routes  array of currently available routes
   * @param   {object}  route   route configuration
   * @param   {object}  moduleConf
   */
  attachRoute(routes, route, moduleConf) {
    for (const parentRoute of routes) {
      if (parentRoute.name === route.parent) {
        if (typeof parentRoute.children === 'undefined') {
          parentRoute.children = []
        }
        parentRoute.children.push(route)
      } else {
        if (typeof parentRoute.children !== 'undefined') {
          this.attachRoute(parentRoute.children, route)
        }
      }
    }
  },

  /**
   * Prepare route - replace componentPath into component property with dynamic import
   *
   * @param {object} moduleConf
   */
  prepareRoutes(module, routes) {
    if (!routes) {
      return []
    }
    return routes.map(route => {
      const routeItem = { ...route }
      routeItem.children = []
      if (typeof routeItem.componentPath === 'undefined') {
        return routeItem
      }
      if (routeItem.componentPath.substring(0, 1) !== '/') {
        routeItem.component = () => import(`src/${this.getPath(module.path)}/${route.componentPath}`)
      } else {
        routeItem.component = () => import(`src/${route.componentPath.substring(1)}`)
      }
      if (typeof route.children !== 'undefined') {
        this.prepareRoutes(module, route.children).forEach(route => routeItem.children.push(route))
      }
      return routeItem
    })
  },

  /**
   * Attach routes to currently defined routes
   *
   * @param   {array}  currentRoutes  current routes configuration
   * @param   {object}  moduleRoutes   routes defined by module
   */
  attachRoutes(currentRoutes, moduleConf) {
    const routes = this.prepareRoutes(moduleConf, typeof moduleConf !== 'undefined' ? moduleConf.routes : [])
    routes.forEach(route => {
      if (typeof route.parent === 'string') {
        this.attachRoute(currentRoutes, route, moduleConf)
      } else {
        currentRoutes.push(route)
      }
    })
  },

  /**
   * Load routes
   *
   * @param   {array}  routes
   * @param   {array}  modules
   *
   */
  loadRoutes(routes, modules) {
    for (const module of modules) {
      this.attachRoutes(routes, module)
      if (typeof module.modules !== 'undefined') {
        this.loadRoutes(routes, module.modules)
      }
    }
    return routes
  },

  /**
   * Get concrete module from array of nested modules
   *
   * @param   {string}  fullModuleName
   * @param   {array}  modules
   *
   * @return  {object}
   */
  getModule(fullModuleName, modules) {
    for (let module of modules) {
      if (module.fullName === fullModuleName) {
        return module
      }
      if (typeof module.modules !== 'undefined') {
        let found = null
        if ((found = this.getModule(fullModuleName, module.modules))) {
          return found
        }
      }
    }
    return null
  },

  /**
   * Prepare store names for easier store creation process
   *
   * @param   {string}  fullModuleName
   * @param   {object}  store
   *
   * @return  {object} store with modified property names (full ones)
   */
  prepareStoreNames(fullModuleName, store) {
    const module = this.getModule(fullModuleName, window.modules)
    if (typeof module.store === 'undefined') {
      return store
    }
    const updatedStore = { ...store }
    for (let which in store) {
      if (typeof module.store[which] === 'undefined') {
        continue
      }
      updatedStore[which] = {}
      for (let prop in store[which]) {
        updatedStore[which][module.store[which][prop]] = store[which][prop]
      }
    }
    return updatedStore
  },

  /**
   * Private flat array of all modules
   *
   * @param   {array}  modules
   * @param   {object}  flat
   *
   * @return  {array}
   */
  _flattenModules(modules, flat = { components: {}, modules: [] }) {
    for (const module of modules) {
      if (typeof module.entry !== 'undefined') {
        const modulePath = this.getPath(module.entry)
        flat.components[module.name] = () => import(`src/${modulePath}`)
        flat.modules.push({
          component: flat.components[module.name],
          ...module
        })
      }
      if (typeof module.modules !== 'undefined') {
        flat = this._flattenModules(module.modules, flat)
      }
    }
    return flat
  },

  /**
   * Flatten modules
   *
   * @param   {array}  modules
   *
   * @return  {array}
   */
  flattenModules(modules, forAutoLoad = true) {
    const flat = this._flattenModules(modules)
    flat.modules.sort((a, b) => {
      return b.priority - a.priority
    })
    if (forAutoLoad) {
      flat.modules = flat.modules.filter(module => {
        if (!module.autoLoad) {
          delete flat.components[module.fullName]
          return false
        }
        return true
      })
    }
    return flat
  }
}

export default ModuleLoader
