/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
export default {
	dialog(state) {
		return state.storage.dialog
	},
	miniMode(state) {
		return state.storage.miniMode
	},
	leftPanel(state) {
		return state.storage.leftPanel
	},
	rightPanel(state) {
		return state.storage.rightPanel
	},
	historyTab(state) {
		return state.storage.historyTab
	},
	isSearchActive(state) {
		return state.storage.isSearchActive
	},
	isSoundNotification(state) {
		return state.storage.isSoundNotification === null
			? state.config.isDefaultSoundNotification
			: state.storage.isSoundNotification
	},
	sendByEnter(state) {
		return state.storage.sendByEnter
	},
	tab(state) {
		return state.storage.tab
	},
	data(state) {
		return state.data
	},
	config(state) {
		return state.config
	},
	storage(state) {
		return state.storage
	},

	hasDesktopPermission(state) {
		if (state.storage.isDesktopNotification) {
			return false
		}
		if (PNotify.modules.Desktop.checkPermission() !== 0) {
			app.setCookie('chat-isDesktopNotification', false, 365)
			return false
		}
		return true
	}
}
