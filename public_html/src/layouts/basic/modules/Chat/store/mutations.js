/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
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
	setLeftPanelMobile(state, isOpen) {
		state.session.leftPanelMobile = isOpen
	},
	setRightPanel(state, isOpen) {
		state.session.rightPanel = isOpen
	},
	setRightPanelMobile(state, isOpen) {
		state.session.rightPanelMobile = isOpen
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
		state.local.roomSoundNotificationsOff[roomType] = state.local.roomSoundNotificationsOff[roomType].filter((item) => item !== id)
	},
	addRoomSoundNotificationsOff(state, { roomType, id }) {
		state.local.roomSoundNotificationsOff[roomType].push(id)
	},
	removeRoomExpanded(state, roomType) {
		state.local.roomsExpanded = state.local.roomsExpanded.filter((room) => room !== roomType)
	},
	addRoomExpanded(state, roomType) {
		state.local.roomsExpanded.push(roomType)
	},
	setDesktopNotification(state, val) {
		state.local.isDesktopNotification = val
	},
	setData(state, data) {
		state.data = data
	},
	setPinnedRooms(state, { rooms, roomType }) {
		Vue.set(state.data.roomList, roomType, rooms)
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
	updateActiveRooms(state, { roomsToUpdate, newData }) {
		state.data.amountOfNewMessages = newData.amountOfNewMessages.amount
		roomsToUpdate.forEach((room) => {
			state.data.roomList[room.roomType][room.recordid].showMoreButton = newData.roomList[room.roomType][room.recordid].showMoreButton
			state.data.roomList[room.roomType][room.recordid].participants = newData.roomList[room.roomType][room.recordid].participants
			state.data.roomList[room.roomType][room.recordid].chatEntries = unionby(
				state.data.roomList[room.roomType][room.recordid].chatEntries,
				newData.roomList[room.roomType][room.recordid].chatEntries,
				'id'
			)
		})
	},
	setNewRooms(state, { newRooms, newData }) {
		newRooms.forEach((room) => {
			Vue.set(state.data.roomList[room.roomType], room.recordId, newData[room.roomType][room.recordId])
		})
	},
	unsetUnpinnedRooms(state, roomsToUnpin) {
		Object.keys(roomsToUnpin).forEach((roomType) => {
			if (roomsToUnpin[roomType].length) {
				roomsToUnpin[roomType].forEach((recordId) => {
					Vue.delete(state.data.roomList[roomType], recordId)
				})
			}
		})
	},
	updateRooms(state, data) {
		state.data.roomList = mergeDeepReactive(state.data.roomList, data)
	},
	updateParticipants(state, { roomType, recordId, data }) {
		if (state.data.currentRoom.roomType === roomType) Vue.set(state.data.roomList[roomType][recordId], 'participants', data)
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
		for (let i = 0; i < participants.length; i++) {
			if (participants[i].user_id === participantId) {
				participants.splice(i, 1)
				break
			}
		}
	},
	unsetRoom(state, { roomType, recordId }) {
		Vue.delete(state.data.roomList[roomType], recordId)
	},
	setRoom(state, { roomType, recordId, room }) {
		Vue.set(state.data.roomList[roomType], recordId, room)
	},
	unsetCurrentRoom(state) {
		Vue.set(state.data, 'currentRoom', {})
	},
	setCurrentRoom(state, data) {
		Vue.set(state.data, 'currentRoom', data)
	},
	setConfig(state, config) {
		state.config = mergeDeepReactive(state.config, config)
	},
	setDetailPreview(state, { id, module }) {
		state.config.detailPreview = { id, module }
	},
	initStorage(state) {
		const chatLocalStorage = Quasar.plugins.LocalStorage.getItem('yf-chat')
		const chatSessionStorage = Quasar.plugins.SessionStorage.getItem('yf-chat')
		if (chatLocalStorage && JSON.stringify(Object.keys(state.local)) === JSON.stringify(Object.keys(JSON.parse(chatLocalStorage)))) {
			state.local = mergeDeepReactive(state.local, JSON.parse(chatLocalStorage))
		}
		if (chatSessionStorage && JSON.stringify(Object.keys(state.session)) === JSON.stringify(Object.keys(JSON.parse(chatSessionStorage)))) {
			state.session = mergeDeepReactive(state.session, JSON.parse(chatSessionStorage))
		}
	},
}
