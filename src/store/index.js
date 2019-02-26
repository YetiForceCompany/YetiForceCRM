import Vue from 'vue'
import Vuex from 'vuex'

import Base from './Base'
import Login from './Login'

Vue.use(Vuex)

/*
 * If not building with SSR mode, you can
 * directly export the Store instantiation
 */

/**
 * Helper function to determine if specified variable is an object
 *
 * @param {any} item
 * @returns {boolean}
 */
function isObject(item) {
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
function mergeDeepReactive(target, ...sources) {
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
      } else {
        Vue.set(target, key, source[key])
      }
    }
  }
  return mergeDeepReactive(target, ...sources)
}

export default function(/* { ssrContext } */) {
  const modules = {
    Base,
    Login
  }

  const Store = new Vuex.Store({
    modules,
    mutations: {
      update(state, payload) {
        state = mergeDeepReactive(state, payload)
      }
    }
  })
  return Store
}
