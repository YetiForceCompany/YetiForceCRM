/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import Vue from 'vue'
import { get, set } from 'dot-prop'

export default {
  /**
   * Helper function to determine if specified variable is an object
   *
   * @param {any} item
   * @returns {boolean}
   */
  isObject(item) {
    return item && typeof item === 'object' && !Array.isArray(item)
  },

  /**
   * Helper function to merge multiple objects with reactivity enabled
   *
   * @param   {object}  target target object
   * @param   {object[]}  source objects
   *
   * @return  {object}  merged object
   */
  mergeDeepReactive(target, ...sources) {
    if (!sources.length) {
      return Vue.observable(target)
    }
    const source = sources.shift()
    if (this.isObject(target) && this.isObject(source)) {
      for (const key in source) {
        if (this.isObject(source[key])) {
          if (typeof target[key] === 'undefined') {
            Vue.set(target, key, {})
          }
          this.mergeDeepReactive(target[key], source[key])
        }
        if (Array.isArray(source[key])) {
          Vue.set(
            target,
            key,
            source[key].map(item => {
              if (this.isObject(item)) {
                return this.mergeDeepReactive({}, item)
              }
              return item
            })
          )
        } else {
          Vue.set(target, key, source[key])
        }
      }
    }
    return this.mergeDeepReactive(target, ...sources)
  },

  get,

  set,

  /**
   * Set reacitve property in object from dot path
   *
   * @param   {object}  target  target object
   * @param   {string}  path    where to store path inside an object
   * @param   {any}     value
   *
   * @return  {object}          reactive objecct
   */
  setReactive(target, path, value) {
    return this.mergeDeepReactive(target, this.set(target, path, value))
  }
}
