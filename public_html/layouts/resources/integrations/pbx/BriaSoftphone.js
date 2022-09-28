/* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
'use strict';
Integrations_Pbx_Base.driver = 'BriaSoftphone';
/**
 * @classdesc Bria Softphone Pbx integrations class.
 * @class
 */
window.Integrations_Pbx_BriaSoftphone = class Integrations_Pbx_BriaSoftphone extends Integrations_Pbx_Base {
	/** @inheritdoc */
	constructor(container) {
		super(container);
		this.accountName = this.lastEvent = this.status = this.connected = false;
		try {
			this.initWebsocket();
		} catch (e) {
			console.warn('WebSocket exception: ' + e);
			this.connected = false;
		}
		this.container.find('.js-phone-status-btn').on('click', (e) => {
			if (this.connected) {
				this.bringToFront();
			}
		});
	}
	/** @inheritdoc */
	performCall(data) {
		this.apiCall(
			'call',
			`<dial type="audio"><number>${data.phone}</number><displayName></displayName><suppressMainWindow>false</suppressMainWindow></dial>`
		);
	}
	/**
	 * Connection initialization via websocket
	 */
	initWebsocket() {
		this.connected = false;
		this.websocket = new WebSocket('wss://cpclientapi.softphone.com:9002/counterpath/socketapi/v1/');
		this.websocket.onopen = (e) => {
			this.connected = true;
			this.setStatus('Connected', e);
			// onConnectedToWebSocket(); ????
			this.getStatus('account', '<accountType>sip</accountType>');
			// this.getStatus('callHistory', '<count>2</count><entryType>all</entryType>');
			// this.getStatus('phone');
			// this.getStatus('call');
			// this.getStatus('presence');
		};
		this.websocket.onclose = (e) => {
			if (this.connected) {
				this.setStatus('Disconnected', e);
			} else {
				this.setStatus('Close', e);
			}
			this.connected = false;
			setTimeout(() => {
				this.initWebsocket();
			}, 5000);
		};
		this.websocket.onerror = (e) => {
			this.connected = false;
			this.setStatus('Error', e);
			this.websocket.close();
		};
		this.websocket.onmessage = (e) => {
			this.received(e.data);
		};
	}
	/**
	 * Update button
	 */
	updateBtn() {
		const btn = this.container.find('.js-phone-status-btn'),
			btnIcon = btn.find('.js-icon'),
			btnText = btn.find('.js-text');
		btn.attr('class', function (i, c) {
			return c.replace(/(^|\s)btn-\S+/g, '');
		});
		let title = this.status;
		switch (this.status) {
			case 'Connected':
				btn.addClass('btn-success');
				btnIcon.removeClass().addClass('fa-solid fa-phone-flip js-icon');
				break;
			case 'Service Unavailable':
				btn.addClass('btn-warning');
				btnIcon.removeClass().addClass('fa-solid fa-phone-slash js-icon');
				break;
			case 'Disconnected':
			case 'Close':
				btn.addClass('btn-danger');
				btnIcon.removeClass().addClass('fa-solid fa-phone-slash js-icon');
				break;
		}
		if (this.lastEvent && (this.lastEvent.code || this.lastEvent.name)) {
			title += ' (' + (this.lastEvent.code || this.lastEvent.name) + ')';
		}
		btn.attr('title', title);
		if (this.accountName) {
			btnText.text(this.accountName).addClass('ml-2');
		} else {
			btnText.removeClass('ml-2');
		}
	}
	/**
	 * Message event
	 * @param {object} data
	 */
	received(data) {
		// console.log(data);
		const message = this.parseMessage(data);
		// console.log(message);
		if (message['errorCode']) {
			this.setStatus(message['errorText']);
		} else {
			switch (message['messageType']) {
				case 'RESPONSE':
					this.response(message['xml']);
					break;
				case 'EVENT':
					this.event(message['xml']);
					break;
			}
		}
		this.updateBtn();
	}
	/**
	 * Response message type
	 * @param {jQuery} xml
	 */
	response(xml) {
		const self = this,
			type = xml.children(':first').attr('type'),
			fn = 'request' + type.charAt(0).toUpperCase() + type.slice(1);
		self.log('← ' + fn, this);
		if (fn in self) {
			self[fn](xml.find(type));
		}
	}
	/**
	 * Event message type
	 * @param {jQuery} xml
	 */
	event(xml) {
		const self = this,
			type = xml.children(':first').attr('type'),
			fn = 'event' + type.charAt(0).toUpperCase() + type.slice(1);
		self.log('← ' + fn, this);
		if (fn in self) {
			self[fn]();
		}
	}
	/**
	 * Account details request
	 * @param {jQuery} xml
	 */
	requestAccount(xml) {
		this.accountName = xml.find('accountName').text();
	}
	/**
	 * Call history details request
	 * @param {jQuery} xml
	 */
	requestCallHistory(xml) {
		let calls = [];
		xml.each((_, callsXml) => {
			let call = {};
			$(callsXml)
				.children()
				.each((_, callXml) => {
					call[callXml.tagName] = callXml.textContent;
				});
			calls.push(call);
		});
		AppConnector.request({
			module: 'AppComponents',
			action: 'Pbx',
			mode: 'saveCalls',
			calls: calls,
			calls: calls
		}).done(function (response) {});
	}
	eventCallHistory() {
		// console.log('eventCallHistory');
		// this.getStatus('callHistory', '<count>3</count><entryType>all</entryType>');
	}
	/**
	 * Get status from phone application
	 * @param {string} type
	 * @param {string} custom
	 */
	getStatus(type, custom) {
		if (!custom) {
			custom = '';
		}
		this.apiCall('status', `<status><type>${type}</type>${custom}</status>`);
	}
	/**
	 * Show phone app
	 */
	bringToFront() {
		this.apiCall('bringToFront', `<bringToFront><window>main</window></bringToFront>`);
	}
	/**
	 * Set status event
	 * @param {string} status
	 * @param {Event} e
	 */
	setStatus(status, e) {
		this.status = status;
		this.lastEvent = e;
		this.updateBtn();
		this.log('☎ %c' + status);
	}
	/**
	 * Send a request to the phone application
	 * @param {string} method
	 * @param {string} body
	 */
	apiCall(method, body) {
		this.log('→ ' + method, body);
		body = '<?xml version="1.0" encoding="utf-8" ?>\r\n' + body;
		let msg = 'GET /' + method + '\r\nUser-Agent: YetiForce CRM';
		msg += '\r\nContent-Type: application/xml\r\nContent-Length: ';
		msg += body.length + '\r\n\r\n' + body;
		this.websocket.send(msg);
	}
	/**
	 * Show console logs
	 * @param {string} message
	 * @param {string} body
	 */
	log(message, body) {
		if (CONFIG.debug) {
			if (body) {
				console.groupCollapsed(message);
				console.dirxml(body);
				console.groupEnd();
			} else {
				console.log(message, 'color: red;font-size: 1.2em; font-weight: bolder; ');
			}
		}
	}
	/**
	 * Parse response from websocket
	 * @param {string} msg
	 * @returns {object}
	 */
	parseMessage(msg) {
		let response = {},
			content = '';
		const lines = msg.replace(/\r\n/g, '\n').split('\n');
		let line = lines[0];
		if (line.substr(0, 4) == 'POST') {
			response['messageType'] = 'EVENT';
			line = line.substr(5).trim();
			response['eventType'] = line.substr(0, 13);
		} else if (line.substr(0, 8) == 'HTTP/1.1') {
			line = line.substr(8).trim();
			if (line.substr(0, 6) == '200 OK') {
				response['messageType'] = 'RESPONSE';
			} else if (line[0] == '4' || line[0] == '5') {
				response['messageType'] = 'ERROR';
				response['errorCode'] = line.substr(0, 3);
				response['errorText'] = line.substr(4);
			}
		}
		let i = 1;
		for (; i < lines.length; i++) {
			if (lines[i][0] == '<') {
				break;
			} else {
				continue;
			}
		}
		for (; i < lines.length; i++) {
			content += lines[i];
			if (i < lines.length - 1) {
				content += '\n';
			}
		}
		if (content.length > 0) {
			const parser = new DOMParser();
			response['xml'] = $(parser.parseFromString(content, 'text/xml'));
		}
		return response;
	}
};
