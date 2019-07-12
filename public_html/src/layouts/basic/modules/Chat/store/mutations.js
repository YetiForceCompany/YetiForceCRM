/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
export default {
	dialog(state, idOpen) {
		state.dialog = idOpen
	},
	miniMode(state, isMini) {
		state.miniMode = isMini
	},
	setLeftPanel(state, isOpen) {
		state.leftPanel = isOpen
	},
	setRightPanel(state, isOpen) {
		state.rightPanel = isOpen
	},
	setHistoryTab(state, isVisible) {
		state.historyTab = isVisible
	},
	setData(state, data) {
		state.data = data
	},
	setConfig(state, config) {
		state.config = config
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
	setSearchActive(state) {
		state.isSearchActive = true
	},
	setSearchInactive(state) {
		state.isSearchActive = false
	},
	setTab(state, tab) {
		state.tab = tab
	},
	setSendByEnter(state, val) {
		state.config.sendByEnter = val
	},
	setSoundNotification(state, val) {
		state.config.isSoundNotification = val
	},
	setDesktopNotification(state, val) {
		state.config.isDesktopNotification = val
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
	}
}
