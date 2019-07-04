/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
export default {
	dialog: false,
	maximizedDialog: true,
	leftPanel: true,
	rightPanel: true,
	historyTab: false,
	isSearchActive: false,
	tab: 'chat',
	data: {
		chatEntries: [],
		currentRoom: {},
		roomList: {},
		participants: [],
		isModalView: null,
		isSoundNotification: null,
		isDesktopNotification: null,
		isNotificationPermitted: PNotify.modules.Desktop.checkPermission() === 0,
		sendByEnter: null,
		showMoreButton: null,
		refreshMessageTime: null,
		refreshRoomTime: null,
		maxLengthMessage: null,
		refreshTimeGlobal: null,
		showNumberOfNewMessages: null
	}
}
