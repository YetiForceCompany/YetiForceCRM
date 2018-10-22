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
		this.init(container);
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
			Chat_Js.instance[typeInstance].init(container);
		}
		return Chat_Js.instance[typeInstance];
	}

	/**
	 * Data initialization from the container.
	 * @param {jQuery} container
	 */
	init(container) {
		this.container = container;
		this.messageContainer = this.container.find('.js-chat_content');
		this.sendByEnter = true;
		this.timerMessage = null;
		this.timerRoom = null;
		this.timerGlobal = null;
		this.lastMessageId = this.container.find('.js-chat-item:last').data('mid');
		this.maxLengthMessage = this.messageContainer.data('maxLengthMessage');
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
		let len = inputMessage.val().length;
		if (0 === len) {
			return;
		}
		if (len < this.maxLengthMessage) {
			clearTimeout(this.timerMessage);
			this.request({
				view: 'Entries',
				mode: 'send',
				roomType: this.getCurrentRoomType(),
				recordId: this.getCurrentRecordId(),
				message: inputMessage.val(),
				mid: this.messageContainer.find('.js-chat-item:last').data('mid')
			}).done((data) => {
				this.messageContainer.append(data);
				this.getMessage(true);
				this.updateParticipants();
			});
			inputMessage.val('');
		} else {
			Vtiger_Helper_Js.showPnotify({
				text: app.vtranslate('JS_MESSAGE_TOO_LONG'),
				animation: 'show'
			});
		}
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
	 * Create a user element.
	 * @param {object}
	 * @returns {jQuery}
	 */
	createUserItem(data = {}) {
		let itemUser = this.container.find('.js-participants-list .js-temp-item-user').clone(false, false);
		itemUser.removeClass('js-temp-item-user');
		itemUser.removeClass('hide');
		itemUser.find('.js-user-name').html(data.userName);
		itemUser.find('.js-role').html(data.role);
		itemUser.find('.js-message').html(data.message);
		itemUser.data('userId', data.userId);
		return itemUser;
	}

	/**
	 * Update the last message from the list of participants.
	 */
	updateParticipants() {
		this.container.find('.js-participants-list .js-users .js-item-user').each((index, element) => {
			let lastMessage = this.messageContainer.find('.js-chat-item[data-user-id=' + $(element).data('userId') + ']:last');
			if (lastMessage.length) {
				$(element).find('.js-message').html(lastMessage.find('.messages').html());
			}
		});
	}

	/**
	 * Is chat room active.
	 * @returns {boolean}
	 */
	isRoomActive() {
		return this.container.find('.js-container-chat').length === 0 || !this.container.find('.js-container-chat').hasClass('hide');
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
	 * Get message timer.
	 * @returns {int}
	 */
	getMessageTimer() {
		return this.messageContainer.data('messageTimer');
	}

	/**
	 * Get room timer.
	 * @returns {int}
	 */
	getRoomTimer() {
		return this.messageContainer.data('roomTimer');
	}

	/**
	 * Activate chat room.
	 */
	activateRoom() {
		this.container.find('.js-container-button').addClass('hide');
		this.container.find('.js-container-chat').removeClass('hide');
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
		if (timer) {
			clearTimeout(this.timerMessage);
		}
		this.timerMessage = setTimeout(() => {
			let participants = [];
			this.container.find('.js-participants-list .js-users .js-item-user').each((index, element) => {
				participants.push($(element).data('userId'));
			});
			this.lastMessageId = this.messageContainer.find('.js-chat-item:last').data('mid');
			let param = {
				view: 'Entries',
				mode: 'get',
				lastId: this.lastMessageId,
				roomType: this.getCurrentRoomType(),
				recordId: this.getCurrentRecordId()
			};
			if (participants.length) {
				param['participants'] = participants;
			}
			this.request(param, false).done((html) => {
				if (html) {
					if (!this.isRoomActive()) {
						this.activateRoom();
					}
					let obj = $('<div></div>').html($.parseHTML(html));
					this.buildParticipants(obj.find('.js-participants-data'), false);
					this.messageContainer.append(html);
					this.updateParticipants();
					//this.lastMessageId = this.messageContainer.find('.js-chat-item:last').data('mid');
					//console.log(this.lastMessageId);
					//console.log(this.messageContainer.find('.js-chat-item:last'));
				}
				if (timer) {
					this.getMessage(true);
				}
			});
		}, this.getMessageTimer());
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
				}, this.getRoomTimer());
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
			} else {
				let users = JSON.parse(element.val());
				let len = users.length;
				for (let i = 0; i < len; ++i) {
					this.container.find('.js-participants-list .js-users').append(
						this.createUserItem({
							userName: users[i]['user_name'],
							role: users[i]['role_name'],
							message: users[i]['message'],
							userId: users[i]['user_id'],
						})
					);
				}
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
				}).done(() => {
					this.activateRoom();
				});
			});
		}
	}

	/**
	 * Register listen event.
	 */
	registerListenEvent() {
		this.getMessage(true);
		/*this.timerRoom = setTimeout(() => {
			this.getRoomsDetail(true);
		}, this.getRoomTimer());*/
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
