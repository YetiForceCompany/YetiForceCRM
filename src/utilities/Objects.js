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
  },

  /**
   * Convert array of object with keys to associative array
   *
   * @param   {array}  array
   * @param   {string} key
   * @param   {string} nestedKey
   *
   * @return  {object}
   */
  arrayToAssoc(array, key, nestedKey, output = {}) {
    for (let item of array) {
      output[item[key]] = item
      if (typeof item[nestedKey] !== 'undefined' && Array.isArray(item[nestedKey])) {
        output[item[key]][nestedKey] = this.arrayToAssoc(item[nestedKey], key, nestedKey, output[item[key]][nestedKey])
      }
    }
    return output
  },

  /**
   * Create array like tree structure from object
   *
   * @param   {object}  assoc
   * @param   {array}  keep keep those keys
   * @param   {string}  nestedKey
   *
   * @return  {array}
   */
  assocToArray(assoc, keep, nestedKey, array = []) {
    const current = {}
    for (let key of Object.keys(assoc)) {
      if (keep.indexOf(key) === -1) {
        if (typeof current[nestedKey] === 'undefined') {
          current[nestedKey] = []
        }
        this.assocToArray(assoc[key], keep, nestedKey, current[nestedKey])
      } else {
        current[key] = assoc[key]
      }
    }
    array.push(current)
    return array
  },

  /**
   * Convert object to flat array of items
   *
   * @param   {object}  assoc
   * @param   {array}  keep keys to keep
   *
   * @return  {array}
   */
  assocToFlatArray(assoc, keep, array = []) {
    const current = {}
    const haveKeep = Object.keys(assoc).filter(key => keep.indexOf(key) !== -1)
    if (haveKeep.length) {
      array.push(current)
    }
    for (let key of Object.keys(assoc)) {
      if (keep.indexOf(key) === -1) {
        this.assocToFlatArray(assoc[key], keep, array)
      } else {
        current[key] = assoc[key]
      }
    }
    return array
  },

  /**
   * Convert flat one dimensional array into array with children arrays
   *
   * @param   {array}  flat
   * @param   {string}  idKey id property for elements in array
   * @param   {string}  parentKey parent id property
   * @param   {string}  childrenKey where to store children
   * @param   {any}  rootId root node id
   *
   * @return  {array}
   */
  flatArrayToAssocArray(flat, idKey, parentKey, childrenKey, rootId, assoc = { [idKey]: rootId, [childrenKey]: [] }) {
    for (let item of flat) {
      if (item[parentKey] === assoc[idKey]) {
        const itemCopy = { ...item }
        assoc[childrenKey].push(itemCopy)
        itemCopy[childrenKey] = []
        this.flatArrayToAssocArray(flat, idKey, parentKey, childrenKey, rootId, itemCopy)
      }
    }
    return assoc
  }
}
