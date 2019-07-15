/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

import ChatDialog from './components/Dialog.vue'
import store from 'store'
import moduleStore from './store'
Vue.mixin({
	methods: {
		translate(key) {
			return app.vtranslate(key)
		}
	}
})
window.ChatModalVueComponent = {
	component: ChatDialog,
	mount(config) {
		ChatDialog.state = config.state
		return new Vue({
			store,
			render: h => h(ChatDialog),
			beforeCreate() {
				store.registerModule('Chat', moduleStore)
				this.$store.commit('Chat/initStorage')
				store.subscribe((mutation, state) => {
					Quasar.plugins.LocalStorage.set('yf-chat', JSON.stringify(state.Chat.local))
					Quasar.plugins.SessionStorage.set('yf-chat', JSON.stringify(state.Chat.session))
				})
			}
		}).$mount(config.el)
	}
}
