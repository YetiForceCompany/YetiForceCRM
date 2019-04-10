/**
 * Notification store
 *
 * @description Notification store
 * @license YetiForce Public License 3.0
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
import state from './state.js'
import getters from './getters.js'
import mutations from './mutations.js'
import actions from './actions.js'

export default {
  namespaced: false,
  state,
  getters,
  mutations,
  actions
}
