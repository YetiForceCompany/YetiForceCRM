/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
export default {
	dialog(state) {
		return state.dialog
	},
	maximizedDialog(state) {
		return state.maximizedDialog
	},
	leftPanel(state) {
		return state.leftPanel
	},
	rightPanel(state) {
		return state.rightPanel
	},
	historyTab(state) {
		return state.historyTab
	},
	isSearchActive(state) {
		return state.isSearchActive
	},
	tab(state) {
		return state.tab
	},
	data(state) {
		return state.data
	},
	config(state) {
		return state.config
	},

	hasDesktopPermission(state) {
		if (state.config.isDesktopNotification) {
			return false
		}
		if (!state.config.isNotificationPermitted) {
			app.setCookie('chat-isDesktopNotification', false, 365)
			return false
		}
		return true
	}
}
