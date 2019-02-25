import Vue from 'vue'
import Vuex from 'vuex'

import Base from './Base'
import Login from './Login'

Vue.use(Vuex)

/*
 * If not building with SSR mode, you can
 * directly export the Store instantiation
 */

export default function(/* { ssrContext } */) {
  const modules = {
    Base,
    Login
  }

  const Store = new Vuex.Store({
    modules
  })
  return Store
}
