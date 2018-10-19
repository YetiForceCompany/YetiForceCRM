/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

/**
 * Class Chat_Js.
 * @type {Window.Chat_Js}
 */
window.Chat_JS = class Chat_Js {
	/**
	 * Constructor
	 * @param {jQuery} container
	 */
	constructor(container) {
		this.container = container;
		this.messageContainer = container.find('.js-chat_content');
		this.sendByEnter = true;
		this.timerMessage = null;
		this.timerRoom = null;
		this.timerGlobal = null;
		this.lastMessageId = 0;
		this.roomId = 0;
	}

	/**
	 * Get instance of Chat_Js.
	 * @returns {Chat_Js|Window.Chat_Js}
	 */
	static getInstance(container, typeInstance = 'modal') {
		if (typeof Chat_Js.instance === 'undefined') {
			Chat_Js.instance = {};
		}
		if (typeof Chat_Js.instance[typeInstance] === 'undefined') {
			Chat_Js.instance[typeInstance] = new Chat_Js(container);
		} else {
			Chat_Js.instance[typeInstance].container = container;
			Chat_Js.instance[typeInstance].messageContainer = container.find('.js-chat_content');
		}
		return Chat_Js.instance[typeInstance];
	}

	/**
	 * Sending HTTP requests to the chat module.
	 * @param {object} data
	 * @param {bool} progress
	 * @returns {object}
	 */
	request(data = {}, progress = true) {
		const aDeferred = $.Deferred();
		let progressIndicator = null;
		if (progress) {
			progressIndicator = this.progressShow();
		}
		AppConnector.request($.extend({module: 'Chat'}, data)).done((data) => {
			aDeferred.resolve(data);
		}).always(() => {
			if (progress) {
				progressIndicator.progressIndicator({mode: 'hide'});
			}
		});
		return aDeferred.promise();
	}

	/**
	 * Send chat message.
	 * @param {jQuery} inputMessage
	 */
	sendMessage(inputMessage) {
		if (inputMessage.val() === '') {
			return;
		}
		this.request({
			view: 'Entries',
			mode: 'send',
			roomType: this.getCurrentRoomType(),
			recordId: this.getCurrentRecordId(),
			message: inputMessage.val(),
			mid: this.messageContainer.children().last().data('mid')
		}).done((data) => {
			this.messageContainer.append(data);
		});
		inputMessage.val('');
	}

	/**
	 * Show progress indicator
	 * @returns {jQuery}
	 */
	progressShow() {
		return $.progressIndicator({
			position: 'html',
			blockInfo: {enabled: true}
		});
	}

	/**
	 * Get current room type.
	 * @returns {int}
	 */
	getCurrentRoomType() {
		return this.messageContainer.data('currentRoomType');
	}

	/**
	 * Get current record ID.
	 * @returns {*}
	 */
	getCurrentRecordId() {
		return this.messageContainer.data('currentRecordId');
	}

	/**
	 * Select room.
	 * @param {string} roomType
	 * @param {int} recordId
	 */
	selectRoom(roomType, recordId) {
		this.container.find('.js-room-list .js-room').each((index, element) => {
			$(element).removeClass('active');
		});
		this.container.find(
			'.js-room-list .js-room-type[data-room-type=' + roomType + '] .js-room[data-record-id=' + recordId + ']'
		).addClass('active');
		this.messageContainer.data('currentRoomType', roomType);
		this.messageContainer.data('currentRecordId', recordId);
	}

	/**
	 * Get new message
	 * @param {bool} timer
	 */
	getMessage(timer = false) {
		this.request({
			view: 'Entries',
			mode: 'get',
			lastId: this.lastMessageId,
			roomId: this.roomId
		}).done((data) => {
			this.buildParticipants(data.find('.js-participants-data'), false);
			this.messageContainer.append(data);
			if (timer) {
				this.timerMessage = setTimeout(() => {
					this.getMessage(true);
				}, this.container.data('messageTimer'));
			}
		});
	}

	/**
	 * Get rooms details
	 * @param {bool} timer
	 */
	getRoomsDetail(timer = false) {
		this.request({
			action: 'Room',
			mode: 'getAll'
		}, false).done((data) => {
			this.reloadRoomsDetail(data);
			if (timer) {
				this.timerRoom = setTimeout(() => {
					this.getRoomsDetail(true);
				}, this.container.data('roomTimer'));
			}
		});
	}

	/**
	 * Refresh all information about the rooms
	 * @param {object} data
	 */
	reloadRoomsDetail(data) {

	}

	/**
	 * Build a list of participants
	 * @param {jQuery} element
	 * @param {bool} add
	 */
	buildParticipants(element, reload = false) {
		if (element.length) {
			if (reload) {
				this.container.find('.js-participants-list').html('');
			}

		}
	}

	/**
	 * Register send event
	 */
	registerSendEvent() {
		const self = this;
		const inputMessage = this.container.find('.js-chat-message');
		if (this.sendByEnter) {
			inputMessage.on('keydown', function (e) {
				if (e.keyCode === 13) {
					e.preventDefault();
					self.sendMessage($(this));
				}
			});
		}
		this.container.find('.js-btn-send').on('click', () => {
			this.sendMessage(inputMessage);
		});
	}

	/**
	 * Register switch room
	 */
	registerSwitchRoom() {
		this.container.find('.js-room-list .js-room').off('click').on('click', (e) => {
			let element = $(e.currentTarget);
			let recordId = element.data('recordId');
			let roomType = element.closest('.js-room-type').data('roomType');
			this.request({
				view: 'Entries',
				mode: 'get',
				roomType: roomType,
				recordId: recordId
			}).done((data) => {
				this.selectRoom(roomType, recordId);
				//this.buildParticipants(data.find('.js-participants-data'), true);
				//this.lastMessageId = data.find('.js-chat-item:last').data('mid');
				this.messageContainer.html(data);
			});
		});
	}


	registerListenEvent() {
		this.timerMessage = setTimeout(() => {
			//this.getMessage(true);
		}, this.messageContainer.data('messageTimer'));
		this.timerRoom = setTimeout(() => {
			this.getRoomsDetail(true);
		}, this.messageContainer.data('roomTimer'));
	}

	/**
	 * Register tracking events
	 */
	registerTrackingEvents() {

	}

	/**
	 * Register create room.
	 */
	registerCreateRoom() {
		let btnCreate = this.container.find('.js-create-chatroom');
		if (btnCreate.length) {
			btnCreate.off('click').on('click', (e) => {
				this.request({
					action: 'Room',
					mode: 'create',
					roomType: this.getCurrentRoomType(),
					recordId: this.getCurrentRecordId()
				}).done((data) => {
					this.container.find('.js-container-button').addClass('hide');
					this.container.find('.js-container-chat').removeClass('hide');
				});
			});
		}
	}

	/**
	 * Register base events
	 */
	registerBaseEvents() {
		this.registerSendEvent();
		this.registerListenEvent();
		this.registerCreateRoom();
	}

	/**
	 * Register modal events
	 */
	registerModalEvents() {
		this.registerBaseEvents();
		this.registerSwitchRoom();
	}
}
