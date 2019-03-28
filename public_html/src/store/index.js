/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import Objects from '/utilities/Objects.js'
let store = null
function createStore() {
  if (store === null) {
    Vue.use(Vuex)
    store = new Vuex.Store({
      mutations: {
        ['Global/update'](state, payload) {
          state = Objects.mergeDeepReactive(state, payload)
        }
      }
    })
  }
  return store
}

export default createStore
