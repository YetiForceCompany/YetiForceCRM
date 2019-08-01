/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import unionby from 'lodash.unionby'

export default {
	setDialog(state, idOpen) {
		state.session.dialog = idOpen
	},
	miniMode(state, isMini) {
		state.session.miniMode = isMini
	},
	setLeftPanel(state, isOpen) {
		state.session.leftPanel = isOpen
	},
	setRightPanel(state, isOpen) {
		state.session.rightPanel = isOpen
	},
	setHistoryTab(state, tab) {
		state.session.historyTab = tab
	},
	setSearchActive(state) {
		state.session.isSearchActive = true
	},
	setSearchInactive(state) {
		state.session.isSearchActive = false
	},
	setTab(state, tab) {
		state.session.tab = tab
	},
	setCoordinates(state, val) {
		state.session.coordinates = val
	},
	setSendByEnter(state, val) {
		state.local.sendByEnter = val
	},
	setSoundNotification(state, val) {
		state.local.isSoundNotification = val
	},
	setDesktopNotification(state, val) {
		state.local.isDesktopNotification = val
	},
	setData(state, data) {
		state.data = data
	},
	pushSended(state, data) {
		state.data.chatEntries.push(data.chatEntries.slice(-1)[0])
		state.data.showMoreButton = data.showMoreButton
		state.data.participants = data.participants
	},
	updateChat(state, data) {
		state.data.chatEntries = unionby(state.data.chatEntries, data.chatEntries, 'id')
		state.data.participants = data.participants
		state.data.roomList = data.roomList
	},
	updateRooms(state, data) {
		state.data.roomList = data
	},
	pushOlderEntries(state, data) {
		state.data.chatEntries.unshift(...data.chatEntries)
		state.data.showMoreButton = data.showMoreButton
	},
	setAmountOfNewMessages(state, val) {
		state.data.amountOfNewMessages = val
	},
	setPinned(state, { roomType, room }) {
		const roomList = state.data.roomList
		switch (roomType) {
			case 'crm':
				for (let i = 0; i < roomList.crm.length; i++) {
					if (roomList.crm[i] === room) {
						roomList.crm.pop(i)
						break
					}
				}
				break
			case 'group':
				for (let i = 0; i < roomList.group.length; i++) {
					if (roomList.group[i] === room) {
						roomList.group[i].isPinned = !roomList.group[i].isPinned
						break
					}
				}
				break
		}
	},
	setConfig(state, config) {
		state.config = config
	},
	initStorage(state) {
		const chatLocalStorage = Quasar.plugins.LocalStorage.getItem('yf-chat')
		const chatSessionStorage = Quasar.plugins.SessionStorage.getItem('yf-chat')
		if (
			chatLocalStorage &&
			JSON.stringify(Object.keys(state.local)) === JSON.stringify(Object.keys(JSON.parse(chatLocalStorage)))
		) {
			state.local = Object.assign(state.local, JSON.parse(chatLocalStorage))
		}
		if (
			chatSessionStorage &&
			JSON.stringify(Object.keys(state.session)) === JSON.stringify(Object.keys(JSON.parse(chatSessionStorage)))
		) {
			state.session = Object.assign(state.session, JSON.parse(chatSessionStorage))
		}
	}
}
