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
		this.sendByEnter = app.getCookie("chat-notSendByEnter") !== 'true';
		this.isSearchMode = false;
		this.isHistoryMode = false;
		this.isUnreadMode = false;
		this.searchValue = null;
		this.isSearchParticipantsMode = false;
		this.timerMessage = null;
		this.timerRoom = null;
		this.amountOfNewMessages = null;
		this.isSoundNotification = app.getCookie("chat-isSoundNotification") === 'true';
		this.isModalWindow = false;
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
	 * Get chat header button.
	 * @returns {jQuery}
	 */
	static getHeaderChatButton() {
		if (typeof Chat_Js.headerChatButton === 'undefined') {
			if (window.location !== window.top.location) {
				Chat_Js.headerChatButton = window.top.$('.js-header-chat-button');
			} else {
				Chat_Js.headerChatButton = $('.js-header-chat-button');
			}
		}
		return Chat_Js.headerChatButton;
	}

	/**
	 * Return the time value for the global timer.
	 * @returns {int}
	 */
	static getRefreshTimeGlobal() {
		if (typeof Chat_Js.refreshTimerGlobal === 'undefined') {
			Chat_Js.refreshTimeGlobal = Chat_Js.getHeaderChatButton().data('refreshTimeGlobal');
		}
		return Chat_Js.refreshTimeGlobal;
	}

	/**
	 * Register tracking events.
	 */
	static registerTrackingEvents() {
		const headerChatButton = Chat_Js.getHeaderChatButton();
		const showNumberOfNewMessages = headerChatButton.data('showNumberOfNewMessages');
		const lblChatNewMessage = headerChatButton.data('lblChatNewMessage');
		const lblChat = headerChatButton.data('lblChat');
		if (headerChatButton.data('userSwitched')) {
			headerChatButton.on('click', (e) => {
				Vtiger_Helper_Js.showPnotify({
					text: headerChatButton.data('lblChatUserSwitched'),
					type: 'error',
					animation: 'show'
				});
			});
			return;
		}
		if (!Chat_Js.getRefreshTimeGlobal()) {
			return;
		}
		Chat_Js.timerGlobal = setTimeout(() => {
			AppConnector.request({
				module: 'Chat',
				action: 'Room',
				mode: 'tracking'
			}).done(function (data) {
				const badge = headerChatButton.find('.js-badge');
				if (data.result > 0) {
					headerChatButton.toggleClass('btn-light').toggleClass('btn-danger');
					if (showNumberOfNewMessages) {
						badge.removeClass('hide');
						badge.html(data.result);
					}
				} else if (headerChatButton.hasClass('btn-danger')) {
					headerChatButton.removeClass('btn-danger').addClass('btn-light');
					if (showNumberOfNewMessages) {
						badge.addClass('hide');
						badge.html('');
					}
				}
				if (data.result > Chat_Js.amountOfNewMessages) {
					if (app.getCookie("chat-isSoundNotification") === 'true') {
						app.playSound('CHAT');
					}
					if (Chat_Js.desktopPermission()) {
						let message = lblChatNewMessage;
						if (showNumberOfNewMessages) {
							message += ' ' + data.result;
						}
						app.showNotify({
							text: message,
							title: lblChat,
							type: 'success'
						}, true);
					}
				}
				Chat_Js.amountOfNewMessages = data.result;
				Chat_Js.registerTrackingEvents();
			});
		}, Chat_Js.getRefreshTimeGlobal());
	}

	/**
	 * Unregister tracking events.
	 */
	static unregisterTrackingEvents() {
		clearTimeout(Chat_Js.timerGlobal);
		const headerChatButton = Chat_Js.getHeaderChatButton();
		let badge = headerChatButton.find('.js-badge');
		badge.addClass('hide');
		badge.html('');
		headerChatButton.removeClass('btn-danger').addClass('btn-light');
	}

	/**
	 * Check if the user has enabled notifications.
	 * @returns {boolean}
	 */
	static desktopPermission() {
		if (!Chat_Js.isDesktopNotification()) {
			return false;
		}
		if (!Chat_Js.checkDesktopPermission()) {
			app.setCookie("chat-isDesktopNotification", false, 365);
			return false;
		}
		return true;
	}

	/**
	 * Check if the user has enabled notifications.
	 * @returns {boolean}
	 */
	static isDesktopNotification() {
		return app.getCookie("chat-isDesktopNotification") === 'true';
	}

	/**
	 * Check desktop permission.
	 * @returns {boolean}
	 */
	static checkDesktopPermission() {
		return PNotify.modules.Desktop.checkPermission() === 0;
	}

	/**
	 * Data initialization from the container.
	 * @param {jQuery} container
	 */
	init(container) {
		this.container = container;
		this.messageContainer = this.container.find('.js-chat_content');
		this.maxLengthMessage = this.messageContainer.data('maxLengthMessage');
		this.searchInput = this.container.find('.js-search-message');
		this.searchCancel = this.container.find('.js-search-cancel');
		this.searchParticipantsInput = this.container.find('.js-search-participants');
		this.searchParticipantsCancel = this.container.find('.js-search-participants-cancel');
		this.participants = this.container.find('.js-participants-list .js-users');
	}

	/**
	 * Sending HTTP requests to the chat module.
	 * @param {object} data
	 * @param {bool} progress
	 * @param {jQuery} elementTo
	 * @returns {object}
	 */
	request(data = {}, progress = true, elementTo = this.messageContainer) {
		const aDeferred = $.Deferred();
		let progressIndicator = null;
		if (progress) {
			progressIndicator = this.progressShow(elementTo);
		}
		AppConnector.request($.extend({module: 'Chat'}, data)).done((data) => {
			aDeferred.resolve(data);
		}).fail(() => {
			if (this.isModalWindow) {
				Vtiger_Helper_Js.showPnotify({
					text: app.vtranslate('JS_UNEXPECTED_ERROR'),
					type: 'error',
					animation: 'show'
				});
				app.hideModalWindow();
				this.unregisterEvents();
				Chat_Js.registerTrackingEvents();
			}
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
	 * Show progress indicator.
	 * @param {jQuery} elementTo
	 * @returns {jQuery}
	 */
	progressShow(elementTo) {
		return $.progressIndicator({
			position: 'html',
			blockInfo: {
				enabled: true,
				elementToBlock: elementTo
			},
		});
	}

	/**
	 * Send chat message.
	 * @param {jQuery} inputMessage
	 */
	sendMessage(inputMessage) {
		let len = inputMessage.html().length;
		if (0 === len) {
			return;
		}
		if (len < this.maxLengthMessage) {
			clearTimeout(this.timerMessage);
			let mid = null;
			if (!this.isSearchMode) {
				mid = this.messageContainer.find('.js-chat-item:last').data('mid')
			}
			this.request({
				view: 'Entries',
				mode: 'send',
				roomType: this.getCurrentRoomType(),
				recordId: this.getCurrentRecordId(),
				message: inputMessage.html(),
				mid: mid
			}).done((html) => {
				if (this.isSearchMode) {
					this.messageContainer.html(html);
					this.turnOffSearchMode();
					this.registerLoadMore();
				} else {
					this.messageContainer.append(html);
				}
				this.getMessage(true);
				this.buildParticipantsFromMessage($('<div></div>').html(html));
				this.scrollToBottom(false);
			});
			inputMessage.html('');
		} else {
			Vtiger_Helper_Js.showPnotify({
				text: app.vtranslate('JS_MESSAGE_TOO_LONG'),
				type: 'error',
				animation: 'show'
			});
		}
	}

	/**
	 * Get the last chat message.
	 */
	getAll(reloadParticipants = true) {
		clearTimeout(this.timerMessage);
		this.request({
			view: 'Entries',
			mode: 'get',
			roomType: this.getCurrentRoomType(),
			recordId: this.getCurrentRecordId()
		}, false).done((html) => {
			if (html) {
				if (!this.isRoomActive()) {
					this.activateRoom();
				}
				if (reloadParticipants) {
					this.buildParticipantsFromInput($('<div></div>').html(html).find('.js-participants-data'), true);
				}
				this.messageContainer.html(html);
				this.scrollToBottom();
				this.registerLoadMore();
			}
			this.getMessage(true);
		});
	}

	/**
	 * Get more messages.
	 * @param {jQuery} btn
	 */
	getMore(btn) {
		clearTimeout(this.timerMessage);
		this.request({
			view: 'Entries',
			mode: 'getMore',
			lastId: btn.data('mid'),
			roomType: this.getCurrentRoomType(),
			recordId: this.getCurrentRecordId()
		}, false).done((html) => {
			if (html) {
				btn.before(html);
				btn.remove();
				this.registerLoadMore();
			}
			this.getMessage(true);
		});
	}

	/**
	 * Search messages.
	 * @param {jQuery} btn
	 */
	searchMessage(btn = null) {
		this.isHistoryMode = false;
		clearTimeout(this.timerMessage);
		let mid = null;
		if (btn !== null) {
			mid = btn.data('mid');
		}
		this.request({
			view: 'Entries',
			mode: 'search',
			searchVal: this.searchInput.val(),
			mid: mid,
			roomType: this.getCurrentRoomType(),
			recordId: this.getCurrentRecordId()
		}, false).done((html) => {
			if (btn === null) {
				this.messageContainer.html(html);
			} else {
				btn.before(html);
				btn.remove();
			}
			this.registerLoadMore();
			if (mid == null) {
				this.scrollToBottom();
			}
		});
	}

	/**
	 * Get history.
	 * @param {jQuery} btn
	 * @param {string} groupHistory
	 */
	history(btn = null, groupHistory = 'crm') {
		this.isSearchMode = false;
		this.isHistoryMode = true;
		this.isUnreadMode = false;
		clearTimeout(this.timerMessage);
		let mid = null;
		if (btn !== null) {
			mid = btn.data('mid');
		}
		this.turnOffInputAndBtn();
		this.request({
			view: 'Entries',
			mode: 'history',
			mid: mid,
			groupHistory: groupHistory
		}, true).done((html) => {
			if (btn === null) {
				this.messageContainer.html(html);
			} else {
				btn.before(html);
				btn.remove();
			}
			this.registerLoadMore();
		});
	}

	/**
	 * Turn off unread mode.
	 */
	turnOffUnreadMode() {
		this.turnOnInputAndBtnInRoom();
		this.isUnreadMode = false;
	}

	/**
	 * Get unread messages.
	 */
	unread() {
		clearTimeout(this.timerMessage);
		this.isSearchMode = false;
		this.isHistoryMode = false;
		this.isUnreadMode = true;
		this.turnOffInputAndBtn();
		this.request({
			view: 'Entries',
			mode: 'unread'
		}, true).done((html) => {
			this.messageContainer.html(html);
			this.participants.html('');
			this.buildParticipantsFromMessage($('<div></div>').html(html));
		});
	}

	/**
	 * Turn off input and button.
	 */
	turnOffInputAndBtn() {
		let container = this.container;
		container.find('.js-users').addClass('hide');
		container.find('.js-scrollbar').addClass('o-chat__scrollbar--history');
		container.find('.js-footer-history-hide').removeClass('hide');
		container.find('.js-footer-history').addClass('hide');
		container.find('.js-room').removeClass('active o-chat__room');
		this.messageContainer.removeClass('col-9');
		this.messageContainer.addClass('col-12');
		container.find('.js-chat-message').addClass('hide');
		container.find('.js-btn-send').addClass('hide');
		container.find('.js-input-group-search').addClass('hide');
		let footer = container.find('.js-chat-footer');
		if (this.isHistoryMode) {
			this.container.find('.js-chat-nav-history').removeClass('hide');
			this.container.find('.js-footer-history-hide .js-breadcrumb-item').text(footer.data('lblHistory'));
		} else if (this.isUnreadMode) {
			this.container.find('.js-chat-nav-history').addClass('hide');
			this.container.find('.js-footer-history-hide .js-breadcrumb-item').text(footer.data('lblUnread'));
		}
	}

	/**
	 * Select nav history.
	 */
	selectNavHistory() {
		this.container.find('.js-chat-nav-history .js-chat-link').each((index, element) => {
			$(element).on('click', () => {
				this.history(null, $(element).data('groupName'));
			});
		});
	}

	/**
	 * Turn on after click in room.
	 */
	turnOnInputAndBtnInRoom() {
		this.container.on('click', '.js-room', () => {
			let container = this.container;
			container.find('.js-input-group-search').removeClass('hide');
			container.find('.js-users').removeClass('hide');
			container.find('.js-scrollbar').removeClass('o-chat__scrollbar--history');
			let messageContainer = container.find('.js-message-container');
			container.find('.js-footer-history-hide').addClass('hide');
			container.find('.js-footer-history').removeClass('hide');
			messageContainer.removeClass('col-12');
			messageContainer.addClass('col-9');
			container.find('.js-chat-nav-history').addClass('hide');
			container.find('.js-chat-message').removeClass('hide');
			container.find('.js-btn-send').removeClass('hide');
		})
	}

	/**
	 * Turn off search mode.
	 */
	turnOffSearchMode() {
		this.searchCancel.addClass('hide');
		this.searchInput.val('');
		this.isSearchMode = false;
	}

	/**
	 * Turn off search participants mode.
	 */
	turnOffSearchParticipantsMode() {
		this.searchParticipantsCancel.addClass('hide');
		this.searchParticipantsInput.val('');
		this.isSearchParticipantsMode = false;
		this.participants.find('.js-item-user').each((index, element) => {
			$(element).removeClass('hide');
		});
	}

	/**
	 * Search participants.
	 */
	searchParticipants() {
		let searchVal = this.searchParticipantsInput.val().toLowerCase();
		this.participants.find('.js-item-user').each((index, element) => {
			let userName = $(element).find('.js-user-name').text().toLowerCase();
			$(element).toggleClass('hide', !(userName.indexOf(searchVal) >= 0));
		});
		this.container.find('.js-participants-list').scrollTop(0);
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
		if (data.image) {
			itemUser.find('.js-image .js-chat-image_icon').addClass('hide');
			itemUser.find('.js-image .js-chat-image_src').removeClass('hide').attr('src', data.image);
		}
		return itemUser;
	}

	/**
	 * Return the participants' ID list from the message.
	 * @returns {int[]}
	 */
	getParticipantsFromMessages(messageContainer) {
		let participantsId = [];
		messageContainer.find('.js-chat-item').each((index, element) => {
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
		let len = participantsId.length;
		for (let i = 0; i < len; ++i) {
			let userId = participantsId[i];
			let lastMessage = this.messageContainer.find('.js-chat-item[data-user-id=' + userId + ']:last');
			if (lastMessage.length) {
				this.participants.append(
					this.createParticipantItem({
						userName: lastMessage.find('.js-author').data('userName'),
						role: lastMessage.find('.js-author').data('roleName'),
						message: lastMessage.find('.messages').html(),
						userId: userId,
						image: lastMessage.find('.js-author .js-image .js-chat-image_src').attr('src')
					})
				);
			}
		}
	}

	/**
	 * Build a list of participants from the message.
	 */
	buildParticipantsFromMessage(messageContainer) {
		if (this.isSearchParticipantsMode) {
			return;
		}
		let currentParticipants = [];
		this.participants.find('.js-item-user').each((index, element) => {
			let userId = $(element).data('userId');
			currentParticipants.push(userId);
			let lastMessage = messageContainer.find('.js-chat-item[data-user-id=' + userId + ']:last');
			if (lastMessage.length) {
				$(element).find('.js-message').html(lastMessage.find('.js-message').html());
			}
		});
		this.createParticipants(
			$(this.getParticipantsFromMessages(messageContainer)).not(currentParticipants).get()
		);
	}

	/**
	 * Build a list of participants
	 * @param {jQuery} element
	 * @param {bool} add
	 */
	buildParticipantsFromInput(element, reload = false) {
		if (this.isSearchParticipantsMode) {
			return;
		}
		if (reload) {
			this.participants.html('');
		}
		if (element.length) {
			let users = JSON.parse(element.val());
			let len = users.length;
			for (let i = 0; i < len; ++i) {
				this.participants.append(
					this.createParticipantItem({
						userName: users[i]['user_name'],
						role: users[i]['role_name'],
						message: users[i]['message'],
						userId: users[i]['user_id'],
						image: users[i]['image'],
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
	 * @returns {string}
	 */
	getCurrentRoomType() {
		return this.messageContainer.data('currentRoomType');
	}

	/**
	 * Get current record ID.
	 * @returns {int}
	 */
	getCurrentRecordId() {
		return this.messageContainer.data('currentRecordId');
	}

	/**
	 * Is this a view for the record.
	 * @returns {boolean}
	 */
	isViewForRecord() {
		return this.messageContainer.data('viewForRecord');
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
	scrollToBottom(scrollToTop = true) {
		const chatContent = this.messageContainer.closest('.js-chat-main-content');
		if (scrollToTop) {
			chatContent.scrollTop(0);
		}
		if (chatContent.length) {
			chatContent.animate({
				scrollTop: chatContent[0].scrollHeight
			}, 300);
		}
	}

	/**
	 * Play sound notification.
	 */
	soundNotification() {
		if (this.isSoundNotification) {
			app.playSound('CHAT');
		}
	}

	/**
	 * Select room.
	 * @param {string} roomType
	 * @param {int} recordId
	 */
	selectRoom(roomType, recordId) {
		const roomList = this.getRoomList();
		roomList.find('.js-room').each((index, element) => {
			$(element).removeClass('active o-chat__room');
		});
		let itemRoom = roomList.find('.js-room-type[data-room-type=' + roomType + '] .js-room[data-record-id=' + recordId + ']');
		itemRoom.addClass('active o-chat__room');
		this.getRoomName(itemRoom, roomType);
		itemRoom.find('.js-room-cnt').html('');
		this.messageContainer.data('currentRoomType', roomType);
		this.messageContainer.data('currentRecordId', recordId);
	}

	/**
	 * Create a room element.
	 * @param {object}
	 * @returns {jQuery}
	 */
	createRoomItem(data = {}, favorite = false, roomType = 'global') {
		let itemRoom = this.container.find('.js-room-list .js-temp-item-room').clone(false, false);
		itemRoom.removeClass('js-temp-item-room').removeClass('hide');
		itemRoom.addClass('d-flex');
		itemRoom.find('.js-room-name').html(data.name);
		itemRoom.attr('title', data.name);
		if (roomType === 'crm') {
			itemRoom.find('.js-link')
				.removeClass('hide')
				.attr('href', "index.php?module=" + data['moduleName'] + "&view=Detail&record=" + data.recordid);
		}
		if (data['cnt_new_message'] == 0) {
			itemRoom.find('.js-room-cnt').html('');
		} else {
			itemRoom.find('.js-room-cnt').html(data['cnt_new_message']);
		}
		if (favorite) {
			itemRoom.find('.js-remove-favorites').removeClass('hide');
		}
		itemRoom.attr('data-record-id', data.recordid);
		return itemRoom;
	}

	/**
	 * Get room name.
	 * @param {object}
	 * @param {string} roomType
	 */
	getRoomName(data, roomType) {
		let container = this.container;
		let containerFooter = container.find('.js-chat-footer');
		container.find('.js-group-name').each(function (e) {
			let element = $(this);
			if (element.data('group') == roomType) {
				containerFooter.find('.js-footer-group-name').text(element.text());
			}
		});
		containerFooter.find('.js-footer-room-name').text(data.find('.js-room-name').text());
	}

	/**
	 * Refresh all information about the rooms
	 * @param {object} data
	 */
	reloadRoomsDetail(data) {
		const currentRoomType = this.getCurrentRoomType();
		const currentRecordId = this.getCurrentRecordId();
		const roomList = this.getRoomList();
		let cnt = 0;
		this.selectRoom(data.currentRoom.roomType, data.currentRoom.recordId);
		for (let key in data.roomList) {
			let roomTypeList = roomList.find('.js-room-type[data-room-type="' + key + '"]').not(".js-hide-group");
			roomTypeList.html('');
			for (let idx in data.roomList[key]) {
				let newMessage = data.roomList[key][idx]['cnt_new_message'];
				if (newMessage !== null) {
					cnt += newMessage;
				}
				let itemRoom = this.createRoomItem(data.roomList[key][idx], roomTypeList.data('favoriteRemoveBtn'), key);
				if (key === currentRoomType && data.roomList[key][idx]['recordid'] === currentRecordId) {
					itemRoom.addClass('active o-chat__room');
				}
				roomTypeList.append(itemRoom);
			}
		}
		if (this.amountOfNewMessages !== null && cnt > this.amountOfNewMessages) {
			this.soundNotification();
		}
		this.amountOfNewMessages = cnt;
		this.registerSwitchRoom();
		this.registerButtonFavoritesInModal();
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
				recordId: this.getCurrentRecordId(),
				viewForRecord: this.isViewForRecord()
			}, false).done((html) => {
				if (html) {
					if (!this.isRoomActive()) {
						this.activateRoom();
					}
					this.messageContainer.append(html);
					this.buildParticipantsFromMessage($('<div></div>').html(html));
					this.scrollToBottom(false);
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
		const inputMessage = this.container.find('.js-chat-message');
		inputMessage.on('keydown', (e) => {
			if (!this.sendByEnter) {
				return;
			}
			if (!e.shiftKey && e.keyCode === 13) {
				e.preventDefault();
				this.sendMessage(inputMessage);
			}
		});
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
			}, true, this.container).done((html) => {
				this.turnOffSearchParticipantsMode();
				this.selectRoom(roomType, recordId);
				this.buildParticipantsFromInput($('<div></div>').html(html).find('.js-participants-data'), true);
				this.messageContainer.html(html);
				this.getMessage(true);
				this.getRoomsDetail(true);
				this.scrollToBottom();
				this.registerLoadMore();
				this.turnOffSearchMode();
				this.isHistoryMode = false;
				this.turnOffUnreadMode();
			});
		});
	}

	/**
	 * Button favorites.
	 */
	registerButtonFavorites() {
		let btnRemove = this.container.find('.js-remove-from-favorites');
		let btnAdd = this.container.find('.js-add-from-favorites');
		btnRemove.off('click').on('click', (e) => {
			btnRemove.toggleClass('hide');
			btnAdd.toggleClass('hide');
			this.request({
				action: 'Room',
				mode: 'removeFromFavorites',
				roomType: this.getCurrentRoomType(),
				recordId: this.getCurrentRecordId()
			});
		});
		btnAdd.off('click').on('click', (e) => {
			btnRemove.toggleClass('hide');
			btnAdd.toggleClass('hide');
			this.request({
				action: 'Room',
				mode: 'addToFavorites',
				roomType: this.getCurrentRoomType(),
				recordId: this.getCurrentRecordId()
			});
		});
	}

	/**
	 * Register buttons favorites in modal.
	 */
	registerButtonFavoritesInModal() {
		this.getRoomList().find('.js-room .js-remove-favorites').off('click').on('click', (e) => {
			clearTimeout(this.timerMessage);
			e.stopPropagation();
			let btn = $(e.currentTarget);
			let item = btn.closest('.js-room');
			let roomType = btn.closest('.js-room-type').data('roomType');
			this.request({
				action: 'Room',
				mode: 'removeFromFavorites',
				roomType: roomType,
				recordId: item.data('recordId')
			}).done(() => {
				if (roomType === 'group') {
					let listGroup = this.getRoomList().find('.js-hide-group');
					item.detach();
					item.find('.js-remove-favorites').addClass('hide');
					item.find('.js-add-favorites').removeClass('hide');
					listGroup.append(item);
					this.container.find('.js-btn-more-remove').removeClass('hide');
					this.container.find('.js-btn-more').addClass('hide');
					listGroup.removeClass('hide');
				} else {
					item.remove();
				}
				this.getRoomsDetail(true);
			});
		});
		this.getRoomList().find('.js-room .js-add-favorites').off('click').on('click', (e) => {
			e.stopPropagation();
			let btn = $(e.currentTarget);
			let item = btn.closest('.js-room');
			let roomType = btn.closest('.js-room-type').data('roomType');
			this.request({
				action: 'Room',
				mode: 'addToFavorites',
				roomType: roomType,
				recordId: item.data('recordId')
			}).done(() => {
				if (roomType === 'group') {
					let listGroup = this.getRoomList().find('.js-room-type[data-room-type=group]').not('.js-hide-group');
					item.detach();
					item.find('.js-remove-favorites').removeClass('hide');
					item.find('.js-add-favorites').addClass('hide');
					listGroup.append(item);
					if (this.getRoomList().find('.js-hide-group .js-room').length === 0) {
						this.container.find('.js-btn-more-remove').addClass('hide');
					}
				} else {
					item.remove();
				}
			});
		});
	}

	/**
	 * Button more in room.
	 */
	registerButtonMoreInRoom() {
		let btnMore = this.container.find('.js-btn-more');
		let btnMoreRemove = this.container.find('.js-btn-more-remove');
		let listHideGroup = this.container.find('.js-hide-group');
		btnMore.on('click', (e) => {
			listHideGroup.toggleClass('hide');
			btnMore.toggleClass('hide');
			btnMoreRemove.toggleClass('hide');
		});
		btnMoreRemove.on('click', (e) => {
			listHideGroup.toggleClass('hide');
			btnMore.toggleClass('hide');
			btnMoreRemove.toggleClass('hide');
		});
	}

	/**
	 * Register load more messages.
	 */
	registerLoadMore() {
		this.messageContainer.find('.js-load-more').off('click').on('click', (e) => {
			let btn = $(e.currentTarget);
			if (this.isSearchMode) {
				this.searchMessage(btn);
			} else if (this.isHistoryMode) {
				this.history(
					btn,
					this.container.find('.js-chat-nav-history .js-chat-link .active').closest('.js-chat-link').data('groupName')
				);
			} else {
				this.getMore(btn);
			}
		});
	}

	/**
	 * Register search message events.
	 */
	registerSearchMessage() {
		this.searchInput.off('keydown').on('keydown', (e) => {
			if (e.keyCode === 13) {
				e.preventDefault();
				if (this.searchInput.val() === '') {
					this.searchCancel.addClass('hide');
					this.isSearchMode = false;
				} else {
					this.isSearchMode = true;
					this.searchCancel.removeClass('hide');
					this.searchMessage();
				}
			}
		});
		this.container.find('.js-icon-search-message').on('click', (e) => {
			e.preventDefault();
			if (this.searchInput.val() === '') {
				this.searchCancel.addClass('hide');
				this.isSearchMode = false;
			} else {
				this.isSearchMode = true;
				this.searchCancel.removeClass('hide');
				this.searchMessage();
			}
		});
		this.searchCancel.off('click').on('click', (e) => {
			e.preventDefault();
			this.turnOffSearchMode();
			this.getAll(false);
		});
	}

	/**
	 * Register search participants.
	 */
	registerSearchParticipants() {
		this.searchParticipantsInput.off('keyup').on('keyup', (e) => {
			let len = this.searchParticipantsInput.val().length;
			if (1 < len) {
				this.isSearchParticipantsMode = true;
				this.searchParticipantsCancel.removeClass('hide');
			} else if (len === 0) {
				this.turnOffSearchParticipantsMode();
			}
			this.searchParticipants();
		});
		this.searchParticipantsCancel.off('click').on('click', (e) => {
			this.turnOffSearchParticipantsMode();
			this.searchParticipants();
		});
	}

	/**
	 * Register button history.
	 */
	registerButtonHistory() {
		this.container.find('.js-btn-history').off('click').on('click', (e) => {
			this.history();
		});
	}

	/**
	 * Register button settings.
	 */
	registerButtonSettings() {
		this.container.find('.js-btn-settings').off('click').on('click', (e) => {

		});
	}

	/**
	 * Register button desktop notification.
	 */
	registerButtonDesktopNotification() {
		let btnDesktop = this.container.find('.js-btn-desktop-notification');
		let iconOn = btnDesktop.data('iconOn');
		let iconOff = btnDesktop.data('iconOff');
		if (Chat_Js.isDesktopNotification() && !Chat_Js.checkDesktopPermission()) {
			app.setCookie("chat-isDesktopNotification", false, 365);
			btnDesktop.find('.js-icon').removeClass(iconOn).addClass(iconOff);
		} else if (Chat_Js.isDesktopNotification()) {
			btnDesktop.find('.js-icon').removeClass(iconOff).addClass(iconOn);
		}
		btnDesktop.off('click').on('click', (e) => {
			btnDesktop.find('.js-icon').toggleClass(iconOn + " " + iconOff);
			if (btnDesktop.find('.js-icon').hasClass(iconOn)) {
				if (!Chat_Js.checkDesktopPermission()) {
					PNotify.modules.Desktop.permission();
					setTimeout(() => {
						if (!Chat_Js.checkDesktopPermission()) {
							app.setCookie("chat-isDesktopNotification", false, 365);
							btnDesktop.find('.js-icon').removeClass(iconOn).addClass(iconOff);
							this.desktopNotificationShouldReload = true;
							Vtiger_Helper_Js.showPnotify({
								text: app.vtranslate('JS_NO_DESKTOP_PERMISSION'),
								type: 'info',
								animation: 'show'
							});
						} else {
							app.setCookie("chat-isDesktopNotification", true, 365);
						}
					}, 2000);
				}
			} else {
				app.setCookie("chat-isDesktopNotification", false, 365);
			}
		});
	}

	/**
	 * Register button bell.
	 */
	registerButtonBell() {
		let btnBell = this.container.find('.js-btn-bell');
		btnBell.off('click').on('click', (e) => {
			let icon = btnBell.find('.js-icon');
			icon.toggleClass(btnBell.data('iconOn')).toggleClass(btnBell.data('iconOff'));
			this.isSoundNotification = icon.hasClass(btnBell.data('iconOn'));
			app.setCookie("chat-isSoundNotification", this.isSoundNotification, 365);
		});
	}

	/**
	 * Register button unread.
	 */
	registerButtonUnread() {
		this.container.find('.js-btn-unread').on('click', (e) => {
			this.unread();
		});
	}

	/**
	 * Button that enables / disables the sending of messages by pressing the ENTER button.
	 */
	registerButtonToggleEnter() {
		let btnEnter = this.container.find('.js-btn-enter');
		btnEnter.on('click', (e) => {
			this.sendByEnter = !this.sendByEnter;
			app.setCookie("chat-notSendByEnter", !this.sendByEnter, 365);
			let icon = btnEnter.find('.js-icon');
			icon.toggleClass(btnEnter.data('iconOn'), this.sendByEnter).toggleClass(btnEnter.data('iconOff'), !this.sendByEnter);
		});
	}

	/**
	 * Register close modal.
	 */
	registerCloseModal() {
		this.container.find('.close[data-dismiss="modal"]').on('click', (e) => {
			this.unregisterEvents();
			Chat_Js.registerTrackingEvents();
		});
	}

	/**
	 * Register base events
	 */
	registerBaseEvents() {
		this.registerSendEvent();
		this.registerLoadMore();
		this.getMessage(true);
		this.registerButtonFavorites();
		this.registerSearchMessage();
		this.registerSearchParticipants();
		new App.Fields.Text.Completions(this.container.find('.js-completions'));
		setTimeout(() => {
			this.scrollToBottom();
		}, 100);
	}

	/**
	 * Register modal events
	 */
	registerModalEvents() {
		this.getRoomList(true);
		this.getRoomsDetail(true);
		this.registerBaseEvents();
		this.registerSwitchRoom();
		this.registerButtonHistory();
		this.registerButtonSettings();
		this.registerButtonBell();
		this.registerButtonUnread();
		this.registerButtonFavoritesInModal();
		this.registerButtonMoreInRoom();
		this.registerButtonDesktopNotification();
		this.registerButtonToggleEnter();
		this.registerCloseModal();
		this.turnOnInputAndBtnInRoom();
		this.selectNavHistory();
		Chat_Js.unregisterTrackingEvents();
		this.isModalWindow = true;
	}

	/**
	 * Unregister events.
	 */
	unregisterEvents() {
		clearTimeout(this.timerMessage);
		clearTimeout(this.timerRoom);
	}
};
