/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
import unionby from 'lodash.unionby'
import { mergeDeepReactive } from '../utils/utils.js'

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
	setTab(state, tab) {
		state.session.tab = tab
	},
	setCoordinates(state, val) {
		state.session.coordinates = val
	},
	setButtonCoordinates(state, val) {
		state.session.buttonCoordinates = val
	},
	setSendByEnter(state, val) {
		state.local.sendByEnter = val
	},
	setSoundNotification(state, val) {
		state.local.isSoundNotification = val
	},
	removeRoomSoundNotificationsOff(state, { roomType, id }) {
		state.local.roomSoundNotificationsOff[roomType] = state.local.roomSoundNotificationsOff[roomType].filter(
			item => item !== id
		)
	},
	addRoomSoundNotificationsOff(state, { roomType, id }) {
		state.local.roomSoundNotificationsOff[roomType].push(id)
	},
	setDesktopNotification(state, val) {
		state.local.isDesktopNotification = val
	},
	setData(state, data) {
		state.data = data
	},
	setRoomData(state, data) {
		state.data.amountOfNewMessages = data.amountOfNewMessages
		state.data.currentRoom = data.currentRoom
		state.data.roomList = data.roomList
	},
	setPrivateRooms(state, data) {
		if (state.data.currentRoom.roomType === 'private' && data[state.data.currentRoom.recordId]) {
			data[state.data.currentRoom.recordId].chatEntries =
				state.data.roomList.private[state.data.currentRoom.recordId].chatEntries
		}
		state.data.roomList.private = data
	},
	mergeData(state, data) {
		state.data = mergeDeepReactive(state.data, data)
	},

	setHistoryData(state, data) {
		state.data.history = mergeDeepReactive(state.data.history, data)
	},
	pushSended(state, { result, roomType, recordId }) {
		state.data.roomList[roomType][recordId].chatEntries.push(result.chatEntries.slice(-1)[0])
		state.data.roomList[roomType][recordId].showMoreButton = result.showMoreButton
		state.data.roomList[roomType][recordId].participants = result.participants
	},
	updateChatData(state, { roomsToUpdate, newData }) {
		state.data.amountOfNewMessages = newData.amountOfNewMessages
		roomsToUpdate.forEach(room => {
			state.data.roomList[room.roomType][room.recordid].showMoreButton =
				newData.roomList[room.roomType][room.recordid].showMoreButton
			state.data.roomList[room.roomType][room.recordid].participants =
				newData.roomList[room.roomType][room.recordid].participants
			state.data.roomList[room.roomType][room.recordid].chatEntries = unionby(
				state.data.roomList[room.roomType][room.recordid].chatEntries,
				newData.roomList[room.roomType][room.recordid].chatEntries,
				'id'
			)
		})
	},
	updateRooms(state, data) {
		state.data.roomList = mergeDeepReactive(state.data.roomList, data)
	},
	updateParticipants(state, { roomType, recordId, data }) {
		if (state.data.currentRoom.roomType === roomType) state.data.roomList[roomType][recordId].participants = data
	},
	pushOlderEntries(state, { result, roomType, recordId }) {
		state.data.roomList[roomType][recordId].chatEntries.unshift(...result.chatEntries)
		state.data.roomList[roomType][recordId].showMoreButton = result.showMoreButton
	},
	pushOlderEntriesToHistory(state, result) {
		state.data.history.chatEntries.unshift(...result.chatEntries)
		state.data.history.showMoreButton = result.showMoreButton
	},
	setSearchData(state, { roomType, recordId, searchData }) {
		state.data.roomList[roomType][recordId].searchData = searchData
	},
	pushOlderEntriesToSearch(state, { roomType, recordId, searchData }) {
		state.data.roomList[roomType][recordId].searchData.chatEntries.unshift(...searchData.chatEntries)
		state.data.roomList[roomType][recordId].searchData = searchData.showMoreButton
	},
	setAmountOfNewMessages(state, val) {
		state.data.amountOfNewMessages = val
	},
	setAmountOfNewMessagesByRoom(state, val) {
		state.data.roomList = mergeDeepReactive(state.data.roomList, val)
	},
	unsetActiveRoom(state, { recordId, roomType }) {
		state.data.roomList[roomType][recordId].active = false
	},
	setActiveRoom(state, { recordId, roomType }) {
		state.data.roomList[roomType][recordId].active = true
	},
	unsetParticipant(state, { roomId, participantId }) {
		let participants = state.data.roomList.private[roomId].participants
		for (const [i, participant] of participants.entries()) {
			if (participant.user_id === participantId) {
				participants.splice(i, 1)
				break
			}
		}
	},
	setPinned(state, { roomType, room }) {
		const roomList = state.data.roomList
		if (roomType === 'crm') {
			for (let roomId in roomList.crm) {
				if (parseInt(roomId) === room.recordid) {
					roomList.crm[roomId].isPinned = false
					break
				}
			}
		} else {
			for (let roomId in roomList[roomType]) {
				if (parseInt(roomId) === room.recordid) {
					roomList[roomType][roomId].isPinned = !roomList[roomType][roomId].isPinned
					break
				}
			}
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
			state.local = mergeDeepReactive(state.local, JSON.parse(chatLocalStorage))
		}
		if (
			chatSessionStorage &&
			JSON.stringify(Object.keys(state.session)) === JSON.stringify(Object.keys(JSON.parse(chatSessionStorage)))
		) {
			state.session = mergeDeepReactive(state.session, JSON.parse(chatSessionStorage))
		}
	}
}
