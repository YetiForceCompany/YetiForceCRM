/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
export default {
	layout: {
		drawer: {
			fs: '.88rem'
		}
	},
	session: {
		dialog: false,
		miniMode: true,
		leftPanel: false,
		rightPanel: false,
		historyTab: 'crm',
		tab: 'chat',
		coordinates: {
			width: 450,
			height: window.innerHeight - 160,
			top: 60,
			left: window.innerWidth - 450
		},
		buttonCoordinates: {
			top: window.innerHeight - 82,
			left: window.innerWidth - 62
		}
	},
	local: {
		isSoundNotification: null,
		isDesktopNotification: false,
		sendByEnter: true,
		roomSoundNotificationsOff: {
			crm: [],
			global: [],
			group: [],
			private: []
		}
	},
	data: {
		amountOfNewMessages: 0,
		roomList: {
			crm: {},
			group: {},
			global: {},
			private: {}
		},
		currentRoom: {},
		history: {
			chatEntries: [],
			showMoreButton: null
		}
	},
	config: {
		isChatAllowed: null,
		isDefaultSoundNotification: null,
		refreshMessageTime: null,
		refreshRoomTime: null,
		maxLengthMessage: null,
		refreshTimeGlobal: null,
		showNumberOfNewMessages: null,
		dynamicAddingRooms: null
	}
}
