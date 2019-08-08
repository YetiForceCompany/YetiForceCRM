/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */

export default {
	maximize({ commit }, isMini) {
		commit('miniMode', isMini)
	},
	toggleLeftPanel({ commit, getters }) {
		commit('setLeftPanel', !getters['leftPanel'])
	},
	toggleRightPanel({ commit, getters }) {
		commit('setRightPanel', !getters['rightPanel'])
	},
	toggleRoomSoundNotification({ commit, getters }, { roomType, id }) {
		if (getters.roomSoundNotificationsOff[roomType].includes(id)) {
			commit('removeRoomSoundNotificationsOff', { roomType, id })
		} else {
			commit('addRoomSoundNotificationsOff', { roomType, id })
		}
	},
	fetchChatConfig({ commit }) {
		return new Promise((resolve, reject) => {
			AppConnector.request({
				module: 'Chat',
				action: 'ChatAjax',
				mode: 'getChatConfig'
			}).done(({ result }) => {
				commit('setConfig', result.config)
				commit('setAmountOfNewMessagesByRoom', result.roomList)
				resolve(result)
			})
		})
	},
	fetchRoom(
		{ commit, getters },
		options = { id: getters.data.currentRoom.recordId, roomType: getters.data.currentRoom.roomType }
	) {
		return new Promise((resolve, reject) => {
			AppConnector.request({
				module: 'Chat',
				action: 'ChatAjax',
				mode: 'getMessages',
				recordId: options.id,
				roomType: options.roomType
			}).done(({ result }) => {
				// const room = options.id === undefined ? result.currentRoom : options
				// let tempData = Object.assign({}, getters.data.roomList[room.roomType][room.id])
				commit('setData', result)
				resolve(result)
			})
		})
	},
	sendMessage({ commit, getters }, { text, roomType, recordId }) {
		const lastEntries = getters.data.roomList[roomType][recordId].chatEntries.slice(-1)[0]
		return new Promise((resolve, reject) => {
			AppConnector.request({
				module: 'Chat',
				action: 'ChatAjax',
				mode: 'send',
				roomType: roomType,
				recordId: recordId,
				message: text,
				mid: lastEntries !== undefined ? lastEntries['id'] : undefined
			}).done(({ result }) => {
				console.log(result)
				commit('pushSended', { result, roomType, recordId })
				resolve(result)
			})
		})
	},
	togglePinned({ dispatch, commit, getters }, { roomType, room }) {
		const mode = room.isPinned || roomType === 'crm' ? 'removeFromFavorites' : 'addToFavorites'
		commit('setPinned', { roomType, room })
		AppConnector.request({
			module: 'Chat',
			action: 'Room',
			mode: mode,
			roomType: roomType,
			recordId: room.recordid
		}).done(_ => {
			if (mode === 'removeFromFavorites' && roomType === 'crm' && getters.data.currentRoom.roomType === roomType) {
				dispatch('fetchRoom', { id: undefined, roomType: undefined })
			}
		})
	},
	fetchEarlierEntries({ commit }, { chatEntries, roomType, recordId }) {
		return new Promise((resolve, reject) => {
			AppConnector.request(
				{
					module: 'Chat',
					action: 'ChatAjax',
					mode: 'getMoreMessages',
					lastId: chatEntries[0].id,
					roomType: roomType,
					recordId: recordId
				},
				false
			).done(({ result }) => {
				commit('pushOlderEntries', { result, roomType, recordId })
				resolve(result)
			})
		})
	},
	/**
	 * Search messages.
	 * @param {jQuery} btn
	 */
	fetchSearchData({ commit, getters }, { value, roomData }) {
		return new Promise((resolve, reject) => {
			const showMoreClicked = getters.isSearchActive && roomData.showMoreButton
			AppConnector.request(
				{
					module: 'Chat',
					action: 'ChatAjax',
					mode: 'search',
					searchVal: value,
					mid: showMoreClicked ? roomData.chatEntries[0].id : null,
					roomType: roomData.roomType,
					recordId: roomData.recordid
				},
				false
			).done(({ result }) => {
				if (!showMoreClicked) {
					commit('setData', result)
					commit('setSearchActive')
				} else {
					commit('pushOlderEntries', { result, roomType: roomData.roomType, recordId: roomData.recordid })
				}
				resolve(result)
			})
		})
	},
	/**
	 * Get unread messages.
	 */
	fetchUnread() {
		return new Promise((resolve, reject) => {
			AppConnector.request({
				module: 'Chat',
				action: 'ChatAjax',
				mode: 'getUnread'
			}).done(({ result }) => {
				resolve(result)
			})
		})
	},
	/**
	 * Fetch history.
	 * @param {jQuery} btn
	 * @param {string} groupHistory
	 */
	fetchHistory({ commit, getters }, { groupHistory, showMoreClicked }) {
		return new Promise((resolve, reject) => {
			AppConnector.request({
				module: 'Chat',
				action: 'ChatAjax',
				mode: 'getHistory',
				mid: showMoreClicked ? getters.data.chatEntries[0].id : null,
				groupHistory: groupHistory
			}).done(({ result }) => {
				if (!showMoreClicked) {
					commit('setData', Object.assign(tempData, result))
				} else {
					commit('pushOlderEntries', result)
				}
				resolve(result)
			})
		})
	},

	updateAmountOfNewMessages({ commit, getters }, { roomList, amount }) {
		if (amount > getters.data.amountOfNewMessages) {
			if (getters.isSoundNotification) {
				for (let roomType in roomList) {
					let played = false
					for (let room in roomList[roomType]) {
						if (
							roomList[roomType][room].cnt_new_message > getters.data.roomList[roomType][room].cnt_new_message &&
							!getters.roomSoundNotificationsOff[roomType].includes(parseInt(room))
						) {
							app.playSound('CHAT')
							played = true
							break
						}
					}
					if (played) break
				}
			}
			if (getters.isDesktopNotification && !PNotify.modules.Desktop.checkPermission()) {
				let message = app.vtranslate('JS_CHAT_NEW_MESSAGE')
				if (getters.config.showNumberOfNewMessages) {
					message += ' ' + amount
				}
				app.showNotify(
					{
						text: message,
						title: app.vtranslate('JS_CHAT'),
						type: 'success'
					},
					true
				)
			}
		}
		if (amount !== getters.data.amountOfNewMessages && amount !== undefined) {
			commit('setAmountOfNewMessages', amount)
			commit('setAmountOfNewMessagesByRoom', roomList)
		}
	}
}
