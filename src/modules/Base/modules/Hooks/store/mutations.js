/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import Objects from 'utilities/Objects.js'
export default {
  /**
   * Add component to specified hook
   *
   * @param   {object}  state
   * @param   {string}  hookName
   * @param   {object|function} component
   */
  add(state, { hookName, component }) {
    let components = Objects.get(state, hookName)
    if (typeof components === 'undefined' || !components || !Array.isArray(components)) {
      Objects.setReactive(state, hookName, [])
      components = Objects.get(state, hookName)
    }
    components.push(component)
  },

  /**
   * Remove component from specified hook
   *
   * @param   {object}  state
   * @param   {string}  hookName
   * @param   {object|function}  component
   */
  remove(state, { hookName, component }) {
    let components = Objects.get(state, hookName)
    if (Array.isArray(components)) {
      Objects.set(state, hookName, components.filter(currentComponent => currentComponent !== component))
    }
  }
}
