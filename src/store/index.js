import Vue from 'vue'
import Vuex from 'vuex'
import Objects from '../utilities/Objects.js'

// modules
import Base from './Base'
import Login from './Login'

Vue.use(Vuex)

export default function(/* { ssrContext } */) {
  const modules = {
    Base,
    Login
  }

  const Store = new Vuex.Store({
    modules,
    mutations: {
      UPDATE(state, payload) {
        state = Objects.mergeDeepReactive(state, payload)
      }
    }
  })
  return Store
}
