/**
 * Vuex store
 *
 * @description Vuex store initialization
 * @license YetiForce Public License 6.5
 * @author Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */

import Vuex from 'vuex'

Vue.use(Vuex)

const debug = process.env.NODE_ENV !== 'production'
if (window.vuexStore === undefined) {
	window.vuexStore = new Vuex.Store({
		strict: debug,
	})
}
export default window.vuexStore
