/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';

/**
 * Class Chat_Js.
 * @type {Window.Chat_Js}
 */
window.Chat_JS = class Chat_Js {
	/**
	 * Get instance of Chat_Js.
	 * @returns {Chat_Js|Window.Chat_Js}
	 */
	static getInstance() {
		if (typeof Chat_Js.instance === 'undefined') {
			Chat_Js.instance = new Chat_Js();
		}
		return Chat_Js.instance;
	}

	/**
	 * Send chat message.
	 * @param {jQuery} inputMessage
	 */
	sendMessage(inputMessage) {
		if (inputMessage.val() === '') {
			return;
		}
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
	 * @param {jQuery} container
	 */
	registerSendEvent(container) {
		const self = this;
		const inputMessage = container.find('.js-chat-message');
		inputMessage.on('keydown', (e) => {
			console.log(e.keyCode);
			if (e.keyCode === 13) {
				e.preventDefault();
				self.sendMessage($(e.currentTarget));
				return false;
			}
		});
		container.find('.js-btn-send').on('click', (e) => {
			self.sendMessage(inputMessage);
		});
	}

	/**
	 * Register switch room.
	 * @param {jQuery} container
	 */
	registerSwitchRoom(container) {
		const self = this;
		container.find('.js-room-list .js-room').off('click').on('click', (e) => {
			let roomType = $(e.currentTarget).closest('.js-room-type').data('roomType');
			let roomId = $(e.currentTarget).data('roomId');
			let id = $(e.currentTarget).data('id');
			console.log('room: ' + roomId + ' t: ' + roomType + ' id: ' + id);
		});
	}

	/**
	 * Register chat events
	 * @param {jQuery} container
	 */
	registerEvents(container) {
		if (container.length) {
			this.registerSendEvent(container);
			this.registerSwitchRoom(container);
		}
	}
}
