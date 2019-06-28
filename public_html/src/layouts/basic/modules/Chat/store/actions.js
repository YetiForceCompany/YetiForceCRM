/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

export default {
	setDialog({ commit }, isOpen) {
		commit('dialog', isOpen)
	},
	maximize({ commit }, isMax) {
		commit('maximizedDialog', isMax)
	},
	toggleLeftPanel({ commit, getters }) {
		commit('setLeftPanel', !getters['leftPanel'])
	},
	toggleRightPanel({ commit, getters }) {
		commit('setRightPanel', !getters['rightPanel'])
	},
	toggleHistoryTab({ commit, getters }) {
		commit('setHistoryTab', !getters['historyTab'])
	},
	fetchData({ commit, getters }) {
		AppConnector.request({
			module: 'Chat',
			action: 'ChatAjax',
			mode: 'data'
		}).done(({ result }) => {
			commit('setData', result)
		})
	},
	fetchRoom({ commit, getters }, options) {
		AppConnector.request({
			module: 'Chat',
			action: 'ChatAjax',
			mode: 'getEntries',
			recordId: options.id,
			roomType: options.roomType
		}).done(({ result }) => {
			let tempData = Object.assign({}, getters['data'])
			commit('setData', Object.assign(tempData, result))
		})
	},
	sendMessage({ commit, getters }, text) {
		return new Promise((resolve, reject) => {
			AppConnector.request({
				module: 'Chat',
				action: 'ChatAjax',
				mode: 'send',
				roomType: getters.data.currentRoom.roomType,
				recordId: getters.data.currentRoom.recordId,
				message: text,
				mid: getters.data.chatEntries.slice(-1)[0]['id']
			}).done(({ result }) => {
				commit('updateEntries', result)
				resolve(result)
			})
		})
	},
	togglePinned({ commit }, { roomType, room }) {
		console.log(roomType, room)
		const mode = room.isPinned || roomType === 'crm' ? 'removeFromFavorites' : 'addToFavorites'
		commit('setPinned', { roomType, room })
		AppConnector.request({
			module: 'Chat',
			action: 'Room',
			mode: mode,
			roomType: roomType,
			recordId: room.recordid
		})
	},
	getMore({ commit, getters }) {
		// clearTimeout(this.timerMessage);
		AppConnector.request(
			{
				module: 'Chat',
				action: 'ChatAjax',
				mode: 'getMore',
				lastId: getters.data.chatEntries[0].id,
				roomType: getters.data.currentRoom.roomType,
				recordId: getters.data.currentRoom.recordId
			},
			false
		).done(data => {
			console.log(data)
		})
	}
}
