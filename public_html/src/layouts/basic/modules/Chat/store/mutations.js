/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
export default {
	setDialog(state, idOpen) {
		state.storage.dialog = idOpen
	},
	miniMode(state, isMini) {
		state.storage.miniMode = isMini
	},
	setLeftPanel(state, isOpen) {
		state.storage.leftPanel = isOpen
	},
	setRightPanel(state, isOpen) {
		state.storage.rightPanel = isOpen
	},
	setHistoryTab(state, isVisible) {
		state.storage.historyTab = isVisible
	},
	setSearchActive(state) {
		state.storage.isSearchActive = true
	},
	setSearchInactive(state) {
		state.storage.isSearchActive = false
	},
	setTab(state, tab) {
		state.storage.tab = tab
	},
	setSendByEnter(state, val) {
		state.storage.sendByEnter = val
	},
	setSoundNotification(state, val) {
		state.storage.isSoundNotification = val
	},
	setDesktopNotification(state, val) {
		state.storage.isDesktopNotification = val
	},
	setCoordinates(state, val) {
		state.storage.coordinates = val
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
		state.data.chatEntries = [...state.data.chatEntries, ...data.chatEntries]
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
		const chatStorage = Quasar.plugins.LocalStorage.getItem('yf-chat')
		if (
			chatStorage &&
			JSON.stringify(Object.keys(state.storage)) === JSON.stringify(Object.keys(JSON.parse(chatStorage)))
		) {
			state.storage = Object.assign(state.storage, JSON.parse(chatStorage))
		}
	}
}
