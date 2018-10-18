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
		this.request({
			view: 'Entries',
			mode: 'send',
			message: inputMessage.val()
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
			this.request({
				view: 'Entries',
				mode: 'get',
				roomId: element.data('roomId'),
				roomType: element.closest('.js-room-type').data('roomType')
			}).done((data) => {
				element.addClass('active');
				this.messageContainer.html(data);
			});
		});
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
	}

	/**
	 * Register modal events
	 */
	registerModalEvents() {
		this.registerBaseEvents();
		this.registerSwitchRoom();
	}
}
