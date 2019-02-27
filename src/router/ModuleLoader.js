/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
const ModuleLoader = {
  /**
   * Attach route - recursive
   *
   * @param   {array}  routes  array of currently available routes
   * @param   {object}  route   route configuration
   */
  attachRoute(routes, route) {
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
  prepareRoutes(moduleName, routes) {
    return routes.map(route => {
      const routeItem = Object.assign({}, route)
      if (routeItem.componentPath.substr(0, 1) !== '/') {
        routeItem.component = () => import(`../modules/${moduleName}/${route.componentPath}`)
      } else {
        routeItem.component = () => import(`../${route.componentPath.substr(1)}`)
      }
      if (typeof route.children !== 'undefined') {
        routeItem.children = this.prepareRoutes(moduleName, route.children)
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
    const routes = this.prepareRoutes(moduleConf.name, moduleConf.routes)
    routes.forEach(route => {
      if (typeof route.parent === 'string') {
        this.attachRoute(currentRoutes, route)
      } else {
        currentRoutes.push(route)
      }
    })
  }
}

export default ModuleLoader
