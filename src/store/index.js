/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

import Vue from 'vue'
import Vuex from 'vuex'
import Objects from 'src/utilities/Objects.js'
import ModuleLoader from '../ModuleLoader.js'
// modules
import Debug from './Debug/index.js'
import Env from './Env/index.js'
import Users from './Users/index.js'
import Menu from './Menu/index.js'
import Url from './Url/index.js'
import Language from './Language/index.js'

ModuleLoader.flattenModules(window.modules).modules.forEach(module => {
  module.component()
})

Vue.use(Vuex)

let modules = {
  Debug,
  Env,
  Menu,
  Url,
  Users,
  Language
}
let store = new Vuex.Store({
  modules,
  mutations: {
    ['Global/update'](state, payload) {
      state = Objects.mergeDeepReactive(state, payload)
    }
  }
})
export default store
