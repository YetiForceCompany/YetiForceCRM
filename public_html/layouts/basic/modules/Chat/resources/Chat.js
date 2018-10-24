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
		console.log('init!!!');
		this.container = container;
		this.messageContainer = this.container.find('.js-chat_content');
		this.sendByEnter = true;
		this.timerMessage = null;
		this.timerRoom = null;
		this.timerGlobal = null;
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
	 * Get last message ID.
	 * @returns {int}
	 */
	getLastMessageId() {
		return this.messageContainer.find('.js-chat-item:last').data('mid');
	}

	/**
	 * Get room list.
	 * @param reload
	 * @returns {jQuery}
	 */
	getRoomList(reload = false) {
		if (typeof this.roomList === 'undefined' || reload) {
			this.roomList = this.container.find('.js-room-list');
		}
		return this.roomList;
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
				this.buildParticipantsFromMessage();
				this.scrollToBottom();
			});
			inputMessage.val('');
		} else {
			Vtiger_Helper_Js.showPnotify({
				text: app.vtranslate('JS_MESSAGE_TOO_LONG'),
				type: 'error',
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
	createParticipantItem(data = {}) {
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
	 * Return the participants' ID list from the message.
	 * @returns {int[]}
	 */
	getParticipantsFromMessages() {
		let participantsId = [];
		this.messageContainer.find('.js-chat-item').each((index, element) => {
			let userId = $(element).data('userId');
			if ($.inArray(userId, participantsId) < 0) {
				participantsId.push(userId);
			}
		});
		return participantsId;
	}

	/**
	 * Create new participants.
	 * @param {int[]} participantsId
	 */
	createParticipants(participantsId = []) {
		if (!participantsId.length) {
			return;
		}
		const participants = this.container.find('.js-participants-list .js-users');
		let len = participantsId.length;
		for (let i = 0; i < len; ++i) {
			let userId = participantsId[i];
			let lastMessage = this.messageContainer.find('.js-chat-item[data-user-id=' + userId + ']:last');
			if (lastMessage.length) {
				participants.append(
					this.createParticipantItem({
						userName: lastMessage.find('.js-user-name').html(),
						role: lastMessage.find('.js-author').data('roleName'),
						message: lastMessage.find('.messages').html(),
						userId: userId,
					})
				);
			}
		}
	}

	/**
	 * Build a list of participants from the message.
	 */
	buildParticipantsFromMessage() {
		let currentParticipants = [];
		this.container.find('.js-participants-list .js-users .js-item-user').each((index, element) => {
			let userId = $(element).data('userId');
			currentParticipants.push(userId);
			let lastMessage = this.messageContainer.find('.js-chat-item[data-user-id=' + userId + ']:last');
			if (lastMessage.length) {
				$(element).find('.js-message').html(lastMessage.find('.messages').html());
			}
		});
		this.createParticipants(
			$(this.getParticipantsFromMessages()).not(currentParticipants).get()
		);
	}

	/**
	 * Build a list of participants
	 * @param {jQuery} element
	 * @param {bool} add
	 */
	buildParticipantsFromInput(element, reload = false) {
		const participants = this.container.find('.js-participants-list .js-users');
		if (reload) {
			participants.html('');
		}
		if (element.length) {
			let users = JSON.parse(element.val());
			let len = users.length;
			for (let i = 0; i < len; ++i) {
				participants.append(
					this.createParticipantItem({
						userName: users[i]['user_name'],
						role: users[i]['role_name'],
						message: users[i]['message'],
						userId: users[i]['user_id'],
					})
				);
			}
		}
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
	 * Scroll the chat content down.
	 */
	scrollToBottom() {
		const chatContent = this.messageContainer.closest('.js-chat-main-content');
		chatContent.animate({
			scrollTop: chatContent[0].scrollHeight
		}, 300);
	}

	/**
	 * Select room.
	 * @param {string} roomType
	 * @param {int} recordId
	 */
	selectRoom(roomType, recordId) {
		const roomList = this.getRoomList();
		roomList.find('.js-room').each((index, element) => {
			$(element).removeClass('active');
		});
		let itemRoom = roomList.find('.js-room-type[data-room-type=' + roomType + '] .js-room[data-record-id=' + recordId + ']');
		//console.log('itemRoom: ' + itemRoom.length + ' roomType: ' + roomType + ' recordId: ' + recordId);
		itemRoom.addClass('active');
		itemRoom.find('.js-room-cnt').html('');
		this.messageContainer.data('currentRoomType', roomType);
		this.messageContainer.data('currentRecordId', recordId);
	}

	/**
	 * Create a room element.
	 * @param {object}
	 * @returns {jQuery}
	 */
	createRoomItem(data = {}) {
		let itemRoom = this.container.find('.js-room-list .js-temp-item-room').clone(false, false);
		itemRoom.removeClass('js-temp-item-room').removeClass('hide');
		itemRoom.find('.js-room-name').html(data.name);
		itemRoom.attr('title', data.name + ' rid: ' + data.recordid);
		itemRoom.find('.js-room-cnt').html(data['cnt_new_message']);
		itemRoom.attr('data-record-id', data.recordid);
		return itemRoom;
	}

	/**
	 * Refresh all information about the rooms
	 * @param {object} data
	 */
	reloadRoomsDetail(data) {
		const currentRoomType = this.getCurrentRoomType();
		const currentRecordId = this.getCurrentRecordId();
		const roomList = this.getRoomList();
		this.selectRoom(data.currentRoom.roomType, data.currentRoom.recordId);
		//console.log('currentRoom: ' + JSON.stringify(data.currentRoom));
		for (let key in data.roomList) {
			let roomTypeList = roomList.find('.js-room-type[data-room-type="' + key + '"]');
			roomTypeList.html('');
			for (let idx in data.roomList[key]) {
				let itemRoom = this.createRoomItem(data.roomList[key][idx]);
				if (key === currentRoomType && data.roomList[key][idx]['recordid'] === currentRecordId) {
					itemRoom.addClass('active');
				}
				roomTypeList.append(itemRoom);
			}
		}
		this.registerSwitchRoom();
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
			this.request({
				view: 'Entries',
				mode: 'get',
				lastId: this.getLastMessageId(),
				roomType: this.getCurrentRoomType(),
				recordId: this.getCurrentRecordId()
			}, false).done((html) => {
				if (html) {
					if (!this.isRoomActive()) {
						this.activateRoom();
					}
					//this.buildParticipantsFromInput($('<div></div>').html(html).find('.js-participants-data'), false);
					this.messageContainer.append(html);
					this.buildParticipantsFromMessage();
					this.scrollToBottom();
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
		if (timer) {
			clearTimeout(this.timerRoom);
		}
		this.timerRoom = setTimeout(() => {
			this.request({
				action: 'Room',
				mode: 'getAll'
			}, false).done((data) => {
				this.reloadRoomsDetail(data['result']);
				if (timer) {
					this.getRoomsDetail(true);
				}
			});
		}, this.getRoomTimer());
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
			clearTimeout(this.timerMessage);
			clearTimeout(this.timerRoom);
			let element = $(e.currentTarget);
			let recordId = element.data('recordId');
			let roomType = element.closest('.js-room-type').data('roomType');
			this.request({
				view: 'Entries',
				mode: 'get',
				roomType: roomType,
				recordId: recordId
			}).done((html) => {
				this.selectRoom(roomType, recordId);
				this.buildParticipantsFromInput($('<div></div>').html(html).find('.js-participants-data'), true);
				this.messageContainer.html(html);
				this.getMessage(true);
				this.getRoomsDetail(true);
				this.scrollToBottom();
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

	/*registerListenEvent() {
		this.getMessage(true);
		this.getRoomsDetail(true);
	}*/

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
		//this.registerListenEvent();
		this.getMessage(true);
		this.registerCreateRoom();
	}

	/**
	 * Register modal events
	 */
	registerModalEvents() {
		this.getRoomList(true);
		this.getRoomsDetail(true);
		this.container.find('.close[data-dismiss="modal"]').on('click', (e) => {
			console.log('CLOSE MODAL');
			clearTimeout(this.timerMessage);
			clearTimeout(this.timerRoom);
		});
		this.registerBaseEvents();
		this.registerSwitchRoom();
	}
}
