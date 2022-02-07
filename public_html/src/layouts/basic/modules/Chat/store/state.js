/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
export default {
	layout: {
		drawer: {
			fs: '.88rem',
			breakpoint: 1023,
		},
	},
	session: {
		dialog: false,
		miniMode: true,
		leftPanel: false,
		leftPanelMobile: false,
		rightPanel: false,
		rightPanelMobile: false,
		historyTab: 'crm',
		tab: 'chat',
		coordinates: {
			width: 450,
			height: window.innerHeight - 160,
			top: 60,
			left: window.innerWidth - 450,
		},
		buttonCoordinates: {
			top: window.innerHeight - 82,
			left: window.innerWidth - 62,
		},
	},
	local: {
		isSoundNotification: null,
		isDesktopNotification: false,
		sendByEnter: true,
		roomSoundNotificationsOff: {
			crm: [],
			global: [],
			group: [],
			private: [],
			user: [],
		},
		roomsExpanded: [],
	},
	data: {
		amountOfNewMessages: 0,
		roomList: {
			private: {},
			group: {},
			global: {},
			crm: {},
			user: {},
		},
		currentRoom: {},
		history: {
			chatEntries: [],
			showMoreButton: null,
		},
	},
	config: {
		isChatAllowed: null,
		isDefaultSoundNotification: null,
		refreshMessageTime: null,
		refreshRoomTime: null,
		maxLengthMessage: null,
		refreshTimeGlobal: null,
		showNumberOfNewMessages: null,
		showRoleName: null,
		dynamicAddingRooms: null,
		draggableButton: null,
		detailPreview: { id: null, module: null },
		activeRoomTypes: [],
		userRoomPin: null,
	},
}
