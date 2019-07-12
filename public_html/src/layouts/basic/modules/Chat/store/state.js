/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
export default {
	dialog: false,
	miniMode: true,
	leftPanel: false,
	rightPanel: false,
	historyTab: false,
	isSearchActive: false,
	tab: 'chat',
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
		isSoundNotification: null,
		isDesktopNotification: null,
		isNotificationPermitted: PNotify.modules.Desktop.checkPermission() === 0,
		sendByEnter: null,
		refreshMessageTime: null,
		refreshRoomTime: null,
		maxLengthMessage: null,
		refreshTimeGlobal: null,
		showNumberOfNewMessages: null
	}
}
