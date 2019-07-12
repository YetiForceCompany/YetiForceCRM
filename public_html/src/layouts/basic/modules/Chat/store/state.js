/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
export default {
	storage: {
		dialog: false,
		miniMode: true,
		leftPanel: false,
		rightPanel: false,
		historyTab: false,
		isSearchActive: false,
		isSoundNotification: null,
		isDesktopNotification: false,
		isNotificationPermitted: PNotify.modules.Desktop.checkPermission() === 0,
		sendByEnter: true,
		tab: 'chat'
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
