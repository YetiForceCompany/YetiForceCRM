/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import difference from 'lodash.difference'
let timer = false

export default {
	maximize({ commit }, isMini) {
		commit('miniMode', isMini)
	},
	toggleLeftPanel({ commit, getters }, newValue) {
		if (getters.mobileMode) {
			commit('setLeftPanelMobile', newValue !== undefined ? newValue : !getters['leftPanelMobile'])
		} else {
			commit('setLeftPanel', newValue !== undefined ? newValue : !getters['leftPanel'])
		}
	},
	toggleRightPanel({ commit, getters }, newValue) {
		if (getters.mobileMode) {
			commit('setRightPanelMobile', newValue !== undefined ? newValue : !getters['rightPanelMobile'])
		} else {
			commit('setRightPanel', newValue !== undefined ? newValue : !getters['rightPanel'])
		}
	},
	toggleRoomSoundNotification({ commit, getters }, { roomType, id }) {
		if (getters.roomSoundNotificationsOff[roomType].includes(id)) {
			commit('removeRoomSoundNotificationsOff', { roomType, id })
		} else {
			commit('addRoomSoundNotificationsOff', { roomType, id })
		}
	},
	toggleRoomExpanded({ commit, getters }, roomType) {
		if (getters.roomsExpanded.includes(roomType)) {
			commit('removeRoomExpanded', roomType)
		} else {
			commit('addRoomExpanded', roomType)
		}
	},
	fetchChatConfig({ commit }) {
		return new Promise((resolve, reject) => {
			AppConnector.request({
				module: 'Chat',
				action: 'ChatAjax',
				mode: 'getChatConfig',
			}).done(({ result }) => {
				commit('setConfig', result.config)
				commit('setAmountOfNewMessagesByRoom', result.roomList)
				commit('setCurrentRoom', result.currentRoom)
				resolve(result)
			})
		})
	},
	fetchRoom({ commit, dispatch }, { id, roomType } = {}) {
		return new Promise((resolve, reject) => {
			AppConnector.request({
				module: 'Chat',
				action: 'ChatAjax',
				mode: 'getMessages',
				recordId: id,
				roomType,
				recordRoom: false,
			}).done(({ result }) => {
				if (result) {
					if (result.amountOfNewMessages) {
						dispatch('updateAmountOfNewMessages', result.amountOfNewMessages)
						commit('mergeData', { currentRoom: result.currentRoom, roomList: result.roomList })
					} else {
						commit('mergeData', result)
					}
				}
				resolve(result)
			})
		})
	},
	fetchRoomList({ commit, dispatch }, { id, roomType } = {}) {
		return new Promise((resolve, reject) => {
			AppConnector.request({
				module: 'Chat',
				action: 'ChatAjax',
				mode: 'getRooms',
			}).done(({ result }) => {
				if (result) {
					commit('mergeData', result)
				}
				resolve(result)
			})
		})
	},
	fetchRoomsUnpinned({ commit, dispatch }, { roomType }) {
		return new Promise((resolve, reject) => {
			AppConnector.request({
				module: 'Chat',
				action: 'ChatAjax',
				mode: 'getRoomsUnpinned',
				roomType,
			}).done(({ result }) => {
				resolve(result)
			})
		})
	},
	archivePrivateRoom({ commit }, { recordId }) {
		return new Promise((resolve, reject) => {
			AppConnector.request({
				module: 'Chat',
				action: 'ChatAjax',
				mode: 'archivePrivateRoom',
				recordId: recordId,
			}).done(({ result }) => {
				if (result) {
					commit('unsetRoom', { roomType: 'private', recordId })
				}
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
				id: id,
			}).done(({ result }) => {
				commit('mergeData', result)
				resolve(result)
			})
		})
	},
	/**
	 * Fetch all chat users
	 */
	fetchPrivateRoomUnpinnedUsers({}, roomId) {
		return new Promise((resolve, reject) => {
			AppConnector.request({
				module: 'Chat',
				action: 'ChatAjax',
				mode: 'getRoomPrivateUnpinnedUsers',
				roomId,
			}).done(({ result }) => {
				resolve(result)
			})
		})
	},
	removeActiveRoom({ commit }, { recordId, roomType }) {
		commit('unsetActiveRoom', { recordId, roomType })
	},
	addActiveRoom({ commit, getters }, { recordId, roomType }) {
		if (roomType && getters.data.roomList[roomType][recordId]) {
			commit('setActiveRoom', { recordId, roomType })
		}
	},
	addPrivateRoom({ dispatch, commit, getters }, { name }) {
		return new Promise((resolve, reject) => {
			AppConnector.request({
				module: 'Chat',
				action: 'ChatAjax',
				mode: 'addPrivateRoom',
				name: name,
			}).done((result) => {
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
				userId,
			}).done((result) => {
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
				roomType,
				recordId,
				message: text,
				mid: lastEntries !== undefined ? lastEntries['id'] : undefined,
			}).done(({ result }) => {
				if (result.message) {
					commit('setData', result.data)
					Quasar.plugins.Notify.create({
						position: 'top',
						textColor: 'negative',
						message: app.vtranslate(result.message),
					})
				} else {
					commit('pushSended', { result, roomType, recordId })
				}
				resolve(result)
			})
		})
	},
	unpinRoom({ dispatch }, { roomType, recordId }) {
		AppConnector.request({
			module: 'Chat',
			action: 'Room',
			mode: 'removeFromFavorites',
			roomType,
			recordId,
		}).done((data) => {
			if (data) {
				dispatch('unsetRoom', { roomType, recordId })
			}
		})
	},
	unsetRoom({ commit, getters }, { roomType, recordId }) {
		commit('unsetRoom', { roomType, recordId })
		let currentRoom = getters.data.currentRoom
		if (currentRoom.roomType === roomType && currentRoom.recordId === recordId) {
			commit('unsetCurrentRoom')
		}
	},
	pinRoom({ dispatch, commit }, { roomType, recordId }) {
		AppConnector.request({
			module: 'Chat',
			action: 'Room',
			mode: 'addToFavorites',
			roomType,
			recordId,
		}).done(({ success, result }) => {
			if (success) {
				dispatch('fetchRoomList')
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
				userId,
			}).done(({ result }) => {
				commit('unsetParticipant', { roomId: recordId, participantId: userId })
				Quasar.plugins.Notify.create({
					position: 'top',
					color: 'success',
					message: app.vtranslate('JS_CHAT_PARTICIPANT_REMOVED'),
					icon: 'mdi-check',
				})
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
					roomType,
					recordId,
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
					recordId: roomData.recordid,
				},
				false
			).done(({ result }) => {
				if (!showMore) {
					commit('setSearchData', {
						searchData: result,
						roomType: roomData.roomType,
						recordId: roomData.recordid,
					})
				} else {
					commit('pushOlderEntriesToSearch', {
						searchData: result,
						roomType: roomData.roomType,
						recordId: roomData.recordid,
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
				mode: 'getUnread',
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
				groupHistory,
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
	notifyAboutNewMessages({ dispatch, getters }, { roomList, amount, lastMessage, firstFetch }) {
		if (amount > getters.data.amountOfNewMessages) {
			const storageKey = lastMessage.roomid + '-' + lastMessage.id
			const isNotified = app.cacheGet('chat-desktop-notify') === storageKey
			if (!isNotified) {
				dispatch('playNotificationSound', { roomList, firstFetch })
				dispatch('showDesktopNotification', { amount, lastMessage })
				app.cacheSet('chat-desktop-notify', storageKey)
			}
		}
		dispatch('updateAmountOfNewMessages', { roomList, amount })
	},
	playNotificationSound({ getters }, { roomList, firstFetch }) {
		if (getters.isSoundNotification) {
			let areNewMessagesForRoom = (roomType, room) => roomList[roomType][room].cnt_new_message > getters.data.roomList[roomType][room].cnt_new_message
			if (firstFetch) {
				areNewMessagesForRoom = (roomType, room) => roomList[roomType][room].cnt_new_message >= getters.data.roomList[roomType][room].cnt_new_message
			}
			for (let roomType in roomList) {
				let played = false
				for (let room in roomList[roomType]) {
					let isSoundOn = !getters.roomSoundNotificationsOff[roomType].includes(parseInt(room))
					if (areNewMessagesForRoom(roomType, room) && isSoundOn) {
						app.playSound('CHAT')
						played = true
						break
					}
				}
				if (played) break
			}
		}
	},
	showDesktopNotification({ getters }, { amount, lastMessage }) {
		const notificationActive = getters.isDesktopNotification && App.Notify.isDesktopPermitted()
		if (!notificationActive) {
			return
		}
		let text = lastMessage.messages
		let roomCrumb = 'user' === lastMessage.roomData.roomType ? '' : ` / ${app.vtranslate('JS_CHAT_ROOM_' + lastMessage.roomData.roomType.toUpperCase())}`
		let userName = lastMessage.userData.user_name
		let title = `${app.vtranslate('JS_CHAT')}${roomCrumb} / ${userName}`
		let icon = lastMessage.userData.image ? lastMessage.userData.image : app.getMainParams('layoutPath') + '/../resources/Logo/logo'
		if (getters.config.showNumberOfNewMessages) {
			text += `\n✉️ ${app.vtranslate('JS_CHAT_SUM_UNREAD')}: ${amount}`
		}
		App.Notify.desktop({ icon, text, title })
	},
	updateAmountOfNewMessages({ commit, getters }, { roomList, amount }) {
		if (amount !== getters.data.amountOfNewMessages && amount !== undefined) {
			commit('setAmountOfNewMessages', amount)
			commit('setAmountOfNewMessagesByRoom', roomList)
		}
	},
	setNewRooms({ commit, getters }, roomList) {
		let newRooms = []
		for (let roomType in roomList) {
			for (let room in roomList[roomType]) {
				if (!getters.data.roomList[roomType][room]) {
					newRooms.push({
						roomType,
						recordId: roomList[roomType][room].recordid,
					})
				}
			}
		}
		if (newRooms.length) {
			commit('setNewRooms', {
				newRooms,
				newData: roomList,
			})
		}
	},
	unsetUnpinnedRooms({ commit, getters }, roomList) {
		let roomsToUnpin = {}
		let areDifferences = false
		for (let roomType in roomList) {
			roomsToUnpin[roomType] = difference(Object.keys(getters.data.roomList[roomType]), Object.keys(roomList[roomType]))
			if (roomsToUnpin[roomType].length) {
				areDifferences = true
			}
		}
		if (areDifferences) {
			commit('unsetUnpinnedRooms', roomsToUnpin)
		}
	},
	/**
	 * Init timer
	 */
	startUpdatesListener({ dispatch }) {
		dispatch('fetchNewMessages', { firstFetch: true })
	},
	/**
	 * Init timer
	 */
	initTimer({ dispatch, getters }) {
		let timeoutCallback = () => dispatch('fetchNewMessages')
		timer = setTimeout(timeoutCallback, getters.getInterval)
	},
	/**
	 * Fetch new messages timeout function
	 */
	fetchNewMessages({ getters, commit, dispatch }, { firstFetch } = { firstFetch: false }) {
		let activeRooms = getters.allRooms.length ? getters.allRooms.filter((el) => el.active) : []
		AppConnector.request({
			module: 'Chat',
			action: 'ChatAjax',
			mode: 'getRoomsMessages',
			rooms: activeRooms,
		}).done(({ result }) => {
			dispatch('unsetUnpinnedRooms', result.roomList)
			dispatch('setNewRooms', result.roomList)
			if (result.areNewEntries) {
				commit('updateActiveRooms', {
					roomsToUpdate: [...activeRooms],
					newData: result,
				})
			}
			dispatch('notifyAboutNewMessages', {
				...result.amountOfNewMessages,
				firstFetch,
			})
			if (timer || firstFetch) {
				dispatch('initTimer')
			}
		})
	},
}
