/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import Vue from 'vue'
/**
 * Helper function to determine if specified variable is an object
 *
 * @param {any} item
 * @returns {boolean}
 */
export function isObject(item) {
  return item && typeof item === 'object' && !Array.isArray(item)
}

/**
 * Helper function to merge multiple objects with reactivity enabled
 *
 * @param   {object}  target target object
 * @param   {object[]}  source objects
 *
 * @return  {object}  merged object
 */
export function mergeDeepReactive(target, ...sources) {
  if (!sources.length) {
    return target
  }
  const source = sources.shift()
  if (isObject(target) && isObject(source)) {
    for (const key in source) {
      if (isObject(source[key])) {
        if (typeof target[key] === 'undefined') {
          Vue.set(target, key, {})
        }
        mergeDeepReactive(target[key], source[key])
      }
      if (Array.isArray(source[key])) {
        Vue.set(
          target,
          key,
          source[key].map(item => {
            if (isObject(item)) {
              return mergeDeepReactive({}, item)
            }
            return item
          })
        )
      } else {
        Vue.set(target, key, source[key])
      }
    }
  }
  return mergeDeepReactive(target, ...sources)
}

export default {
  isObject,
  mergeDeepReactive
}
