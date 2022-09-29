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
		this.btnText = this.accountName = this.lastEvent = this.status = this.connected = false;
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
		if (this.connected) {
			this.apiCall(
				'call',
				`<dial type="audio"><number>${data.phone}</number><displayName></displayName><suppressMainWindow>false</suppressMainWindow></dial>`
			);
		} else {
			app.showError({
				title: app.vtranslate('JS_UNEXPECTED_ERROR')
			});
		}
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
			this.getStatus('account', '<accountType>sip</accountType>');
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
		btn.removeClass('d-none');
		btn.attr('class', function (i, c) {
			return c.replace(/(^|\s)btn-\S+/g, '');
		});
		let title = this.status;
		switch (this.status) {
			case 'Ringing':
				btn.addClass('btn-primary');
				btnIcon.removeClass().addClass('fa-solid fa-phone-volume js-icon');
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
			case 'Connected':
			default:
				btn.addClass('btn-success');
				btnIcon.removeClass().addClass('fa-solid fa-phone-flip js-icon');
				break;
		}
		if (this.lastEvent && (this.lastEvent.code || this.lastEvent.name)) {
			title += ' (' + (this.lastEvent.code || this.lastEvent.name) + ')';
		}
		btn.attr('title', title);
		if (this.btnText) {
			btnText.text(this.btnText).addClass('ml-2');
		} else {
			btnText.removeClass('ml-2');
		}
	}
	/**
	 * Message event
	 * @param {object} data
	 */
	received(data) {
		const message = this.parseMessage(data);
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
		const type = xml.children(':first').attr('type'),
			fn = 'request' + type.charAt(0).toUpperCase() + type.slice(1);
		this.log('|◄| ' + fn + ' [' + (fn in this) + ']', xml.get(0));
		if (fn in this) {
			this[fn](xml.find(type));
		}
	}
	/**
	 * Event message type
	 * @param {jQuery} xml
	 */
	event(xml) {
		if (xml) {
			const type = xml.children(':first').attr('type'),
				fn = 'event' + type.charAt(0).toUpperCase() + type.slice(1);
			this.log('|◄| ' + fn + ' [' + (fn in this) + ']', xml.get(0));
			if (fn in this) {
				this[fn]();
			}
		}
	}
	/**
	 * Account details request
	 * @param {jQuery} xml
	 * @description `|◄| requestAccount [true]`
	 */
	requestAccount(xml) {
		this.btnText = this.accountName = xml.find('accountName').text();
		const value = app.cacheGet('PBX|lastCallHistoryUpdate|' + this.accountName, null);
		if (value == null || Math.floor((Date.now() - value) / 1000) > 300) {
			app.cacheClear('PBX|lastCallHistoryUpdate|' + this.accountName);
			this.getStatus('callHistory', '<count>20</count><entryType>all</entryType>');
		}
	}
	/**
	 * Call history details request
	 * @param {jQuery} xml
	 * @description `|◄| requestCallHistory [true]`
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
			calls: calls
		}).done((response) => {
			if (response.result.loadMore === true) {
				this.getStatus('callHistory', '<count>100</count><entryType>all</entryType>');
			}
		});
		app.cacheSet('PBX|lastCallHistoryUpdate|' + this.accountName, Date.now());
	}
	/**
	 * Call request
	 * @param {jQuery} xml
	 * @description `|◄| requestCall [true]`
	 */
	requestCall(xml) {
		if (xml.length) {
			this.setStatus('Ringing');
			this.btnText = xml.find('displayName').text();
		} else {
			this.setStatus('Connected');
			this.btnText = this.accountName;
		}
	}
	/**
	 * Call history event
	 * @description `|◄| eventCallHistory [true]`
	 */
	eventCallHistory() {
		this.getStatus('callHistory', '<count>5</count><entryType>all</entryType>');
	}
	/**
	 * Call event
	 * @description `|◄| eventCall [true]`
	 */
	eventCall() {
		this.setStatus('Ringing');
		this.getStatus('call');
	}
	/**
	 * Get status from phone application
	 * @param {string} type
	 * @param {string} custom
	 * @description `|►| status`
	 */
	getStatus(type, custom) {
		if (!custom) {
			custom = '';
		}
		this.apiCall('status', `<status><type>${type}</type>${custom}</status>`);
	}
	/**
	 * Show phone app
	 * @description `|►| bringToFront`
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
		this.log('|►| ' + method, body);
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
