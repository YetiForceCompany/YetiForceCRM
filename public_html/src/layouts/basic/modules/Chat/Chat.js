import ChatDialog from './components/Dialog.vue'
import store from 'store'
import moduleStore from './store'
store.registerModule('Chat', moduleStore)

window.ChatVueComponent = {
	component: ChatDialog,
	mount(config) {
		ChatDialog.state = config.state
		return new Vue({
			store,
			render: h => h(ChatDialog)
		}).$mount(config.el)
	}
}
