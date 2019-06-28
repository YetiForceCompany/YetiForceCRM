/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
export default {
	dialog(state, idOpen) {
		state.dialog = idOpen
	},
	maximizedDialog(state, isMax) {
		state.maximizedDialog = isMax
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
	updateEntries(state, data) {
		state.data.chatEntries.push(data.chatEntries.slice(-1)[0])
		state.data.showMoreButton = data.showMoreButton
		state.data.participants = data.participants
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
