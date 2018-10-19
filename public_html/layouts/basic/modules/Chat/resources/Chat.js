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
	static getInstance(container) {
		if (typeof Chat_Js.instance === 'undefined') {
			Chat_Js.instance = new Chat_Js(container);
		} else {
			Chat_Js.instance.container = container;
		}
		return Chat_Js.instance;
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
		const currentRoom = this.messageContainer.data('currentRoom');
		this.request({
			view: 'Entries',
			mode: 'send',
			roomId: currentRoom.roomId,
			roomType: currentRoom.roomType,
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
			this.roomId = element.data('roomId');
			this.request({
				view: 'Entries',
				mode: 'get',
				roomId: this.roomId,
				roomType: element.closest('.js-room-type').data('roomType')
			}).done((data) => {
				element.addClass('active');
				//this.buildParticipants(data.find('.js-participants-data'), true);
				//this.lastMessageId = data.find('.js-chat-item:last').data('cid');
				this.messageContainer.html(data);
			});
		});
	}


	registerListenEvent() {
		this.timerMessage = setTimeout(() => {
			//this.getMessage(true);
		}, this.container.data('messageTimer'));
		this.timerRoom = setTimeout(() => {
			this.getRoomsDetail(true);
		}, this.container.data('roomTimer'));
	}

	/**
	 * Register tracking events
	 */
	registerTrackingEvents() {

	}

	/**
	 * Register base events
	 */
	registerBaseEvents() {
		this.registerSendEvent();
		this.registerListenEvent();
	}

	/**
	 * Register modal events
	 */
	registerModalEvents() {
		this.registerBaseEvents();
		this.registerSwitchRoom();
	}
}
