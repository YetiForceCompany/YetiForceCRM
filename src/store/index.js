/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

import Vue from 'vue'
import Vuex from 'vuex'
import Objects from 'utilities/Objects.js'

Vue.use(Vuex)

let store = new Vuex.Store({
  mutations: {
    ['Global/update'](state, payload) {
      state = Objects.mergeDeepReactive(state, payload)
    }
  }
})
export default store
