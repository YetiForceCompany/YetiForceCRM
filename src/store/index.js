/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

import Vue from 'vue'
import Vuex from 'vuex'
import Objects from 'utilities/Objects.js'
import ModuleLoader from '../ModuleLoader.js'
// modules
import Debug from 'store/Debug/index.js'
import Env from 'store/Env/index.js'
import Users from 'store/Users/index.js'
import Menu from 'store/Menu/index.js'
import Url from 'store/Url/index.js'
import Language from 'store/Language/index.js'

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
