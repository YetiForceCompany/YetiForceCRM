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
		this.sendByEnter = app.getCookie('chat-notSendByEnter') !== 'true';
		this.isSearchMode = false;
		this.searchValue = null;
		this.isSearchParticipantsMode = false;
		this.timerMessage = null;
		this.amountOfNewMessages = null;
		this.isSoundNotification = app.getCookie('chat-isSoundNotification') === 'true';
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
	 * Check if the user has enabled notifications.
	 * @returns {boolean}
	 */
	static isDesktopNotification() {
		return app.getCookie('chat-isDesktopNotification') === 'true';
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
		AppConnector.request($.extend({ module: 'Chat' }, data))
			.done(data => {
				aDeferred.resolve(data);
			})
			.always(() => {
				if (progress) {
					progressIndicator.progressIndicator({ mode: 'hide' });
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
			}
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
				mid = this.messageContainer.find('.js-chat-item:last').data('mid');
			}
			this.request({
				view: 'Entries',
				mode: 'send',
				roomType: this.getCurrentRoomType(),
				recordId: this.getCurrentRecordId(),
				message: inputMessage.html(),
				mid: mid
			}).done(html => {
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
		this.request(
			{
				view: 'Entries',
				mode: 'get',
				roomType: this.getCurrentRoomType(),
				recordId: this.getCurrentRecordId()
			},
			false
		).done(html => {
			if (html) {
				if (!this.isRoomActive()) {
					this.activateRoom();
				}
				if (reloadParticipants) {
					this.buildParticipantsFromInput(
						$('<div></div>')
							.html(html)
							.find('.js-participants-data'),
						true
					);
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
		this.request(
			{
				view: 'Entries',
				mode: 'getMore',
				lastId: btn.data('mid'),
				roomType: this.getCurrentRoomType(),
				recordId: this.getCurrentRecordId()
			},
			false
		).done(html => {
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
		clearTimeout(this.timerMessage);
		let mid = null;
		if (btn !== null) {
			mid = btn.data('mid');
		}
		this.request(
			{
				view: 'Entries',
				mode: 'search',
				searchVal: this.searchInput.val(),
				mid: mid,
				roomType: this.getCurrentRoomType(),
				recordId: this.getCurrentRecordId()
			},
			false
		).done(html => {
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
			let userName = $(element)
				.find('.js-user-name')
				.text()
				.toLowerCase();
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
			itemUser
				.find('.js-image .js-chat-image_src')
				.removeClass('hide')
				.attr('src', data.image);
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
				$(element)
					.find('.js-message')
					.html(lastMessage.find('.js-message').html());
			}
		});
		this.createParticipants(
			$(this.getParticipantsFromMessages(messageContainer))
				.not(currentParticipants)
				.get()
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
						image: users[i]['image']
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
		return (
			this.container.find('.js-container-chat').length === 0 ||
			!this.container.find('.js-container-chat').hasClass('hide')
		);
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
	 * Get message timer.
	 * @returns {int}
	 */
	getMessageTimer() {
		return this.messageContainer.data('messageTimer');
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
			chatContent.animate(
				{
					scrollTop: chatContent[0].scrollHeight
				},
				300
			);
		}
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
			this.request(
				{
					view: 'Entries',
					mode: 'get',
					lastId: this.getLastMessageId(),
					roomType: this.getCurrentRoomType(),
					recordId: this.getCurrentRecordId(),
					viewForRecord: true
				},
				false
			).done(html => {
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
	 * Register send event
	 */
	registerSendEvent() {
		const inputMessage = this.container.find('.js-chat-message');
		inputMessage.on('keydown', e => {
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
	 * Button favorites.
	 */
	registerButtonFavorites() {
		let btnRemove = this.container.find('.js-remove-from-favorites');
		let btnAdd = this.container.find('.js-add-from-favorites');
		btnRemove.off('click').on('click', e => {
			btnRemove.toggleClass('hide');
			btnAdd.toggleClass('hide');
			this.request({
				action: 'Room',
				mode: 'removeFromFavorites',
				roomType: this.getCurrentRoomType(),
				recordId: this.getCurrentRecordId()
			});
		});
		btnAdd.off('click').on('click', e => {
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
	 * Register load more messages.
	 */
	registerLoadMore() {
		this.messageContainer
			.find('.js-load-more')
			.off('click')
			.on('click', e => {
				let btn = $(e.currentTarget);
				if (this.isSearchMode) {
					this.searchMessage(btn);
				} else {
					this.getMore(btn);
				}
			});
	}

	/**
	 * Register search message events.
	 */
	registerSearchMessage() {
		this.searchInput.off('keydown').on('keydown', e => {
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
		this.container.find('.js-icon-search-message').on('click', e => {
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
		this.searchCancel.off('click').on('click', e => {
			e.preventDefault();
			this.turnOffSearchMode();
			this.getAll(false);
		});
	}

	/**
	 * Register search participants.
	 */
	registerSearchParticipants() {
		this.searchParticipantsInput.off('keyup').on('keyup', e => {
			let len = this.searchParticipantsInput.val().length;
			if (1 < len) {
				this.isSearchParticipantsMode = true;
				this.searchParticipantsCancel.removeClass('hide');
			} else if (len === 0) {
				this.turnOffSearchParticipantsMode();
			}
			this.searchParticipants();
		});
		this.searchParticipantsCancel.off('click').on('click', e => {
			this.turnOffSearchParticipantsMode();
			this.searchParticipants();
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
	 * Unregister events.
	 */
	unregisterEvents() {
		clearTimeout(this.timerMessage);
	}
};
