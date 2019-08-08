/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

import ChatDialog from './views/Dialog.vue'
import ChatRecordRoom from './views/RecordRoom.vue'
import Icon from 'components/Icon.vue'
import store from 'store'
import moduleStore from './store'
let isModuleRegistered = false
Vue.component('icon', Icon)
Vue.mixin({
	methods: {
		translate(key) {
			return app.vtranslate(key)
		}
	}
})
store.registerModule('Chat', moduleStore)

window.ChatModalVueComponent = {
	component: ChatDialog,
	mount(config) {
		ChatDialog.state = config.state
		return new Vue({
			store,
			render: h => h(ChatDialog),
			beforeCreate() {
				this.$store.commit('Chat/initStorage')
				store.subscribe((mutation, state) => {
					if (mutation.type !== 'Chat/updateChat' && mutation.type !== 'Chat/setAmountOfNewMessages') {
						Quasar.plugins.LocalStorage.set('yf-chat', JSON.stringify(state.Chat.local))
						Quasar.plugins.SessionStorage.set('yf-chat', JSON.stringify(state.Chat.session))
					}
				})
			}
		}).$mount(config.el)
	}
}
window.ChatRecordRoomVueComponent = {
	component: ChatRecordRoom,
	mount(config) {
		ChatRecordRoom.state = config.state
		return new Vue({
			store,
			render: h => h(ChatRecordRoom),
			recordId: app.getRecordId(),
			beforeCreate() {
				this.$store.dispatch('Chat/fetchRecordRoom', this.$options.recordId)
			}
		}).$mount(config.el)
	}
}
