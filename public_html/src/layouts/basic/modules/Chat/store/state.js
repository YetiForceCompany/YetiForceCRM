/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
export default {
	session: {
		dialog: false,
		miniMode: true,
		leftPanel: false,
		rightPanel: false,
		historyTab: 'crm',
		isSearchActive: false,
		tab: 'chat',
		coordinates: {
			width: 450,
			height: Quasar.plugins.Screen.height - 160,
			top: 60,
			left: Quasar.plugins.Screen.width - 450
		}
	},
	local: {
		isSoundNotification: null,
		isDesktopNotification: false,
		sendByEnter: true
	},
	data: {
		amountOfNewMessages: 0,
		chatEntries: [],
		currentRoom: {},
		roomList: {},
		participants: [],
		showMoreButton: null
	},
	config: {
		isChatAllowed: null,
		isDefaultSoundNotification: null,
		refreshMessageTime: null,
		refreshRoomTime: null,
		maxLengthMessage: null,
		refreshTimeGlobal: null,
		showNumberOfNewMessages: null
	}
}
