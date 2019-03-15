/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
export default {
  /**
   * Get logs
   *
   * @returns {function} getter function (type, moduleName)
   */
  get(state) {
    return (type = 'all', moduleName = '') => {
      return state[type].filter(log => {
        if (moduleName === '') {
          return true
        }
        return log.moduleName === moduleName
      })
    }
  }
}
