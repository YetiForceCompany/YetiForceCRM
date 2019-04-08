/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import _get from '/node_modules/dot-prop-wild/dist/es/index.js'
import _set from '/node_modules/set-nested-prop/src/index.js'

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
   * Get data from object with path with dot notation
   *
   * @param {object} data
   * @param {string} path
   *
   * @returns {any} value
   */
  get(data, path) {
    return _get(data, path).value
  },

  /**
   * Set data in object with path with dot notation
   *
   * @param {object} data
   * @param {string} path
   * @param {any} value
   */
  set(data, path, value) {
    return _set(data, path, value, { force: true, mut: true })
  },

  /**
   * Set reactive property in object from dot path
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
  },

  /**
   * Strip private properties of the object - recursive
   *
   * @param {array|object} value
   * @param {string} childrenProperty - where to find children - property name
   * @param {string} private property prefix - to recognize it is private and should be striped
   *
   * @returns {object|array} without private properties
   */
  stripPrivate(value, childrenProperty = 'children', prefix = '$_', _cacheValue = [], _cacheResult = []) {
    const cacheIndex = _cacheValue.indexOf(value)
    if (cacheIndex >= 0) {
      return _cacheResult[cacheIndex]
    }
    if (Array.isArray(value)) {
      const result = value.map(node => {
        return this.stripPrivate(node, childrenProperty, prefix, _cacheValue, _cacheResult)
      })
      _cacheValue.push(value)
      _cacheResult.push(result)
      return result
    }
    const result = {}
    for (let key in value) {
      if (key.substr(0, prefix.length) !== prefix && key !== childrenProperty) {
        result[key] = value[key]
      }
    }
    _cacheValue.push(value)
    _cacheResult.push(result)
    if (value.propertyIsEnumerable(childrenProperty)) {
      result[childrenProperty] = this.stripPrivate(
        value[childrenProperty],
        childrenProperty,
        prefix,
        _cacheValue,
        _cacheResult
      )
    }
    return result
  }
}
