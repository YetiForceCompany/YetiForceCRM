/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import get from 'lodash.get'

export default {
	layout(state) {
		return state.layout
	},
	dialog(state) {
		return state.session.dialog
	},
	miniMode(state) {
		return state.session.miniMode
	},
	mobileMode(state, getters) {
		return getters.coordinates.width < getters.layout.drawer.breakpoint && getters.miniMode
	},
	leftPanel(state) {
		return state.session.leftPanel
	},
	leftPanelMobile(state) {
		return state.session.leftPanelMobile
	},
	rightPanel(state) {
		return state.session.rightPanel
	},
	rightPanelMobile(state) {
		return state.session.rightPanelMobile
	},
	historyTab(state) {
		return state.session.historyTab
	},
	isSearchActive(state) {
		return state.session.isSearchActive
	},
	tab(state) {
		return state.session.tab
	},
	coordinates(state) {
		return state.session.coordinates
	},
	buttonCoordinates(state) {
		return state.session.buttonCoordinates
	},
	isSoundNotification(state) {
		return state.local.isSoundNotification === null ? state.config.isDefaultSoundNotification : state.local.isSoundNotification
	},
	roomSoundNotificationsOff(state) {
		return state.local.roomSoundNotificationsOff
	},
	sendByEnter(state) {
		return state.local.sendByEnter
	},

	isDesktopNotification(state) {
		return state.local.isDesktopNotification
	},
	roomsExpanded(state) {
		return state.local.roomsExpanded
	},
	data(state) {
		return state.data
	},
	allRooms(state) {
		return Object.values(get(state, 'data.roomList.crm', {})).concat(
			Object.values(get(state, 'data.roomList.group', {})),
			Object.values(get(state, 'data.roomList.global', {})),
			Object.values(get(state, 'data.roomList.private', {})),
			Object.values(get(state, 'data.roomList.user', {}))
		)
	},
	currentRoomData(state, getters) {
		const currentRoom = getters.data.currentRoom
		if (state.data.roomList === undefined || !currentRoom.roomType) {
			return {}
		}
		return state.data.roomList[currentRoom.roomType][currentRoom.recordId] || {}
	},
	config(state) {
		return state.config
	},
	activeRoomTypes(state, getters) {
		return getters.config.activeRoomTypes
	},
	getDetailPreview(state) {
		return state.config.detailPreview
	},
	getInterval(state, getters) {
		return getters.dialog ? getters.config.refreshMessageTime : getters.config.refreshTimeGlobal
	},
}
