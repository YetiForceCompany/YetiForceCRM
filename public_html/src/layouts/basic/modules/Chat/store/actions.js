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
	toggleSoundNotification({ commit, getters }, { roomType, id }) {
		if (getters.roomSoundNotificationsOff[roomType].includes(id)) {
			commit('addRoomSoundNotificationsOff', { roomType, id })
		} else {
			commit('removeRoomSoundNotificationsOff', { roomType, id })
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
				let tempData = Object.assign({}, getters.data)
				commit('setData', Object.assign(tempData, result))
				resolve(result)
			})
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
				mid:
					getters.data.chatEntries.slice(-1)[0] !== undefined ? getters.data.chatEntries.slice(-1)[0]['id'] : undefined
			}).done(({ result }) => {
				commit('pushSended', result)
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
	fetchEarlierEntries({ commit, getters }) {
		return new Promise((resolve, reject) => {
			AppConnector.request(
				{
					module: 'Chat',
					action: 'ChatAjax',
					mode: 'getMoreMessages',
					lastId: getters.data.chatEntries[0].id,
					roomType: getters.data.currentRoom.roomType,
					recordId: getters.data.currentRoom.recordId
				},
				false
			).done(({ result }) => {
				commit('pushOlderEntries', result)
				resolve(result)
			})
		})
	},
	/**
	 * Search messages.
	 * @param {jQuery} btn
	 */
	fetchSearchData({ commit, getters }, value) {
		return new Promise((resolve, reject) => {
			const showMoreClicked = getters.isSearchActive && getters.data.showMoreButton
			AppConnector.request(
				{
					module: 'Chat',
					action: 'ChatAjax',
					mode: 'search',
					searchVal: value,
					mid: showMoreClicked ? getters.data.chatEntries[0].id : null,
					roomType: getters.data.currentRoom.roomType,
					recordId: getters.data.currentRoom.recordId
				},
				false
			).done(({ result }) => {
				if (!showMoreClicked) {
					let tempData = Object.assign({}, getters.data)
					commit('setData', Object.assign(tempData, result))
					commit('setSearchActive')
				} else {
					commit('pushOlderEntries', result)
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
					let tempData = Object.assign({}, getters.data)
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
