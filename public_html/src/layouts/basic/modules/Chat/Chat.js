/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import ChatDialog from './views/Dialog.vue'
import ChatRecordRoom from './views/RecordRoom.vue'
import YfIcon from 'components/YfIcon.vue'
import store from 'store'
import moduleStore from './store'

Vue.config.productionTip = false

let isModuleInitialized = false

Vue.component('YfIcon', YfIcon)
Vue.mixin({
	methods: {
		translate(key) {
			return app.vtranslate(key)
		},
	},
})
function initChat() {
	return new Promise((resolve, reject) => {
		if (!isModuleInitialized) {
			store.registerModule('Chat', moduleStore)
			store.dispatch('Chat/fetchChatConfig')
			isModuleInitialized = true
			resolve()
		} else {
			resolve()
		}
	})
}

let recordChatComponent
window.ChatRecordRoomVueComponent = {
	component: ChatRecordRoom,
	mount(config) {
		ChatRecordRoom.state = config.state
		recordChatComponent = () => {
			return new Vue({
				store,
				config: config,
				render: (h) => h(ChatRecordRoom),
				recordId: app.getRecordId(),
				beforeCreate() {
					this.$store.dispatch('Chat/fetchRecordRoom', this.$options.recordId)
				},
			})
		}
		if (isModuleInitialized) {
			recordChatComponent = recordChatComponent()
			recordChatComponent.$mount(recordChatComponent.$options.config.el)
		}
	},
}
window.ChatModalVueComponent = {
	component: ChatDialog,
	mount(config) {
		ChatDialog.state = config.state
		return new Vue({
			store,
			render: (h) => h(ChatDialog),
			beforeCreate() {
				initChat().then((e) => {
					this.$store.commit('Chat/initStorage')
					store.subscribe((mutation, state) => {
						if (mutation.type !== 'Chat/updateChatData' && mutation.type !== 'Chat/setAmountOfNewMessages') {
							Quasar.plugins.LocalStorage.set('yf-chat', JSON.stringify(state.Chat.local))
							Quasar.plugins.SessionStorage.set('yf-chat', JSON.stringify(state.Chat.session))
						}
					})
					if (recordChatComponent) {
						recordChatComponent = recordChatComponent()
						recordChatComponent.$mount(recordChatComponent.$options.config.el)
					}
				})
			},
		}).$mount(config.el)
	},
}
