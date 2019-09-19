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
	fetchRoom({ commit }, { id, roomType }) {
		return new Promise((resolve, reject) => {
			AppConnector.request({
				module: 'Chat',
				action: 'ChatAjax',
				mode: 'getMessages',
				recordId: id,
				roomType: roomType,
				recordRoom: false
			}).done(({ result }) => {
				commit('mergeData', result)
				resolve(result)
			})
		})
	},
	archivePrivateRoom({ dispatch }, room) {
		return new Promise((resolve, reject) => {
			AppConnector.request({
				module: 'Chat',
				action: 'ChatAjax',
				mode: 'archivePrivateRoom',
				recordId: room.recordid
			}).done(({ result }) => {
				dispatch('fetchRoom', { id: undefined, roomType: undefined })
				resolve(result)
			})
		})
	},
	fetchRecordRoom({ commit }, id) {
		return new Promise((resolve, reject) => {
			AppConnector.request({
				module: 'Chat',
				action: 'ChatAjax',
				mode: 'getRecordRoom',
				id: id
			}).done(({ result }) => {
				commit('mergeData', result)
				resolve(result)
			})
		})
	},
	/**
	 * Fetch all chat users
	 */
	fetchChatUsers() {
		return new Promise((resolve, reject) => {
			AppConnector.request({
				module: 'Chat',
				action: 'ChatAjax',
				mode: 'getChatUsers'
			}).done(({ result }) => {
				resolve(result)
			})
		})
	},
	removeActiveRoom({ commit }, { recordId, roomType }) {
		commit('unsetActiveRoom', { recordId, roomType })
	},
	addActiveRoom({ commit }, { recordId, roomType }) {
		commit('setActiveRoom', { recordId, roomType })
	},
	addPrivateRoom({ dispatch, commit, getters }, { name }) {
		return new Promise((resolve, reject) => {
			AppConnector.request({
				module: 'Chat',
				action: 'ChatAjax',
				mode: 'addPrivateRoom',
				name: name
			}).done(result => {
				resolve(result)
			})
		})
	},
	/**
	 * Add participant to private room
	 */
	addParticipant({}, { recordId, userId }) {
		return new Promise((resolve, reject) => {
			AppConnector.request({
				module: 'Chat',
				action: 'ChatAjax',
				mode: 'addParticipant',
				recordId,
				userId
			}).done(result => {
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
				if (result.message) {
					commit('setData', result.data)
					Quasar.Plugins.Notify({ position: 'top', textColor: 'negative', message: app.vtranslate(result.message) })
				} else {
					commit('pushSended', { result, roomType, recordId })
				}
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
			if (
				mode === 'removeFromFavorites' &&
				(roomType === 'crm' || roomType === 'private') &&
				(getters.data.currentRoom.roomType === roomType && getters.data.currentRoom.recordId === room.recordid)
			) {
				dispatch('fetchRoom', { id: undefined, roomType: undefined })
			}
		})
	},

	removeUserFromRoom({ dispatch, commit, getters }, { roomType, recordId, userId }) {
		return new Promise((resolve, reject) => {
			AppConnector.request({
				module: 'Chat',
				action: 'Room',
				mode: 'removeUserFromRoom',
				recordId,
				roomType,
				userId
			}).done(({ result }) => {
				resolve(result)
			})
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
	fetchSearchData({ commit }, { value, roomData, showMore }) {
		return new Promise((resolve, reject) => {
			AppConnector.request(
				{
					module: 'Chat',
					action: 'ChatAjax',
					mode: 'search',
					searchVal: value,
					mid: showMore ? roomData.searchData.chatEntries[0].id : null,
					roomType: roomData.roomType,
					recordId: roomData.recordid
				},
				false
			).done(({ result }) => {
				if (!showMore) {
					commit('setSearchData', {
						searchData: result,
						roomType: roomData.roomType,
						recordId: roomData.recordid
					})
				} else {
					commit('pushOlderEntriesToSearch', {
						searchData: result,
						roomType: roomData.roomType,
						recordId: roomData.recordid
					})
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
				mid: showMoreClicked ? getters.data.history.chatEntries[0].id : null,
				groupHistory: groupHistory
			}).done(({ result }) => {
				if (!showMoreClicked) {
					commit('setHistoryData', result)
				} else {
					commit('pushOlderEntriesToHistory', result)
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
