/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import Vue from 'vue'
import { get, set } from 'dot-prop'
import _serialize from 'serialize-javascript'
import babylon from 'prettier/parser-babylon'
import prettier from 'prettier/standalone'

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
        } else {
          Vue.set(target, key, source[key])
        }
      }
    }
    return this.mergeDeepReactive(target, ...sources)
  },
  /**
   * Get method from dot-prop
   */
  get,

  /**
   * Set method from dot-prop
   */
  set,

  /**
   * Set reacitve property in object from dot path
   *
   * @param   {object}  target  target object
   * @param   {string}  path    where to store path inside an object
   * @param   {any}     value
   *
   * @return  {object}          reactive object
   */
  setReactive(target, path, value) {
    return this.mergeDeepReactive(target, this.set(target, path, value))
  },

  /**
   * Serialize javascript
   */
  serialize(obj, options) {
    return prettier.format(_serialize(obj, options), { parser: 'babel', plugins: [babylon] })
  }
}
