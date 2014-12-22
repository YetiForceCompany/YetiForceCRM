/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @copyright (c) Sebastian Tschan
 * @license Modified MIT License
 * @link https://blueimp.net/ajax/
 * 
 * The SELFHTML documentation has been used throughout this project:
 * http://selfhtml.org
 * 
 * Stylesheet and cookie methods have been inspired by Paul Sowden (A List Apart):
 * http://www.alistapart.com/stories/alternate/
 */

// AJAX Chat client side logic:
var ajaxChat = {

	settingsInitiated: null,
	styleInitiated: null,
	initializeFunction: null,
	finalizeFunction: null,
	loginChannelID: null,
	loginChannelName: null,
	timerRate: null,
	timer: null,
	ajaxURL: null,
	baseURL: null,
	regExpMediaUrl: null,
	dirs: null,
	startChatOnLoad: null,
	chatStarted: null,
	domIDs: null,
	dom: null,
	settings: null,
	nonPersistentSettings: null,
	unusedSettings: null,
	bbCodeTags: null,
	colorCodes: null,
	emoticonCodes: null,
	emoticonFiles: null,
	soundFiles: null,
	sounds: null,
	soundTransform: null,
	sessionName: null,
	cookieExpiration: null,
	cookiePath: null,
	cookieDomain: null,
	cookieSecure: null,
	chatBotName: null,
	chatBotID: null,
	allowUserMessageDelete: null,
	inactiveTimeout: null,
	privateChannelDiff: null,
	privateMessageDiff: null,
	showChannelMessages: null,
	messageTextMaxLength: null,
	socketServerEnabled: null,
	socketServerHost: null,
	socketServerPort: null,
	socketServerChatID: null,
	socket: null,
	socketIsConnected: null,
	socketTimerRate: null,
	socketReconnectTimer: null,
	socketRegistrationID: null,
	userID: null,
	userName: null,
	userRole: null,
	channelID: null,
	channelName: null,
	channelSwitch: null,
	usersList: null,
	userNamesList: null,
	userMenuCounter: null,
	encodedUserName: null,
	userNodeString: null,
	ignoredUserNames: null,
	lastID: null,
	localID: null,
	lang: null,
	langCode: null,
	baseDirection: null,
	originalDocumentTitle: null,
	blinkInterval: null,
	httpRequest: null,
	retryTimer: null,
	retryTimerDelay: null,
	requestStatus: 'ok',
	DOMbuffering: null,
	DOMbuffer: null,
	DOMbufferRowClass: 'rowOdd',
	
	init: function(config, lang, initSettings, initStyle, initialize, initializeFunction, finalizeFunction) {	
		this.httpRequest		= {};
		this.usersList			= [];
		this.userNamesList		= [];
		this.userMenuCounter	= 0;
		this.lastID				= 0;
		this.localID			= 0;
		this.lang				= lang;		
		this.initConfig(config);
		this.initDirectories();		
		if(initSettings) {
			this.initSettings();
		}
		if(initStyle) {
			this.initStyle();
		}
		this.initializeFunction = initializeFunction;
		this.finalizeFunction = finalizeFunction;
		if(initialize) {
			this.setLoadHandler();
		}
	},
	
	initConfig: function(config) {
		this.loginChannelID			= config['loginChannelID'];
		this.loginChannelName		= config['loginChannelName'];
		this.timerRate				= config['timerRate'];
		this.ajaxURL				= config['ajaxURL'];
		this.baseURL				= config['baseURL'];
		this.regExpMediaUrl			= config['regExpMediaUrl'];
		this.startChatOnLoad		= config['startChatOnLoad'];
		this.domIDs					= config['domIDs'];
		this.settings				= config['settings'];
		this.nonPersistentSettings	= config['nonPersistentSettings'];
		this.bbCodeTags				= config['bbCodeTags'];
		this.colorCodes				= config['colorCodes'];
		this.emoticonCodes			= config['emoticonCodes'];
		this.emoticonFiles			= config['emoticonFiles'];
		this.soundFiles				= config['soundFiles'];
		this.sessionName			= config['sessionName'];
		this.cookieExpiration		= config['cookieExpiration'];
		this.cookiePath				= config['cookiePath'];
		this.cookieDomain			= config['cookieDomain'];
		this.cookieSecure			= config['cookieSecure'];
		this.chatBotName			= config['chatBotName'];
		this.chatBotID				= config['chatBotID'];
		this.allowUserMessageDelete	= config['allowUserMessageDelete'];
		this.inactiveTimeout		= config['inactiveTimeout'];
		this.privateChannelDiff		= config['privateChannelDiff'];
		this.privateMessageDiff		= config['privateMessageDiff'];
		this.showChannelMessages	= config['showChannelMessages'];
		this.messageTextMaxLength	= config['messageTextMaxLength'];
		this.socketServerEnabled	= config['socketServerEnabled'];
		this.socketServerHost		= config['socketServerHost'];
		this.socketServerPort		= config['socketServerPort'];
		this.socketServerChatID		= config['socketServerChatID'];
		this.DOMbuffering			= false;
		this.DOMbuffer				= "";
		this.retryTimerDelay 		= (this.inactiveTimeout*6000 - this.timerRate)/4 + this.timerRate;
	},

	initDirectories: function() {
		this.dirs = {};
		this.dirs['emoticons'] 	= this.baseURL+'img/emoticons/';
		this.dirs['sounds']		= this.baseURL+'sounds/';
		this.dirs['flash']		= this.baseURL+'flash/';
	},
	
	initSettings: function() {
		var cookie = this.readCookie(this.sessionName + '_settings'),
			i, settingsArray, setting, key, value, number;
		this.settingsInitiated = true;
		this.unusedSettings = {};
		if(cookie) {
			settingsArray = cookie.split('&');
			for(i=0; i<settingsArray.length; i++) {
				setting = settingsArray[i].split('=');
				if(setting.length === 2) {
					key = setting[0];
					value = this.decodeText(setting[1]);
					switch(value) {
						case 'true':
							value = true;
							break;
						case 'false':
							value = false;
							break;
						case 'null':
							value = null;
							break;
						default:
							number = parseFloat(value);
							if(!isNaN(number)) {
								if(parseInt(number) === number) {
									value = parseInt(number);
								} else {
									value = number;
								}
							}
					}
					if(this.inArray(this.nonPersistentSettings, key)) {
						// The setting is not used, store it for the persistSettings method:
						this.unusedSettings[key] = value;
					} else {
						this.settings[key] = value;
					}
				}
			}
		}
	},

	persistSettings: function() {
		var settingsArray;
		if(this.settingsInitiated) {
			settingsArray = [];
			for(var property in this.settings) {
				if(this.inArray(this.nonPersistentSettings, property)) {
					if(this.unusedSettings && this.unusedSettings[property]) {
						// Store the unusedSetting previously stored:
						this.settings[property] = this.unusedSettings[property];	
					} else {
						continue;
					}
				}
				settingsArray.push(property + '=' + this.encodeText(this.settings[property]));
			}
			this.createCookie(this.sessionName + '_settings', settingsArray.join('&'), this.cookieExpiration);	
		}
	},
	
	getSettings: function() {
		return this.settings;
	},
	
	getSetting: function(key) {
		// Only return null if setting is null or undefined, not if it is false:
		for(var property in this.settings) {
			if(property === key) {
				return this.settings[key];
			}
		}
		return null;
	},
	
	setSetting: function(key, value) {
		this.settings[key] = value;
	},
	
	initializeSettings: function() {
		if(this.settings['persistFontColor'] && this.settings['fontColor']) {
			// Set the inputField font color to the font color:
			if(this.dom['inputField']) {
				this.dom['inputField'].style.color = this.settings['fontColor'];
			}
		}
	},
	
	initialize: function() {	
		this.setUnloadHandler();
		this.initializeDocumentNodes();
		this.loadPageAttributes();
		this.initEmoticons();
		this.initColorCodes();
		this.initializeSettings();		
		this.setSelectedStyle();
		this.customInitialize();
		//preload the Alert icon (it can't display if there's no connection unless it's cached!)
		this.setStatus('retrying');
		if(typeof this.initializeFunction === 'function') {
			this.initializeFunction();
		}
		if(!this.isCookieEnabled()) {
			this.addChatBotMessageToChatList('/error CookiesRequired');
		} else {
			if(this.startChatOnLoad) {
				this.startChat();
			} else {
				this.setStartChatHandler();
				this.requestTeaserContent();
			}
		}
	},

	requestTeaserContent: function() {
		var params = '&view=teaser';
		params += '&getInfos=' + this.encodeText('userID,userName,userRole');
		if(!isNaN(parseInt(this.loginChannelID))) {
			params += '&channelID='+this.loginChannelID;
		} else if(this.loginChannelName !== null) {
			params += '&channelName='+this.encodeText(this.loginChannelName);
		}
		this.updateChat(params);
	},
	
	setStartChatHandler: function() {
		if(this.dom['inputField']) {
			this.dom['inputField'].onfocus = function() {
				ajaxChat.startChat();
				// Reset the onfocus event on first call:
				ajaxChat.dom['inputField'].onfocus = '';
			};		
		}
	},
	
	startChat: function() {
		this.chatStarted = true;
		if(this.dom['inputField'] && this.settings['autoFocus']) {
			this.dom['inputField'].focus();
		}
		this.loadFlashInterface();
		this.startChatUpdate();
	},

	loadPageAttributes: function() {
		var htmlTag			= document.getElementsByTagName('html')[0];
		this.langCode		= htmlTag.getAttribute('lang')	? htmlTag.getAttribute('lang')	: 'en';
		this.baseDirection	= htmlTag.getAttribute('dir')	? htmlTag.getAttribute('dir')	: 'ltr';		
	},

	setLoadHandler: function() {
		// Make sure initialize() is called on page load:
  		var onload = window.onload;
		if(typeof onload !== 'function') {
			window.onload = function() {
				ajaxChat.initialize();
			};
		} else {
			window.onload = function() {
				onload();
				ajaxChat.initialize();
			};
		}		
	},
	
	setUnloadHandler: function() {
		// Make sure finalize() is called on page unload:
  		var onunload = window.onunload;
		if(typeof onunload !== 'function') {
			window.onunload = function() {
				ajaxChat.finalize();
			};
		} else {
			window.onunload = function() {
				ajaxChat.finalize();
				onunload();
			};
		}
	},

	updateDOM: function(id, str, prepend, overwrite) {
		var domNode = this.dom[id] ? this.dom[id] : document.getElementById(id);
		if(!domNode) {
			return;
		}
		try {
			// Test for validity before adding the string to the DOM:
			domNode.cloneNode(false).innerHTML = str;
			if(overwrite) {
				domNode.innerHTML = str;
			} else if(prepend) {
				domNode.innerHTML = str + domNode.innerHTML;
			} else {
				domNode.innerHTML += str;
			}
		} catch(e) {
			this.addChatBotMessageToChatList('/error DOMSyntax '+id);
			this.updateChatlistView();
		}
	},
	
	initializeDocumentNodes: function() {
		this.dom = {};
		for(var key in this.domIDs) {
			this.dom[key] = document.getElementById(this.domIDs[key]);
		}
	},

	initEmoticons: function() {
		this.DOMbuffer = "";
		for(var i=0; i<this.emoticonCodes.length; i++) {
			// Replace specials characters in emoticon codes:
			this.emoticonCodes[i] = this.encodeSpecialChars(this.emoticonCodes[i]);
			this.DOMbuffer = this.DOMbuffer
						+ '<a href="javascript:ajaxChat.insertText(\''
						+ this.scriptLinkEncode(this.emoticonCodes[i])
						+ '\');"><img src="'
						+ this.dirs['emoticons']
						+ this.emoticonFiles[i]
						+ '" alt="'
						+ this.emoticonCodes[i]
						+ '" title="'
						+ this.emoticonCodes[i]
						+ '"/></a>';
			}
		if(this.dom['emoticonsContainer']) {
 			this.updateDOM('emoticonsContainer', this.DOMbuffer);
 		}
 		this.DOMbuffer = "";
	},
	
	initColorCodes: function() {
		if(this.dom['colorCodesContainer']) {
			this.DOMbuffer = "";
			for(var i=0; i<this.colorCodes.length; i++) {
				this.DOMbuffer = this.DOMbuffer
					+ '<a href="javascript:ajaxChat.setFontColor(\''
					+ this.colorCodes[i]
					+ '\');" style="background-color:'
					+ this.colorCodes[i]
					+ ';" title="'
					+ this.colorCodes[i]
					+ '"></a>'
					+ "\n";
			}
			this.updateDOM('colorCodesContainer', this.DOMbuffer);
 			this.DOMbuffer = "";
		}
	},


	startChatUpdate: function() {
		// Start the chat update and retrieve current user and channel info and set the login channel:
		var infos = 'userID,userName,userRole,channelID,channelName';
		if(this.socketServerEnabled) {
			infos += ',socketRegistrationID';
		}
		var params = '&getInfos=' + this.encodeText(infos);
		if(!isNaN(parseInt(this.loginChannelID))) {
			params += '&channelID='+this.loginChannelID;
		} else if(this.loginChannelName !== null) {
			params += '&channelName='+this.encodeText(this.loginChannelName);
		}
		this.updateChat(params);
	},
	
	updateChat: function(paramString) {
		var requestUrl = this.ajaxURL
						+ '&lastID='
						+ this.lastID;
		if(paramString) {
			requestUrl += paramString;
		}
		this.makeRequest(requestUrl,'GET',null);
	},
	
	loadFlashInterface: function() {
		if(this.dom['flashInterfaceContainer']) {
			this.updateDOM(
				'flashInterfaceContainer',
				'<object id="ajaxChatFlashInterface" style="position:absolute; left:-100px;" '
				+'classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" '
				+'codebase="'
				+ window.location.protocol
				+'//download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" '
				+'height="1" width="1">'
				+'<param name="flashvars" value="bridgeName=ajaxChat"/>'
				+'<param name="src" value="'+this.dirs['flash']+'FABridge.swf"/>'
				+'<embed name="ajaxChatFlashInterface" type="application/x-shockwave-flash" pluginspage="'
				+ window.location.protocol
				+'//www.macromedia.com/go/getflashplayer" '
				+'src="'+this.dirs['flash']+'FABridge.swf" height="1" width="1" flashvars="bridgeName=ajaxChat"/>'
				+'</object>'
			);
			FABridge.addInitializationCallback('ajaxChat', this.flashInterfaceLoadCompleteHandler);
		}
	},
	
	flashInterfaceLoadCompleteHandler: function() {
		ajaxChat.initializeFlashInterface();
	},

	initializeFlashInterface: function() {
		if(this.socketServerEnabled) {
			this.socketTimerRate = (this.inactiveTimeout-1)*60*1000;
			this.socketConnect();
		}
		this.loadSounds();
		this.initializeCustomFlashInterface();
	},

	socketConnect: function() {
		if(!this.socketIsConnected) {
			try {
				if(!this.socket && FABridge.ajaxChat) {
					this.socket = FABridge.ajaxChat.create('flash.net.XMLSocket');
					this.socket.addEventListener('connect', this.socketConnectHandler);
					this.socket.addEventListener('close', this.socketCloseHandler);
					this.socket.addEventListener('data', this.socketDataHandler);
					this.socket.addEventListener('ioError', this.socketIOErrorHandler);
					this.socket.addEventListener('securityError', this.socketSecurityErrorHandler);
				}
				this.socket.connect(this.socketServerHost, this.socketServerPort);
			} catch(e) {
				//alert(e);
			}
		}
		clearTimeout(this.socketReconnectTimer);
		this.socketReconnectTimer = null;
	},
	
	socketConnectHandler: function(event) {
		ajaxChat.socketIsConnected = true;
		// setTimeout is needed to avoid calling the flash interface recursively:
		setTimeout(ajaxChat.socketRegister, 0);
	},

	socketCloseHandler: function(event) {
		ajaxChat.socketIsConnected = false;
		if(ajaxChat.socket) {
			clearTimeout(ajaxChat.timer);
			ajaxChat.updateChat(null);
		}
	},
	
	socketDataHandler: function(event) {
		ajaxChat.socketUpdate(event.getData());
	},

	socketIOErrorHandler: function(event) {
		// setTimeout is needed to avoid calling the flash interface recursively (e.g. sound on new messages):
		setTimeout(function() { ajaxChat.addChatBotMessageToChatList('/error SocketIO'); }, 0);
		setTimeout(ajaxChat.updateChatlistView, 1);
	},

	socketSecurityErrorHandler: function(event) {
		// setTimeout is needed to avoid calling the flash interface recursively (e.g. sound on new messages):
		setTimeout(function() { ajaxChat.addChatBotMessageToChatList('/error SocketSecurity'); }, 0);
		setTimeout(ajaxChat.updateChatlistView, 1);
	},

	socketRegister: function() {
		if(this.socket && this.socketIsConnected) {
			try {
				this.socket.send(
					'<register chatID="'
					+this.socketServerChatID
					+'" userID="'
					+this.userID
					+'" regID="'
					+this.socketRegistrationID
					+'"/>'
				);
			} catch(e) {
				//alert(e);
			}
		}
	},
	
	loadXML: function(str) {
		if(!arguments.callee.parser) {
			try {
				// DOMParser native implementation (Mozilla, Opera):
				arguments.callee.parser = new DOMParser();
			} catch(e) {
				var customDOMParser = function() {};
				if(navigator.appName === 'Microsoft Internet Explorer') {
					// IE implementation:
					customDOMParser.prototype.parseFromString = function(str, contentType) {
						if(!arguments.callee.XMLDOM) {
							arguments.callee.XMLDOM = new ActiveXObject('Microsoft.XMLDOM');
						}
						arguments.callee.XMLDOM.loadXML(str);
						return arguments.callee.XMLDOM;	
					};
				} else {
					// Safari, Konqueror:
					customDOMParser.prototype.parseFromString = function(str, contentType) {
						if(!arguments.callee.httpRequest) {
							arguments.callee.httpRequest = new XMLHttpRequest();
						}
						arguments.callee.httpRequest.open(
							'GET',
							'data:text/xml;charset=utf-8,'+encodeURIComponent(str),
							false
						);
						arguments.callee.httpRequest.send(null);
						return arguments.callee.httpRequest.responseXML;
					};
				}
				arguments.callee.parser = new customDOMParser();
			}
		}
		return arguments.callee.parser.parseFromString(str, 'text/xml');
	},
	
	socketUpdate: function(data) {
		var xmlDoc = this.loadXML(data);
		if(xmlDoc) {
			this.handleOnlineUsers(xmlDoc.getElementsByTagName('user'));
			// If the root node has the attribute "mode" set to "1" it is a channel message:
			if((this.showChannelMessages || xmlDoc.firstChild.getAttribute('mode') !== '1') && !this.channelSwitch) {
				var channelID = xmlDoc.firstChild.getAttribute('channelID');
				if(channelID === this.channelID ||
					parseInt(channelID) === parseInt(this.userID)+this.privateMessageDiff
					) {
					this.handleChatMessages(xmlDoc.getElementsByTagName('message'));
				}
			}
		}
	},

	setAudioVolume: function(volume) {
		volume = parseFloat(volume);
		if(!isNaN(volume)) {
			if(volume < 0) {
				volume = 0.0;
			} else if(volume > 1) {
				volume = 1.0;
			}
			this.settings['audioVolume'] = volume;
			try {
				if(!this.soundTransform) {
					this.soundTransform = FABridge.ajaxChat.create('flash.media.SoundTransform');					
				}
				this.soundTransform.setVolume(volume);
			} catch(e) {
				//alert(e);
			}
		}
	},
	
	loadSounds: function() {
		try {
			this.setAudioVolume(this.settings['audioVolume']);
			this.sounds = {};
			var sound,urlRequest;
			for(var key in this.soundFiles) {
				sound = FABridge.ajaxChat.create('flash.media.Sound');
				sound.addEventListener('complete', this.soundLoadCompleteHandler);
				sound.addEventListener('ioError', this.soundIOErrorHandler);
				urlRequest = FABridge.ajaxChat.create('flash.net.URLRequest');
				urlRequest.setUrl(this.dirs['sounds']+this.soundFiles[key]);
				sound.load(urlRequest);
			}
		} catch(e) {
			alert(e);
		}
	},
	
	soundLoadCompleteHandler: function(event) {
		var sound = event.getTarget();
		for(var key in ajaxChat.soundFiles) {
			// Get the sound key by matching the sound URL with the sound filename:
			if((new RegExp(ajaxChat.soundFiles[key])).test(sound.getUrl())) {
				// Add the loaded sound to the sounds list:
				ajaxChat.sounds[key] = sound;
			}
		}
	},

	soundIOErrorHandler: function(event) {
		// setTimeout is needed to avoid calling the flash interface recursively (e.g. sound on new messages):
		setTimeout(function() { ajaxChat.addChatBotMessageToChatList('/error SoundIO'); }, 0);
		setTimeout(ajaxChat.updateChatlistView, 1);
	},
	
	soundPlayCompleteHandler: function(event) {
		// soundChannel event 'soundComplete'
	},

	playSound: function(soundID) {
		if(this.sounds && this.sounds[soundID]) {
			try {
				// play() parameters are
				// startTime:Number (default = 0),
				// loops:int (default = 0) and
				// sndTransform:SoundTransform  (default = null)
				return this.sounds[soundID].play(0, 0, this.soundTransform);
			} catch(e) {
				//alert(e);
			}
		}
		return null;
	},
	
	playSoundOnNewMessage: function(dateObject, userID, userName, userRole, messageID, messageText, channelID, ip) {
		if(this.settings['audio'] && this.sounds && this.lastID && !this.channelSwitch) {
			switch(userID) {
				case this.chatBotID:
					var messageParts = messageText.split(' ', 1);
					switch(messageParts[0]) {
						case '/login':
						case '/channelEnter':
							this.playSound(this.settings['soundEnter']);
							break;
						case '/logout':
						case '/channelLeave':
						case '/kick':
							this.playSound(this.settings['soundLeave']);
							break;
						case '/error':
							this.playSound(this.settings['soundError']);
							break;
						default:
							this.playSound(this.settings['soundChatBot']);
					}
					break;
				case this.userID:
					this.playSound(this.settings['soundSend']);
					break;
				default:
					this.playSound(this.settings['soundReceive']);
					break;
			}
		}
	},

	fillSoundSelection: function(selectionID, selectedSound) {
		var selection = document.getElementById(selectionID);
		// Skip the first, empty selection:
		var i = 1;
		for(var key in this.soundFiles) {
			selection.options[i] = new Option(key, key);
			if(key === selectedSound){
				selection.options[i].selected = true;
			}
			i++;
		}
	},

	setStatus: function(newStatus) {
		// status options are: ok, retrying, waiting
		if (this.requestStatus !== 'retrying' || newStatus === 'ok') {
			this.requestStatus = newStatus;
		}
		
		var statusIcon = document.getElementById('statusIconContainer');

		if (statusIcon) {
			switch (this.requestStatus) {
				case 'ok':
					this.setClass(statusIcon, 'statusContainerOff');
					break;
				case 'waiting':
					this.setClass(statusIcon, 'statusContainerOn');
					break;
				case 'retrying':
					this.setClass(statusIcon, 'statusContainerAlert');
					break;
			}
		}
	},
	
	forceNewRequest: function() {
		ajaxChat.updateChat(null); 
		ajaxChat.setStatus('retrying');
	},
	
	getHttpRequest: function(identifier) {
		if(!this.httpRequest[identifier]) {
			if (window.XMLHttpRequest) {
				this.httpRequest[identifier] = new XMLHttpRequest();
				if (this.httpRequest[identifier].overrideMimeType) {
					this.httpRequest[identifier].overrideMimeType('text/xml');
				}
			} else if (window.ActiveXObject) {
				try {
					this.httpRequest[identifier] = new ActiveXObject("Msxml2.XMLHTTP");
				} catch (e) {
					try {
						this.httpRequest[identifier] = new ActiveXObject("Microsoft.XMLHTTP");
					} catch (e) {
					}
				}
			}
		}
		return this.httpRequest[identifier];
	},

	makeRequest: function(url, method, data) {
		var identifier;
		this.setStatus('waiting');
		
		try {
			if(data) {
				// Create up to 50 HTTPRequest objects:
				if(!arguments.callee.identifier || arguments.callee.identifier > 50) {
					arguments.callee.identifier = 1;
				} else {
					arguments.callee.identifier++;
				}
				identifier = arguments.callee.identifier;
			} else {
				identifier = 0;
			}
			//if the response takes longer than retryTimerDelay to give an OK status, abort the connection and start again.
			this.retryTimer = setTimeout(ajaxChat.forceNewRequest, ajaxChat.retryTimerDelay);
			
			this.getHttpRequest(identifier).open(method, url, true);
			this.getHttpRequest(identifier).onreadystatechange = function() {
				try {
					ajaxChat.handleResponse(identifier);
				} catch(e) {
					try {
						clearTimeout(ajaxChat.timer);
					} catch(e) {
						//alert(e);
					}
					try {
						if(data) {
							ajaxChat.addChatBotMessageToChatList('/error ConnectionTimeout');
							ajaxChat.setStatus('retrying');
							ajaxChat.updateChatlistView();
						}
					} catch(e) {
						//alert(e);
					}
					try {				
						ajaxChat.timer = setTimeout(function() { ajaxChat.updateChat(null); }, ajaxChat.timerRate);
					} catch(e) {
						//alert(e);
					}
				}
			};
			if(method === 'POST') {
				this.getHttpRequest(identifier).setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			}
			this.getHttpRequest(identifier).send(data);
		} catch(e) {
			clearTimeout(this.timer);
			if(data) {
				this.addChatBotMessageToChatList('/error ConnectionTimeout');
				ajaxChat.setStatus('retrying');
				this.updateChatlistView();
			}
			this.timer = setTimeout(function() { ajaxChat.updateChat(null); }, this.timerRate);
		}
	},
		
	handleResponse: function(identifier) {
		var xmlDoc;
		if (this.getHttpRequest(identifier).readyState === 4) {
			if (this.getHttpRequest(identifier).status === 200) {
				clearTimeout(ajaxChat.retryTimer);
				xmlDoc = this.getHttpRequest(identifier).responseXML;
				ajaxChat.setStatus('ok');
			} else {
				// Connection status 0 can be ignored.
				if (this.getHttpRequest(identifier).status === 0) {
					this.setStatus('waiting');
					this.updateChatlistView();
					return false;
				} else {
					this.addChatBotMessageToChatList('/error ConnectionStatus '+this.getHttpRequest(identifier).status);
					this.setStatus('retrying');
					this.updateChatlistView();				
					return false;
				}
			}
		}
		if(!xmlDoc) {
			return false;
		}
		this.handleXML(xmlDoc);
		return true;
	},
	
	handleXML: function(xmlDoc) {
		this.handleInfoMessages(xmlDoc.getElementsByTagName('info'));
		this.handleOnlineUsers(xmlDoc.getElementsByTagName('user'));
		this.handleChatMessages(xmlDoc.getElementsByTagName('message'));
		this.channelSwitch = null;
		this.setChatUpdateTimer();
	},

	setChatUpdateTimer: function() {
		clearTimeout(this.timer);
		if(this.chatStarted) {
			var timeout;
			if(this.socketIsConnected) {
				timeout = this.socketTimerRate;
			} else {
				timeout = this.timerRate;
				if(this.socketServerEnabled && !this.socketReconnectTimer) {
					// If the socket connection fails try to reconnect once in a minute:
					this.socketReconnectTimer = setTimeout(ajaxChat.socketConnect, 60000);
				}
			}
			this.timer = setTimeout(function() {ajaxChat.updateChat(null);}, timeout);			
		}
	},
	
	handleInfoMessages: function(infoNodes) {
		var infoType, infoData;
		for(var i=0; i<infoNodes.length; i++) {
			infoType = infoNodes[i].getAttribute('type');
			infoData = infoNodes[i].firstChild ? infoNodes[i].firstChild.nodeValue : '';
			this.handleInfoMessage(infoType, infoData);
		}
	},
	
	handleInfoMessage: function(infoType, infoData) {
		switch(infoType) {
			case 'channelSwitch':
				this.clearChatList();
				this.clearOnlineUsersList();
				this.setSelectedChannel(infoData);
				this.channelName = infoData;
				this.channelSwitch = true;
				break;			
			case 'channelName':
				this.setSelectedChannel(infoData);
				this.channelName = infoData;
				break;
			case 'channelID':
				this.channelID = infoData;
				break;
			case 'userID':
				this.userID = infoData;
				break;			
			case 'userName':
				this.userName = infoData;
				this.encodedUserName = this.scriptLinkEncode(this.userName);
				this.userNodeString = null;
				break;
			case 'userRole':
				this.userRole = infoData;
				break;				
			case 'logout':
				this.handleLogout(infoData);
				return;
			case 'socketRegistrationID':
				this.socketRegistrationID = infoData;
				this.socketRegister();
			default:
				this.handleCustomInfoMessage(infoType, infoData);
		}
	},

	handleOnlineUsers: function(userNodes) {
		if(userNodes.length) {
			var index,userID,userName,userRole,i,
				onlineUsers = [];
			for(i=0; i<userNodes.length; i++) {
				userID = userNodes[i].getAttribute('userID');
				userName = userNodes[i].firstChild ? userNodes[i].firstChild.nodeValue : '';
				userRole = userNodes[i].getAttribute('userRole');
				onlineUsers.push(userID);
				index = this.arraySearch(userID, this.usersList);
				if(index === false) {
					this.addUserToOnlineList(
						userID,
						userName,
						userRole
					);
				} else if(this.userNamesList[index] !== userName) {
					this.removeUserFromOnlineList(userID, index);
					this.addUserToOnlineList(
						userID,
						userName,
						userRole
					);
				}
			}
			// Clear the offline users from the online users list:
			for(i=0; i<this.usersList.length; i++) {
				if(!this.inArray(onlineUsers, this.usersList[i])) {
					this.removeUserFromOnlineList(this.usersList[i], i);
				}
			}	
			this.setOnlineListRowClasses();		
		}	
	},

	handleChatMessages: function(messageNodes) {
		var userNode,userName,textNode,messageText,i;
		if(messageNodes.length) {
			for(i=0; i<messageNodes.length; i++) {
				this.DOMbuffering = true;
				userNode = messageNodes[i].getElementsByTagName('username')[0];
				userName = userNode.firstChild ? userNode.firstChild.nodeValue : '';
				textNode = messageNodes[i].getElementsByTagName('text')[0];
				messageText = textNode.firstChild ? textNode.firstChild.nodeValue : '';
				if (i === (messageNodes.length - 1)) {this.DOMbuffering = false;}
				this.addMessageToChatList(
						new Date(messageNodes[i].getAttribute('dateTime')),
						messageNodes[i].getAttribute('userID'),
						userName,
						messageNodes[i].getAttribute('userRole'),
						messageNodes[i].getAttribute('id'),
						messageText,
						messageNodes[i].getAttribute('channelID'),
						messageNodes[i].getAttribute('ip')
				);
			}
			this.DOMbuffering = false;
			this.updateChatlistView();
			this.lastID = messageNodes[messageNodes.length-1].getAttribute('id');
		}
	},
	
	setSelectedChannel: function(channel) {
		var channelSelected = false,
			i,option,text;
		if(this.dom['channelSelection']) {
			// Replace the entities in the channel name with their character equivalent:
			channel = this.decodeSpecialChars(channel);
			for(i=0; i<this.dom['channelSelection'].options.length; i++) {
				if(this.dom['channelSelection'].options[i].value === channel) {
					this.dom['channelSelection'].options[i].selected = true;
					channelSelected = true;
					break;
				}
			}
			// The given channel is not in the list, add it:
			if(!channelSelected) {
				option = document.createElement('option');
				text = document.createTextNode(channel);
				option.appendChild(text);
				option.setAttribute('value', channel);
				option.setAttribute('selected', 'selected');			
				this.dom['channelSelection'].appendChild(option);
			}
		}
	},

	removeUserFromOnlineList: function(userID, index) {
		this.usersList.splice(index, 1);
		this.userNamesList.splice(index, 1);		
		if(this.dom['onlineList']) {
			this.dom['onlineList'].removeChild(this.getUserNode(userID));
		}
	},
		
	addUserToOnlineList: function(userID, userName, userRole) {
		this.usersList.push(userID);
		this.userNamesList.push(userName);	
		if(this.dom['onlineList']) {
			this.updateDOM(
				'onlineList',
				this.getUserNodeString(userID, userName, userRole),
				(this.userID === userID)
			);
		}
	},

	getUserNodeString: function(userID, userName, userRole) {
		var encodedUserName, str;
		if(this.userNodeString && userID === this.userID) {
			return this.userNodeString;
		} else {
			encodedUserName = this.scriptLinkEncode(userName);
			str	= '<div id="'
					+ this.getUserDocumentID(userID)
					+ '"><a href="javascript:ajaxChat.toggleUserMenu(\''
					+ this.getUserMenuDocumentID(userID)
					+ '\', \''
					+ encodedUserName
					+ '\', '
					+ userID
					+ ');" class="'
					+ this.getRoleClass(userRole)
					+ '" title="'
					+ this.lang['toggleUserMenu'].replace(/%s/, userName)
					+ '">'
					+ userName
					+ '</a>'
					+ '<ul class="userMenu" id="'
					+ this.getUserMenuDocumentID(userID)
					+ '" style="display:none;">'
					+ '</ul>'
					+'</div>';
			if(userID === this.userID) {
				this.userNodeString = str;
			}
			return str;	
		}
	},

	toggleUserMenu: function(menuID, userName, userID) {
		// If the menu is empty, fill it with user node menu items before toggling it. 
		var isInline = false;
		if (menuID.indexOf('ium') >= 0 ) {
			isInline = true;
		}
		if(!document.getElementById(menuID).firstChild) {
			this.updateDOM(
				menuID,
				this.getUserNodeStringItems(
					this.encodeText(this.addSlashes(this.getScriptLinkValue(userName))),
					userID,
					isInline
				),
				false,
				true
			)
		}
		this.showHide(menuID);
		this.dom['chatList'].scrollTop = this.dom['chatList'].scrollHeight;
	},
	
	getUserNodeStringItems: function(encodedUserName, userID, isInline) {
		var menu;
		if(encodedUserName !== this.encodedUserName) {
			menu 	= '<li><a href="javascript:ajaxChat.insertMessageWrapper(\'/msg '
					+ encodedUserName
					+ ' \');">'
					+ this.lang['userMenuSendPrivateMessage']
					+ '</a></li>'
					+ '<li><a href="javascript:ajaxChat.insertMessageWrapper(\'/describe '
					+ encodedUserName
					+ ' \');">'
					+ this.lang['userMenuDescribe']
					+ '</a></li>'
					+ '<li><a href="javascript:ajaxChat.sendMessageWrapper(\'/query '
					+ encodedUserName
					+ '\');">'
					+ this.lang['userMenuOpenPrivateChannel']
					+ '</a></li>'
					+ '<li><a href="javascript:ajaxChat.sendMessageWrapper(\'/query\');">'
					+ this.lang['userMenuClosePrivateChannel']
					+ '</a></li>'
					+ '<li><a href="javascript:ajaxChat.sendMessageWrapper(\'/ignore '
					+ encodedUserName
					+ '\');">'
					+ this.lang['userMenuIgnore']
					+ '</a></li>';
			if (isInline) {
				menu	+= '<li><a href="javascript:ajaxChat.sendMessageWrapper(\'/invite '
					+ encodedUserName
					+ '\');">'
					+ this.lang['userMenuInvite']
					+ '</a></li>'
					+ '<li><a href="javascript:ajaxChat.sendMessageWrapper(\'/uninvite '
					+ encodedUserName
					+ '\');">'
					+ this.lang['userMenuUninvite']
					+ '</a></li>'
					+ '<li><a href="javascript:ajaxChat.sendMessageWrapper(\'/whereis '
					+ encodedUserName
					+ '\');">'
					+ this.lang['userMenuWhereis']
					+ '</a></li>';
			}
			if(this.userRole === '2' || this.userRole === '3') {
				menu	+= '<li><a href="javascript:ajaxChat.insertMessageWrapper(\'/kick '
						+ encodedUserName
						+ ' \');">'
						+ this.lang['userMenuKick']
						+ '</a></li>'
						+ '<li><a href="javascript:ajaxChat.sendMessageWrapper(\'/whois '
						+ encodedUserName
						+ '\');">'
						+ this.lang['userMenuWhois']
						+ '</a></li>';
			}
		} else {
			menu 	= '<li><a href="javascript:ajaxChat.sendMessageWrapper(\'/who\');">'
					+ this.lang['userMenuWho']
					+ '</a></li>'
			if(this.userRole === '1' || this.userRole === '2' || this.userRole === '3') {
				menu	+= '<li><a href="javascript:ajaxChat.sendMessageWrapper(\'/join\');">'
						+ this.lang['userMenuEnterPrivateRoom']
						+ '</a></li>';
				if(this.userRole === '2' || this.userRole === '3') {
					menu	+= '<li><a href="javascript:ajaxChat.sendMessageWrapper(\'/bans\');">'
							+ this.lang['userMenuBans']
							+ '</a></li>';
				}
			}
		}
		menu += this.getCustomUserMenuItems(encodedUserName, userID);
		return menu;
	},
	
	setOnlineListRowClasses: function() {
		if(this.dom['onlineList']) {
			var node = this.dom['onlineList'].firstChild;			
			var rowEven = false;
			while(node) {
				this.setClass(node, (rowEven ? 'rowEven' : 'rowOdd'));
				node = node.nextSibling;
				rowEven = !rowEven;
			}
		}
	},
	
	clearChatList: function() {
		while(this.dom['chatList'].hasChildNodes()) {
			this.dom['chatList'].removeChild(this.dom['chatList'].firstChild);
		}
	},

	clearOnlineUsersList: function() {
		this.usersList = [];
		this.userNamesList = [];
		if(this.dom['onlineList']) {
			while(this.dom['onlineList'].hasChildNodes()) {
				this.dom['onlineList'].removeChild(this.dom['onlineList'].firstChild);
			}
		}
	},

	getEncodedChatBotName: function() {
		if(typeof arguments.callee.encodedChatBotName === 'undefined') {
			arguments.callee.encodedChatBotName = this.encodeSpecialChars(this.chatBotName);
		}
		return arguments.callee.encodedChatBotName;
	},
	
	addChatBotMessageToChatList: function(messageText) {
		this.addMessageToChatList(
			new Date(),
			this.chatBotID,
			this.getEncodedChatBotName(),
			4,
			null,
			messageText,
			null
		);
	},
	
	addMessageToChatList: function(dateObject, userID, userName, userRole, messageID, messageText, channelID, ip) {
		// Prevent adding the same message twice:
		if(this.getMessageNode(messageID)) {
			return;
		}		
		if(!this.onNewMessage(dateObject, userID, userName, userRole, messageID, messageText, channelID, ip)) {
			return;
		}
		this.DOMbufferRowClass = this.DOMbufferRowClass === 'rowEven' ? 'rowOdd' : 'rowEven';
		this.DOMbuffer = this.DOMbuffer + 
			this.getChatListMessageString(
				dateObject, userID, userName, userRole, messageID, messageText, channelID, ip
			);
		if(!this.DOMbuffering){
 			this.updateDOM('chatList', this.DOMbuffer);
 			this.DOMbuffer = "";
 		}
	},

	getChatListMessageString: function(dateObject, userID, userName, userRole, messageID, messageText, channelID, ip) {
		var rowClass = this.DOMbufferRowClass;
		var userClass = this.getRoleClass(userRole);
		var colon;
		if(messageText.indexOf('/action') === 0 || messageText.indexOf('/me') === 0 || messageText.indexOf('/privaction') === 0) {
			userClass += ' action';
			colon = ' ';
		} else {
			colon = ': ';
		}
		var dateTime = this.settings['dateFormat'] ? '<span class="dateTime">'
						+ this.formatDate(this.settings['dateFormat'], dateObject) + '</span> ' : '';
		return	'<div id="'
				+ this.getMessageDocumentID(messageID)
				+ '" class="'
				+ rowClass
				+ '">'
				+ this.getDeletionLink(messageID, userID, userRole, channelID)
				+ dateTime
				+ '<span class="'
				+ userClass
				+ '"'
				+ this.getChatListUserNameTitle(userID, userName, userRole, ip)
				+ ' dir="'
				+ this.baseDirection
				+ '" onclick="ajaxChat.insertText(this.firstChild.nodeValue);">'
				+ userName
				+ '</span>'
				+ colon
				+ this.replaceText(messageText)
				+ '</div>';
	},
	
	getChatListUserNameTitle: function(userID, userName, userRole, ip) {
		return (ip !== null) ? ' title="IP: ' + ip + '"' : '';		
	},
	
	getMessageDocumentID: function(messageID) {
		return ((messageID === null) ? 'ajaxChat_lm_'+(this.localID++) : 'ajaxChat_m_'+messageID);
	},
	
	getMessageNode: function(messageID) {
		return ((messageID === null) ? null : document.getElementById(this.getMessageDocumentID(messageID)));
	},
	
	getUserDocumentID: function(userID) {
		return 'ajaxChat_u_'+userID;
	},
	
	getUserNode: function(userID) {
		return document.getElementById(this.getUserDocumentID(userID));
	},

	getUserMenuDocumentID: function(userID) {
		return 'ajaxChat_um_'+userID;
	},
	
	getInlineUserMenuDocumentID: function(menuID, index) {
		return 'ajaxChat_ium_'+menuID+'_'+index;
	},
	
	getDeletionLink: function(messageID, userID, userRole, channelID) {
		if(messageID !== null && this.isAllowedToDeleteMessage(messageID, userID, userRole, channelID)) {
			if(!arguments.callee.deleteMessage) {
				arguments.callee.deleteMessage = this.encodeSpecialChars(this.lang['deleteMessage']);
			}
			return	'<a class="delete" title="'
					+ arguments.callee.deleteMessage
					+ '" href="javascript:ajaxChat.deleteMessage('
					+ messageID
					+ ');"> </a>'; // Adding a space - without any content Opera messes up the chatlist display
		}
		return '';
	},
	
	isAllowedToDeleteMessage: function(messageID, userID, userRole, channelID) {
		if((((this.userRole === '1' && this.allowUserMessageDelete && (userID === this.userID ||
			parseInt(channelID) === parseInt(this.userID)+this.privateMessageDiff ||
			parseInt(channelID) === parseInt(this.userID)+this.privateChannelDiff)) ||
			this.userRole === '2') && userRole !== '3' && userRole !== '4') || this.userRole === '3') {
			return true;
		}
		return false;
	},
	
	onNewMessage: function(dateObject, userID, userName, userRole, messageID, messageText, channelID, ip) {
		if(!this.customOnNewMessage(dateObject, userID, userName, userRole, messageID, messageText, channelID, ip)) {
			return false;
		}
		if(this.ignoreMessage(dateObject, userID, userName, userRole, messageID, messageText, channelID, ip)) {
			return false;
		}
		if(this.parseDeleteMessageCommand(messageText)) {
			return false;
		}
		this.blinkOnNewMessage(dateObject, userID, userName, userRole, messageID, messageText, channelID, ip);
		this.playSoundOnNewMessage(dateObject, userID, userName, userRole, messageID, messageText, channelID, ip);
		return true;
	},

	parseDeleteMessageCommand: function(messageText) {
		if(messageText.indexOf('/delete') === 0) {
			var messageID = messageText.substr(8);
			var messageNode = this.getMessageNode(messageID);
			if(messageNode) {
				var nextSibling = messageNode.nextSibling;
				try {
					this.dom['chatList'].removeChild(messageNode);
					if(nextSibling) {
						this.updateChatListRowClasses(nextSibling);
					}
				} catch(e) {
				}
			}
			return true;
		}
		return false;
	},
	
	blinkOnNewMessage: function(dateObject, userID, userName, userRole, messageID, messageText, channelID, ip) {
		if(this.settings['blink'] && this.lastID && !this.channelSwitch && userID !== this.userID) {
			clearInterval(this.blinkInterval);
			this.blinkInterval = setInterval(
				'ajaxChat.blinkUpdate(\''+this.addSlashes(this.decodeSpecialChars(userName))+'\')',
				this.settings['blinkInterval']
			);
		}
	},
	
	blinkUpdate: function(blinkStr) {
		if(!this.originalDocumentTitle) {
			this.originalDocumentTitle = document.title;
		}
		if(!arguments.callee.blink) {
			document.title = '[@ ] '+blinkStr+' - '+this.originalDocumentTitle;
			arguments.callee.blink = 1;
		} else if(arguments.callee.blink > this.settings['blinkIntervalNumber']) {
			clearInterval(this.blinkInterval);
			document.title = this.originalDocumentTitle;
			arguments.callee.blink = 0;
		} else {
			if(arguments.callee.blink % 2 !== 0) {
				document.title = '[@ ] '+blinkStr+' - '+this.originalDocumentTitle;
			} else {
				document.title = '[ @] '+blinkStr+' - '+this.originalDocumentTitle;
			}
			arguments.callee.blink++;
		}
	},
	
	updateChatlistView: function() {		
		if(this.dom['chatList'].childNodes && this.settings['maxMessages']) {
			while(this.dom['chatList'].childNodes.length > this.settings['maxMessages']) {
				this.dom['chatList'].removeChild(this.dom['chatList'].firstChild);
			}
		}
		
		if(this.settings['autoScroll']) {
			this.dom['chatList'].scrollTop = this.dom['chatList'].scrollHeight;
		}
	},
	
	encodeText: function(text) {
		return encodeURIComponent(text);
	},

	decodeText: function(text) {
		return decodeURIComponent(text);
	},

	utf8Encode: function(plainText) {
		var utf8Text = '';
		for(var i=0; i<plainText.length; i++) {
			var c=plainText.charCodeAt(i);
			if(c<128) {
				utf8Text += String.fromCharCode(c);
			} else if((c>127) && (c<2048)) {
				utf8Text += String.fromCharCode((c>>6)|192);
				utf8Text += String.fromCharCode((c&63)|128);
			} else {
				utf8Text += String.fromCharCode((c>>12)|224);
				utf8Text += String.fromCharCode(((c>>6)&63)|128);
				utf8Text += String.fromCharCode((c&63)|128);
			}
		}
		return utf8Text;
	},

	utf8Decode: function(utf8Text) {
		var plainText = '';
		var c,c2,c3;
		var i=0;
		while(i<utf8Text.length) {
			c = utf8Text.charCodeAt(i);
			if(c<128) {
				plainText += String.fromCharCode(c);
				i++;
			} else if((c>191) && (c<224)) {
				c2 = utf8Text.charCodeAt(i+1);
				plainText += String.fromCharCode(((c&31)<<6) | (c2&63));
				i+=2;
			} else {
				c2 = utf8Text.charCodeAt(i+1);
				c3 = utf8Text.charCodeAt(i+2);
				plainText += String.fromCharCode(((c&15)<<12) | ((c2&63)<<6) | (c3&63));
				i+=3;
			}
		}
		return plainText;
	},

	encodeSpecialChars: function(text) {
		return text.replace(
			/[&<>'"]/g,
			this.encodeSpecialCharsCallback
		);
	},
	
	encodeSpecialCharsCallback: function(str) {
		switch(str) {
			case '&':
				return '&amp;';
			case '<':
				return '&lt;';
			case '>':
				return '&gt;';
			case '\'':
				// As &apos; is not supported by IE, we use &#39; as replacement for ('):
				return '&#39;';
			case '"':
				return '&quot;';
			default:
				return str;
		}
	},

	decodeSpecialChars: function(text) {
		var regExp = new RegExp('(&amp;)|(&lt;)|(&gt;)|(&#39;)|(&quot;)', 'g');
		
		return text.replace(
			regExp,
			this.decodeSpecialCharsCallback
		);
	},
	
	decodeSpecialCharsCallback: function(str) {
		switch(str) {
			case '&amp;':
				return '&';
			case '&lt;':
				return '<';
			case '&gt;':
				return '>';
			case '&#39;':
				return '\'';
			case '&quot;':
				return '"';
			default:
				return str;
		}
	},
	
	inArray: function(haystack, needle) {
		var i = haystack.length;
		while(i--) {
			if(haystack[i] === needle) {
				return true;
			}
		}
		return false;
	},

	arraySearch: function(needle, haystack) {
		var i = haystack.length;
		while(i--) {
			if(haystack[i] === needle) {
				return i;
			}
		}
	    return false;
	},

	stripTags: function(str) {
		if (!arguments.callee.regExp) {
			arguments.callee.regExp = new RegExp('<\\/?[^>]+?>', 'g');
		}
		
		return str.replace(arguments.callee.regExp, '');
	},

	stripBBCodeTags: function(str) {
		if (!arguments.callee.regExp) {
			arguments.callee.regExp = new RegExp('\\[\\/?[^\\]]+?\\]', 'g');
		}
		
		return str.replace(arguments.callee.regExp, '');
	},	

	escapeRegExp: function(text) {
		if (!arguments.callee.regExp) {
			var specials = new Array(
				'^', '$', '*', '+', '?', '.', '|', '/',
				'(', ')', '[', ']', '{', '}', '\\'
			);
			arguments.callee.regExp = new RegExp(
				'(\\' + specials.join('|\\') + ')', 'g'
			);
		}
		return text.replace(arguments.callee.regExp, '\\$1');
	},
	
	addSlashes: function(text) {
		// Adding slashes in front of apostrophs and backslashes to ensure a valid JavaScript expression:
		return text.replace(/\\/g, '\\\\').replace(/\'/g, '\\\'');
	},

	removeSlashes: function(text) {
		// Removing slashes added by calling addSlashes(text) previously:
		return text.replace(/\\\\/g, '\\').replace(/\\\'/g, '\'');
	},

	formatDate: function(format, date) {
		date = (date == null) ? new date() : date;
		
		return format
		.replace(/%Y/g, date.getFullYear())
		.replace(/%m/g, this.addLeadingZero(date.getMonth()+1))
		.replace(/%d/g, this.addLeadingZero(date.getDate()))
		.replace(/%H/g, this.addLeadingZero(date.getHours()))
		.replace(/%i/g, this.addLeadingZero(date.getMinutes()))
		.replace(/%s/g, this.addLeadingZero(date.getSeconds()));
	},
	
	addLeadingZero: function(number) {
		number = number.toString();
		if(number.length < 2) {
			number = '0'+number;
		}
		return number;
	},

	getUserIDFromUserName: function(userName) {
		var index = this.arraySearch(userName, this.userNamesList);
		if(index !== false) {
			return this.usersList[index];
		}
		return null;
	},

	getUserNameFromUserID: function(userID) {
		var index = this.arraySearch(userID, this.usersList);
		if(index !== false) {
			return this.userNamesList[index];
		}
		return null;
	},

	getRoleClass: function(roleID) {
		switch(parseInt(roleID)) {
			case 0:
				return 'guest';
			case 1:
				return 'user';
			case 2:
				return 'moderator';
			case 3:
				return 'admin';
			case 4:
				return 'chatBot';
			default:
				return 'default';
		}
	},
	
	handleInputFieldKeyPress: function(event) {
		if(event.keyCode === 13 && !event.shiftKey) {
			this.sendMessage();
			try {
				event.preventDefault();
			} catch(e) {
				event.returnValue = false; // IE
			}
			return false;
		}
		return true;
	},

	handleInputFieldKeyUp: function(event) {
		this.updateMessageLengthCounter();
	},
	
	updateMessageLengthCounter: function() {
		if(this.dom['messageLengthCounter']) {
			this.updateDOM(
				'messageLengthCounter',
				this.dom['inputField'].value.length	+ '/' + this.messageTextMaxLength,
				false,
				true
			);
		}
	},
	
	sendMessage: function(text) {
		text = text ? text : this.dom['inputField'].value;
		if(!text) {
			return;
		}
		text = this.parseInputMessage(text);
		if(text) {
			clearTimeout(this.timer);
			var message = 	'lastID='
							+ this.lastID
							+ '&text='
							+ this.encodeText(text);				
			this.makeRequest(this.ajaxURL,'POST',message);
		}
		this.dom['inputField'].value = '';
		this.dom['inputField'].focus();
		this.updateMessageLengthCounter();
	},
	
	parseInputMessage: function(text) {
		var textParts;
		if(text.charAt(0) === '/') {
			textParts = text.split(' ');
			switch(textParts[0]) {
				case '/ignore':
					text = this.parseIgnoreInputCommand(text, textParts);
					break;
				case '/clear':
					this.clearChatList();
					return false;
					break;
				default:
					text = this.parseCustomInputCommand(text, textParts);
			}
			if(text && this.settings['persistFontColor'] && this.settings['fontColor']) {
				text = this.assignFontColorToCommandMessage(text, textParts);
			}
		} else {
			text = this.parseCustomInputMessage(text);
			if(text && this.settings['persistFontColor'] && this.settings['fontColor']) {
				text = this.assignFontColorToMessage(text);
			}
		}
		return text;
	},
	
	assignFontColorToMessage: function(text) {
		return '[color='+this.settings['fontColor']+']'+text+'[/color]';
	},

	assignFontColorToCommandMessage: function(text, textParts) {
		switch(textParts[0]) {
			case '/msg':
			case '/describe':
				if(textParts.length > 2) {
					return	textParts[0]+' '+textParts[1]+' '
							+ '[color='+this.settings['fontColor']+']'
							+ textParts.slice(2).join(' ')
							+ '[/color]';
				}
				break;
			case '/me':
			case '/action':
				if(textParts.length > 1) {
					return	textParts[0]+' '
							+ '[color='+this.settings['fontColor']+']'
							+ textParts.slice(1).join(' ')
							+ '[/color]';
				}
				break;
		}
		return text;
	},
	
	parseIgnoreInputCommand: function(text, textParts) {
		var userName, ignoredUserNames = this.getIgnoredUserNames(), i;
		if(textParts.length > 1) {
			userName = this.encodeSpecialChars(textParts[1]);
			// Prevent adding the chatBot or current user to the list:
			if(userName === this.userName || userName === this.getEncodedChatBotName()) {
				// Display the list of ignored users instead:
				return this.parseIgnoreInputCommand(null, new Array('/ignore'));
			}
			if(ignoredUserNames.length > 0) {
				i = ignoredUserNames.length;
				while(i--) {
					if(ignoredUserNames[i] === userName) {
						ignoredUserNames.splice(i,1);
						this.addChatBotMessageToChatList('/ignoreRemoved '+userName);
						this.setIgnoredUserNames(ignoredUserNames);
						this.updateChatlistView();
						return null;
					}
				}
			}
			ignoredUserNames.push(userName);
			this.addChatBotMessageToChatList('/ignoreAdded '+userName);
			this.setIgnoredUserNames(ignoredUserNames);
		} else {
			if(ignoredUserNames.length === 0) {
				this.addChatBotMessageToChatList('/ignoreListEmpty -');
			} else {
				this.addChatBotMessageToChatList('/ignoreList '+ignoredUserNames.join(' '));
			}
		}
		this.updateChatlistView();
		return null;
	},

	getIgnoredUserNames: function() {
		var ignoredUserNamesString;
		if(!this.ignoredUserNames) {
			ignoredUserNamesString = this.getSetting('ignoredUserNames');
			if(ignoredUserNamesString) {
				this.ignoredUserNames = ignoredUserNamesString.split(' ');
			} else {
				this.ignoredUserNames = [];
			}
		}
		return this.ignoredUserNames;
	},
	
	setIgnoredUserNames: function(ignoredUserNames) {
		this.ignoredUserNames = ignoredUserNames;
		this.setSetting('ignoredUserNames', ignoredUserNames.join(' '));
	},
	
	ignoreMessage: function(dateObject, userID, userName, userRole, messageID, messageText, channelID, ip) {
		var textParts;
		if(userID === this.chatBotID && messageText.charAt(0) === '/') {
			textParts = messageText.split(' ');
			if(textParts.length > 1) {
				switch(textParts[0]) {
					case '/invite':
					case '/uninvite':
					case '/roll':
						userName = textParts[1];
						break;
				}	
			}
		}
		if(this.inArray(this.getIgnoredUserNames(), userName)) {
			return true;
		}
		return false;
	},

	deleteMessage: function(messageID) {
		var messageNode = this.getMessageNode(messageID), originalClass, nextSibling;
		if(messageNode) {
			originalClass = this.getClass(messageNode);
			this.setClass(messageNode, originalClass+' deleteSelected');
			if(confirm(this.lang['deleteMessageConfirm'])) {
				nextSibling = messageNode.nextSibling;
				try {
					this.dom['chatList'].removeChild(messageNode);
					if(nextSibling) {
						this.updateChatListRowClasses(nextSibling);
					}
					this.updateChat('&delete='+messageID);
				} catch(e) {
					this.setClass(messageNode, originalClass);
				}
			} else {
				this.setClass(messageNode, originalClass);
			}
		}
	},

	updateChatListRowClasses: function(node) {
		var previousNode, rowEven;
		if(!node) {
			node = this.dom['chatList'].firstChild;
		}
		if(node) {
			previousNode = node.previousSibling;
			rowEven = (previousNode && this.getClass(previousNode) === 'rowOdd') ? true : false;
			while(node) {
				this.setClass(node, (rowEven ? 'rowEven' : 'rowOdd'));
				node = node.nextSibling;
				rowEven = !rowEven;
			}
		}
	},
	
	getClass: function(node) {
		if(typeof node.className !== 'undefined') {
			return node.className; // IE
		} else {
			return node.getAttribute('class');
		}
	},
	
	setClass: function(node, className) {
		if(typeof node.className !== 'undefined') {
			node.className = className; // IE
		} else {
			node.setAttribute('class', className);
		}
	},

	scriptLinkEncode: function(text) {
		return this.encodeText(this.addSlashes(this.decodeSpecialChars(text)));
	},
	
	scriptLinkDecode: function(text) {
		return this.encodeSpecialChars(this.removeSlashes(this.decodeText(text)));
	},

	getScriptLinkValue: function(value) {
		// This method returns plainText encoded values from javascript links
		// The value has to be utf8Decoded for MSIE and Opera:
		if(typeof arguments.callee.utf8Decode === 'undefined') {
			switch(navigator.appName) {
				case 'Microsoft Internet Explorer':
				case 'Opera':
					arguments.callee.utf8Decode = true;
					return this.utf8Decode(value);
				default:
					arguments.callee.utf8Decode = false;
					return value;
			}	
		} else if(arguments.callee.utf8Decode) {
			return this.utf8Decode(value);	
		} else {
			return value;
		}
	},

	sendMessageWrapper: function(text) {
		this.sendMessage(this.getScriptLinkValue(text));
	},

	insertMessageWrapper: function(text) {
		this.insertText(this.getScriptLinkValue(text), true);
	},
	
	switchChannel: function(channel) {
		if(!this.chatStarted) {
			this.clearChatList();
			this.channelSwitch = true;
			this.loginChannelID = null;
			this.loginChannelName = channel;
			this.requestTeaserContent();
			return;
		}
		clearTimeout(this.timer);	
		var message = 	'lastID='
						+ this.lastID
						+ '&channelName='
						+ this.encodeText(channel);		
		this.makeRequest(this.ajaxURL,'POST',message);
		if(this.dom['inputField'] && this.settings['autoFocus']) {
			this.dom['inputField'].focus();
		}
	},

	logout: function() {
		clearTimeout(this.timer);
		var message = 'logout=true';
		this.makeRequest(this.ajaxURL,'POST',message);
	},
	
	handleLogout: function(url) {
		window.location.href = url;
	},

	toggleSetting: function(setting, buttonID) {
		this.setSetting(setting, !this.getSetting(setting));
		if(buttonID) {
			this.updateButton(setting, buttonID);
		}
	},

	updateButton: function(setting, buttonID) {
		var node = document.getElementById(buttonID);
		if(node) {
			this.setClass(node, (this.getSetting(setting) ? 'button' : 'button off'));
		}
	},
	
	showHide: function(id, styleDisplay, displayInline) {
		var node = document.getElementById(id);
		if(node) {
			if(styleDisplay) {
				node.style.display = styleDisplay;
			} else {
				if(node.style.display === 'none') {
					node.style.display = (displayInline ? 'inline' : 'block'); 
				} else {
					node.style.display = 'none';
				}
			}	
		}
	},

	setPersistFontColor: function(bool) {
		this.settings['persistFontColor'] = bool;		
		if(!this.settings['persistFontColor']) {
			this.settings['fontColor'] = null;
			if(this.dom['inputField']) {
				this.dom['inputField'].style.color = '';
			}
		}
	},

	setFontColor: function(color) {
		if(this.settings['persistFontColor']) {
			this.settings['fontColor'] = color;
			if(this.dom['inputField']) {
				this.dom['inputField'].style.color = color;
			}
			if(this.dom['colorCodesContainer']) {
				this.dom['colorCodesContainer'].style.display = 'none';
				if(this.dom['inputField']) {
					this.dom['inputField'].focus();
				}
			}
		} else {
			this.insert('[color=' + color + ']', '[/color]');
		}
	},
	
	insertText: function(text, clearInputField) {
		if(clearInputField) {
			this.dom['inputField'].value = '';
		}
		this.insert(text, '');
	},
	
	insertBBCode: function(bbCode) {
		switch(bbCode) {			
			case 'url':
				var url = prompt(this.lang['urlDialog'], 'http://');
				if(url)
					this.insert('[url=' + url + ']', '[/url]');
				else
					this.dom['inputField'].focus();
				break;
			default:
				this.insert('[' + bbCode + ']', '[/' + bbCode + ']');		
		}
	},

	insert: function(startTag, endTag) {
		this.dom['inputField'].focus();
		// Internet Explorer:
		if(typeof document.selection !== 'undefined') {
			// Insert the tags:
			var range = document.selection.createRange();
			var insText = range.text;
			range.text = startTag + insText + endTag;
			// Adjust the cursor position:
			range = document.selection.createRange();
			if (insText.length === 0) {
				range.move('character', -endTag.length);
			} else {
				range.moveStart('character', startTag.length + insText.length + endTag.length);			
			}
			range.select();
		}
		// Firefox, etc. (Gecko based browsers):
		else if(typeof this.dom['inputField'].selectionStart !== 'undefined') {
			// Insert the tags:
			var start = this.dom['inputField'].selectionStart;
			var end = this.dom['inputField'].selectionEnd;
			var insText = this.dom['inputField'].value.substring(start, end);
			this.dom['inputField'].value = 	this.dom['inputField'].value.substr(0, start)
											+ startTag
											+ insText
											+ endTag
											+ this.dom['inputField'].value.substr(end);
			// Adjust the cursor position:
			var pos;
			if (insText.length === 0) {
				pos = start + startTag.length;
			} else {
				pos = start + startTag.length + insText.length + endTag.length;
			}
			this.dom['inputField'].selectionStart = pos;
			this.dom['inputField'].selectionEnd = pos;
		}
		// Other browsers:
		else {
			var pos = this.dom['inputField'].value.length;
			this.dom['inputField'].value = 	this.dom['inputField'].value.substr(0, pos)
											+ startTag
											+ endTag
											+ this.dom['inputField'].value.substr(pos);
		}
	},
	
	replaceText: function(text) {
		try{
			text = this.replaceLineBreaks(text);
			if(text.charAt(0) === '/') {
				text = this.replaceCommands(text);
			} else {
				text = this.replaceBBCode(text);
				text = this.replaceHyperLinks(text);
				text = this.replaceEmoticons(text);
			}
			text = this.breakLongWords(text);		
			text = this.replaceCustomText(text);
		} catch(e){
			//alert(e);
		}
		return text;
	},
	
	replaceCommands: function(text) {
		try {
			if(text.charAt(0) !== '/') {
				return text;
			}
			var textParts = text.split(' ');				
			switch(textParts[0]) {
				case '/login':
					return this.replaceCommandLogin(textParts);
				case '/logout':
					return this.replaceCommandLogout(textParts);
				case '/channelEnter':
					return this.replaceCommandChannelEnter(textParts);
				case '/channelLeave':
					return this.replaceCommandChannelLeave(textParts);
				case '/privmsg':
					return this.replaceCommandPrivMsg(textParts);
				case '/privmsgto':
					return this.replaceCommandPrivMsgTo(textParts);
				case '/privaction':
					return this.replaceCommandPrivAction(textParts);
				case '/privactionto':
					return this.replaceCommandPrivActionTo(textParts);
				case '/me':
				case '/action':
					return this.replaceCommandAction(textParts);
				case '/invite':
					return this.replaceCommandInvite(textParts);
				case '/inviteto':
					return this.replaceCommandInviteTo(textParts);
				case '/uninvite':
					return this.replaceCommandUninvite(textParts);
				case '/uninviteto':
					return this.replaceCommandUninviteTo(textParts);
				case '/queryOpen':
					return this.replaceCommandQueryOpen(textParts);
				case '/queryClose':
					return this.replaceCommandQueryClose(textParts);
				case '/ignoreAdded':
					return this.replaceCommandIgnoreAdded(textParts);
				case '/ignoreRemoved':
					return this.replaceCommandIgnoreRemoved(textParts);
				case '/ignoreList':
					return this.replaceCommandIgnoreList(textParts);
				case '/ignoreListEmpty':
					return this.replaceCommandIgnoreListEmpty(textParts);
				case '/kick':
					return this.replaceCommandKick(textParts);
				case '/who':
					return this.replaceCommandWho(textParts);
				case '/whoChannel':
					return this.replaceCommandWhoChannel(textParts);
				case '/whoEmpty':
					return this.replaceCommandWhoEmpty(textParts);
				case '/list':
					return this.replaceCommandList(textParts);
				case '/bans':
					return this.replaceCommandBans(textParts);
				case '/bansEmpty':
					return this.replaceCommandBansEmpty(textParts);
				case '/unban':
					return this.replaceCommandUnban(textParts);
				case '/whois':
					return this.replaceCommandWhois(textParts);
				case '/whereis':
					return this.replaceCommandWhereis(textParts);
				case '/roll':
					return this.replaceCommandRoll(textParts);
				case '/nick':
					return this.replaceCommandNick(textParts);
				case '/error':
					return this.replaceCommandError(textParts);
				default:
					return this.replaceCustomCommands(text, textParts);
			}
		} catch(e) {
			//alert(e);
		}
		return text;
	},

	replaceCommandLogin: function(textParts) {
		return	'<span class="chatBotMessage">'
				+ this.lang['login'].replace(/%s/, textParts[1])
				+ '</span>';		
	},

	replaceCommandLogout: function(textParts) {
		var type = '';
		if(textParts.length === 3)
			type = textParts[2];
		return	'<span class="chatBotMessage">'
				+ this.lang['logout' + type].replace(/%s/, textParts[1])
				+ '</span>';		
	},
	
	replaceCommandChannelEnter: function(textParts) {
		return	'<span class="chatBotMessage">'
				+ this.lang['channelEnter'].replace(/%s/, textParts[1])
				+ '</span>';		
	},
	
	replaceCommandChannelLeave: function(textParts) {
		return	'<span class="chatBotMessage">'
				+ this.lang['channelLeave'].replace(/%s/, textParts[1])
				+ '</span>';		
	},
	
	replaceCommandPrivMsg: function(textParts) {
		var privMsgText = textParts.slice(1).join(' ');
		privMsgText = this.replaceBBCode(privMsgText);
		privMsgText = this.replaceHyperLinks(privMsgText);
		privMsgText = this.replaceEmoticons(privMsgText);
		return	'<span class="privmsg">'
				+ this.lang['privmsg']
				+ '</span> '
				+ privMsgText;
	},
	
	replaceCommandPrivMsgTo: function(textParts) {
		var privMsgText = textParts.slice(2).join(' ');
		privMsgText = this.replaceBBCode(privMsgText);
		privMsgText = this.replaceHyperLinks(privMsgText);
		privMsgText = this.replaceEmoticons(privMsgText);
		return	'<span class="privmsg">'
				+ this.lang['privmsgto'].replace(/%s/, textParts[1])
				+ '</span> '
				+ privMsgText;
	},
	
	replaceCommandPrivAction: function(textParts) {
		var privActionText = textParts.slice(1).join(' ');
		privActionText = this.replaceBBCode(privActionText);
		privActionText = this.replaceHyperLinks(privActionText);
		privActionText = this.replaceEmoticons(privActionText);
		return	'<span class="action">'
				+ privActionText
				+ '</span> <span class="privmsg">'
				+ this.lang['privmsg']
				+ '</span> ';
	},
	
	replaceCommandPrivActionTo: function(textParts) {
		var privActionText = textParts.slice(2).join(' ');
		privActionText = this.replaceBBCode(privActionText);
		privActionText = this.replaceHyperLinks(privActionText);
		privActionText = this.replaceEmoticons(privActionText);
		return	'<span class="action">'
				+ privActionText
				+ '</span> <span class="privmsg">'
				+ this.lang['privmsgto'].replace(/%s/, textParts[1])
				+ '</span> ';		
	},
	
	replaceCommandAction: function(textParts) {
		var actionText = textParts.slice(1).join(' ');
		actionText = this.replaceBBCode(actionText);
		actionText = this.replaceHyperLinks(actionText);
		actionText = this.replaceEmoticons(actionText);
		return	'<span class="action">'
				+ actionText
				+ '</span>';		
	},
	
	replaceCommandInvite: function(textParts) {
		var inviteText = this.lang['invite']
							.replace(/%s/, textParts[1])
							.replace(
								/%s/,
								'<a href="javascript:ajaxChat.sendMessageWrapper(\'/join '
								+ this.scriptLinkEncode(textParts[2])
								+ '\');" title="'
								+ this.lang['joinChannel'].replace(/%s/, textParts[2])
								+ '">'
								+ textParts[2]
								+ '</a>'
							);
		return	'<span class="chatBotMessage">'
				+ inviteText
				+ '</span>';		
	},
	
	replaceCommandInviteTo: function(textParts) {
		var inviteText = this.lang['inviteto']
							.replace(/%s/, textParts[1])
							.replace(/%s/, textParts[2]);
		return	'<span class="chatBotMessage">'
				+ inviteText
				+ '</span>';		
	},
	
	replaceCommandUninvite: function(textParts) {
		var uninviteText = this.lang['uninvite']
							.replace(/%s/, textParts[1])
							.replace(/%s/, textParts[2]);
		return	'<span class="chatBotMessage">'
				+ uninviteText
				+ '</span>';		
	},
	
	replaceCommandUninviteTo: function(textParts) {
		var uninviteText = this.lang['uninviteto']
							.replace(/%s/, textParts[1])
							.replace(/%s/, textParts[2]);
		return	'<span class="chatBotMessage">'
				+ uninviteText
				+ '</span>';		
	},
	
	replaceCommandQueryOpen: function(textParts) {
		return	'<span class="chatBotMessage">'
				+ this.lang['queryOpen'].replace(/%s/, textParts[1])
				+ '</span>';		
	},
		
	replaceCommandQueryClose: function(textParts) {
		return	'<span class="chatBotMessage">'
				+ this.lang['queryClose'].replace(/%s/, textParts[1])
				+ '</span>';		
	},
		
	replaceCommandIgnoreAdded: function(textParts) {
		return	'<span class="chatBotMessage">'
				+ this.lang['ignoreAdded'].replace(/%s/, textParts[1])
				+ '</span>';		
	},
		
	replaceCommandIgnoreRemoved: function(textParts) {
		return	'<span class="chatBotMessage">'
				+ this.lang['ignoreRemoved'].replace(/%s/, textParts[1])
				+ '</span>';		
	},
		
	replaceCommandIgnoreList: function(textParts) {
		return	'<span class="chatBotMessage">'
				+ this.lang['ignoreList'] + ' '
				+ this.getInlineUserMenu(textParts.slice(1))
				+ '</span>';		
	},
		
	replaceCommandIgnoreListEmpty: function(textParts) {
		return	'<span class="chatBotMessage">'
				+ this.lang['ignoreListEmpty']
				+ '</span>';			
	},
		
	replaceCommandKick: function(textParts) {
		return	'<span class="chatBotMessage">'
				+ this.lang['logoutKicked'].replace(/%s/, textParts[1])
				+ '</span>';		
	},
		
	replaceCommandWho: function(textParts) {
		return	'<span class="chatBotMessage">'
				+ this.lang['who'] + ' '
				+ this.getInlineUserMenu(textParts.slice(1))
				+ '</span>';		
	},

	replaceCommandWhoChannel: function(textParts) {
		return	'<span class="chatBotMessage">'
				+ this.lang['whoChannel'].replace(/%s/, textParts[1]) + ' '
				+ this.getInlineUserMenu(textParts.slice(2))
				+ '</span>';		
	},
	
	replaceCommandWhoEmpty: function(textParts) {
		return	'<span class="chatBotMessage">'
				+ this.lang['whoEmpty']
				+ '</span>';		
	},
		
	replaceCommandList: function(textParts) {
		var channels = textParts.slice(1);
		var listChannels = [];
		var channelName;
		for(var i=0; i<channels.length; i++) {
			channelName = (channels[i] === this.channelName) ? '<b>'+channels[i]+'</b>' : channels[i];
			listChannels.push(
				'<a href="javascript:ajaxChat.sendMessageWrapper(\'/join '
				+ this.scriptLinkEncode(channels[i])
				+ '\');" title="'
				+ this.lang['joinChannel'].replace(/%s/, channels[i])
				+ '">'
				+ channelName
				+ '</a>'
			);
		}
		return	'<span class="chatBotMessage">'
				+ this.lang['list'] + ' '
				+ listChannels.join(', ')
				+ '</span>';		
	},
		
	replaceCommandBans: function(textParts) {
		var users = textParts.slice(1);
		var listUsers = [];
		for(var i=0; i<users.length; i++) {
			listUsers.push(
				'<a href="javascript:ajaxChat.sendMessageWrapper(\'/unban '
				+ this.scriptLinkEncode(users[i])
				+ '\');" title="'
				+ this.lang['unbanUser'].replace(/%s/, users[i])
				+ '">'
				+ users[i]
				+ '</a>'
			);
		}
		return	'<span class="chatBotMessage">'
				+ this.lang['bans'] + ' '
				+ listUsers.join(', ')
				+ '</span>';		
	},
		
	replaceCommandBansEmpty: function(textParts) {
		return	'<span class="chatBotMessage">'
				+ this.lang['bansEmpty']
				+ '</span>';		
	},
		
	replaceCommandUnban: function(textParts) {
		return	'<span class="chatBotMessage">'
				+ this.lang['unban'].replace(/%s/, textParts[1])
				+ '</span>';		
	},
	
	replaceCommandWhois: function(textParts) {
		return	'<span class="chatBotMessage">'
				+ this.lang['whois'].replace(/%s/, textParts[1]) + ' '
				+ textParts[2]
				+ '</span>';		
	},

	replaceCommandWhereis: function(textParts) {
		return	'<span class="chatBotMessage">'
				+ this.lang['whereis'].replace(/%s/, textParts[1]).replace(
								/%s/,
								'<a href="javascript:ajaxChat.sendMessageWrapper(\'/join '
								+ this.scriptLinkEncode(textParts[2])
								+ '\');" title="'
								+ this.lang['joinChannel'].replace(/%s/, textParts[2])
								+ '">'
								+ textParts[2]
								+ '</a>'
							)
				+ '</span>';		
	},
	
	replaceCommandRoll: function(textParts) {
		var rollText = this.lang['roll'].replace(/%s/, textParts[1]);
		rollText = rollText.replace(/%s/, textParts[2]);
		rollText = rollText.replace(/%s/, textParts[3]);
		return	'<span class="chatBotMessage">'
				+ rollText
				+ '</span>';		
	},
		
	replaceCommandNick: function(textParts) {
		return	'<span class="chatBotMessage">'
				+ this.lang['nick'].replace(/%s/, textParts[1]).replace(/%s/, textParts[2])
				+ '</span>';		
	},
		
	replaceCommandError: function(textParts) {
		var errorMessage = this.lang['error'+textParts[1]];
		if(!errorMessage) {
			errorMessage = 'Error: Unknown.';
		} else if(textParts.length > 2) {
			errorMessage = errorMessage.replace(/%s/, textParts.slice(2).join(' '));
		}
		return	'<span class="chatBotErrorMessage">'
				+ errorMessage
				+ '</span>';		
	},

	getInlineUserMenu: function(users) {
		var menu = '';
		for(var i=0; i<users.length; i++) {
			if(i>0) {
				menu += ', ';
			}
			menu	+= '<a href="javascript:ajaxChat.toggleUserMenu(\''
					+ this.getInlineUserMenuDocumentID(this.userMenuCounter, i)
					+ '\', \''
					+ this.scriptLinkEncode(users[i])
					+ '\', null);" title="'
					+ this.lang['toggleUserMenu'].replace(/%s/, users[i])
					+ '" dir="'
					+ this.baseDirection
					+ '">'
					+ ((users[i] === this.userName) ? '<b>'+users[i]+'</b>' : users[i])
					+ '</a>'
					+ '<ul class="inlineUserMenu" id="'
					+ this.getInlineUserMenuDocumentID(this.userMenuCounter, i)
					+ '" style="display:none;">'
					+ '</ul>';
		}
		this.userMenuCounter++;
		return menu;
	},

	containsUnclosedTags: function(str) {
		var openTags, closeTags,
			regExpOpenTags = /<[^>\/]+?>/gm,
			regExpCloseTags = /<\/[^>]+?>/gm;
	
		openTags	= str.match(regExpOpenTags);
		closeTags	= str.match(regExpCloseTags);
		// Return true if the number of tags doesn't match:
		if((!openTags && closeTags) ||
			(openTags && !closeTags) ||
			(openTags && closeTags && (openTags.length !== closeTags.length))) {
			return true;
		}
		return false;
	},
		
	breakLongWords: function(text) {
		var newText, charCounter, currentChar, withinTag, withinEntity, i;
	
		if(!this.settings['wordWrap'])
			return text;
		
		newText = '';
		charCounter = 0;
		
		for(i=0; i<text.length; i++) {
			currentChar = text.charAt(i);
			
			// Check if we are within a tag or entity:
			if(currentChar === '<') {
				withinTag = true;
				// Reset the charCounter after newline tags (<br/>):
				if(i>5 && text.substr(i-5,4) === '<br/')
					charCounter = 0;				
			} else if(withinTag && i>0 && text.charAt(i-1) === '>') {
				withinTag = false;
				// Reset the charCounter after newline tags (<br/>):
				if(i>4 && text.substr(i-5,4) === '<br/')
					charCounter = 0;
			}
			
			if(!withinTag && currentChar === '&') {
				withinEntity = true;
			} else if(withinEntity && i>0 && text.charAt(i-1) === ';') {
				withinEntity = false;
				// We only increase the charCounter once for the whole entiy:
				charCounter++;
			}
				
			if(!withinTag && !withinEntity) {
				// Reset the charCounter if we encounter a word boundary:
				if(currentChar === ' ' || currentChar === '\n' || currentChar === '\t') {
					charCounter = 0;
				} else {
					// We are not within a tag or entity, increase the charCounter:
					charCounter++;
				}
				if(charCounter > this.settings['maxWordLength']) {
					// maxWordLength has been reached, break here and reset the charCounter:
					newText += '&#8203;';
					charCounter = 0;
				}
			}		
			// Add the current char to the text:
			newText += currentChar;
		}
		
		return newText;
	},
	
	replaceBBCode: function(text) {
		if(!this.settings['bbCode']) {
			// If BBCode is disabled, just strip the text from BBCode tags:
			return text.replace(/\[(?:\/)?(\w+)(?:=([^<>]*?))?\]/, '');
		}
		// Remove the BBCode tags:
		return text.replace(
			/\[(\w+)(?:=([^<>]*?))?\](.+?)\[\/\1\]/gm, 
			this.replaceBBCodeCallback
		);
	},
	
	replaceBBCodeCallback: function(str, p1, p2, p3) {
		// Only replace predefined BBCode tags:
		if(!ajaxChat.inArray(ajaxChat.bbCodeTags, p1)) {
			return str;
		}
		// Avoid invalid XHTML (unclosed tags):
		if(ajaxChat.containsUnclosedTags(p3)) {
			return str;
		}	
		switch(p1) {
			case 'color':
				return ajaxChat.replaceBBCodeColor(p3, p2);
			case 'url':
				return ajaxChat.replaceBBCodeUrl(p3, p2);
			case 'img':
				return ajaxChat.replaceBBCodeImage(p3);
			case 'quote':
				return ajaxChat.replaceBBCodeQuote(p3, p2);
			case 'code':
				return ajaxChat.replaceBBCodeCode(p3);
			case 'u':
				return ajaxChat.replaceBBCodeUnderline(p3);
			default:
				return ajaxChat.replaceCustomBBCode(p1, p2, p3);
		}
	},

	replaceBBCodeColor: function(content, attribute) {
		if(this.settings['bbCodeColors']) {
			// Only allow predefined color codes:
			if(!attribute || !this.inArray(ajaxChat.colorCodes, attribute))
				return content;								
			return 	'<span style="color:'
					+ attribute + ';">'
					+ this.replaceBBCode(content)
					+ '</span>';
		}
		return content;
	},
	
	replaceBBCodeUrl: function(content, attribute) {
		var url, regExpUrl;
		if(attribute)
			url = attribute.replace(/\s/gm, this.encodeText(' '));
		else
			url = this.stripBBCodeTags(content.replace(/\s/gm, this.encodeText(' ')));
		regExpUrl = new RegExp(
			'^(?:(?:http)|(?:https)|(?:ftp)|(?:irc)):\\/\\/',
			''
		);
		if(!url || !url.match(regExpUrl))
			return content;
		return 	'<a href="'
				+ url
				+ '" onclick="window.open(this.href); return false;">'
				+ this.replaceBBCode(content)
				+ '</a>';
	},
	
	replaceBBCodeImage: function(url) {
		var regExpUrl, maxWidth, maxHeight;
		if(this.settings['bbCodeImages']) {
			regExpUrl = new RegExp(
				this.regExpMediaUrl,
				''
			);
			if(!url || !url.match(regExpUrl))
				return url;
			url = url.replace(/\s/gm, this.encodeText(' '));
			maxWidth = this.dom['chatList'].offsetWidth-50;
			maxHeight = this.dom['chatList'].offsetHeight-50;
			return	'<a href="'
					+url
					+'" onclick="window.open(this.href); return false;">'
					+'<img class="bbCodeImage" style="max-width:'
					+maxWidth
					+'px; max-height:'
					+maxHeight
					+'px;" src="'
					+url
					+'" alt="" onload="ajaxChat.updateChatlistView();"/></a>';
		}
		return url;
	},

	replaceBBCodeQuote: function(content, attribute) {
		if(attribute)
			return	'<span class="quote"><cite>'
					+ this.lang['cite'].replace(/%s/, attribute)
					+ '</cite><q>'
					+ this.replaceBBCode(content)
					+ '</q></span>';
		return 	'<span class="quote"><q>'
				+ this.replaceBBCode(content)
				+ '</q></span>';
	},

	replaceBBCodeCode: function(content) {
		// Replace vertical tabs and multiple spaces with two non-breaking space characters:
		return 	'<code>'
				+ this.replaceBBCode(content.replace(/\t|(?:  )/gm, '&#160;&#160;'))
				+ '</code>';
	},
	
	replaceBBCodeUnderline: function(content) {
		return 	'<span style="text-decoration:underline;">'
				+ this.replaceBBCode(content)
				+ '</span>';
	},
	
	replaceHyperLinks: function(text) {
		var regExp;
		if(!this.settings['hyperLinks']) {
			return text;
		}
		regExp = new RegExp(
			'(^|\\s|>)((?:(?:http)|(?:https)|(?:ftp)|(?:irc)):\\/\\/[^\\s<>]+)(<\\/a>)?',
			'gm'
		);
		return text.replace(
			regExp,
			// Specifying an anonymous function as second parameter:
			function(str, p1, p2, p3) {
				// Do not replace URL's inside URL's:
				if(p3) {
					return str;
				}
				return 	p1
						+ '<a href="'
						+ p2
						+ '" onclick="window.open(this.href); return false;">'
						+ p2
						+ '</a>';
			}
		);
	},

	replaceLineBreaks: function(text) {
		var regExp  = new RegExp('\\n',	'g');

		if(!this.settings['lineBreaks']) {
			return text.replace(regExp, ' ');
		} else {
			return text.replace(regExp, '<br/>');
		}
	},

	replaceEmoticons: function(text) {
		if(!this.settings['emoticons']) {
			return text;
		}
		if(!arguments.callee.regExp) {
			var regExpStr = '^(.*)(';
			for(var i=0; i<this.emoticonCodes.length; i++) {
				if(i!==0)
					regExpStr += '|';
				regExpStr += '(?:' + this.escapeRegExp(this.emoticonCodes[i]) + ')';
			}
			regExpStr += ')(.*)$';
			arguments.callee.regExp = new RegExp(regExpStr, 'gm');
		}
		return text.replace(
			arguments.callee.regExp,			
			this.replaceEmoticonsCallback
		);
	},
	
	replaceEmoticonsCallback: function(str, p1, p2, p3) {
		if (!arguments.callee.regExp) {
			arguments.callee.regExp = new RegExp('(="[^"]*$)|(&[^;]*$)', '');
		}
		// Avoid replacing emoticons in tag attributes or XHTML entities:
		if(p1.match(arguments.callee.regExp)) {
			return str;
		}	
		if(p2) {
			var index = ajaxChat.arraySearch(p2, ajaxChat.emoticonCodes);							
			return 	ajaxChat.replaceEmoticons(p1)
				+	'<img src="'
				+	ajaxChat.dirs['emoticons']
				+	ajaxChat.emoticonFiles[index]
				+	'" alt="'
				+	p2
				+	'" />'
				+ 	ajaxChat.replaceEmoticons(p3);
		}
		return str;
	},
	
	getActiveStyle: function() {
		var cookie = this.readCookie(this.sessionName + '_style');
		var style = cookie ? cookie : this.getPreferredStyleSheet();
		return style;		
	},

	initStyle: function() {
		this.styleInitiated = true;
		this.setActiveStyleSheet(this.getActiveStyle());
	},
	
	persistStyle: function() {
		if(this.styleInitiated) {
			this.createCookie(this.sessionName + '_style', this.getActiveStyleSheet(), this.cookieExpiration);
		}
	},
	
	setSelectedStyle: function() {
		if(this.dom['styleSelection']) {
			var style = this.getActiveStyle();
			var styleOptions = this.dom['styleSelection'].getElementsByTagName('option');
			for(var i=0; i<styleOptions.length; i++) {
				if(styleOptions[i].value == style) {
					styleOptions[i].selected = true;
					break;
				}
			}
		}
	},
	
	getSelectedStyle: function() {
		var styleOptions = this.dom['styleSelection'].getElementsByTagName('option');
		if(this.dom['styleSelection'].selectedIndex === -1) {
			return styleOptions[0].value;
		} else {
			return styleOptions[this.dom['styleSelection'].selectedIndex].value;
		}
	},
	
	setActiveStyleSheet: function(title) {
		var i, a, main, titleFound = false;
		for(i=0; (a = document.getElementsByTagName('link')[i]); i++) {
			if(a.getAttribute('rel').indexOf('style') !== -1 && a.getAttribute('title')) {
				a.disabled = true;
				if(a.getAttribute('title') === title) {
	                a.disabled = false;
	                titleFound = true;
				}
			}
		}
		if(!titleFound && title !== null) {
		   this.setActiveStyleSheet(this.getPreferredStyleSheet());
		}
	},
	
	getActiveStyleSheet: function() {
		var i, a;
		for(i=0; (a = document.getElementsByTagName('link')[i]); i++) {
			if(a.getAttribute('rel').indexOf('style') != -1 && a.getAttribute('title') && !a.disabled) {
				return a.getAttribute('title');
			}
		}
		return null;
	},
	
	getPreferredStyleSheet: function() {
		var i,a;
		for(i=0; (a = document.getElementsByTagName('link')[i]); i++) {
			if(a.getAttribute('rel').indexOf('style') !== -1
				&& a.getAttribute('rel').indexOf('alt') === -1
				&& a.getAttribute('title')
				) {
				return a.getAttribute('title');
			}
		}
		return null;
	},

	switchLanguage: function(langCode) {
		window.location.search = '?lang='+langCode;
	},
	
	createCookie: function(name,value,days) {
		var expires = '';
		if(days) {
			var date = new Date();
			date.setTime(date.getTime()+(days*24*60*60*1000));
			expires = '; expires='+date.toGMTString();
		}
		var path = '; path='+this.cookiePath;
		var domain = this.cookieDomain ? '; domain='+this.cookieDomain : '';
		var secure = this.cookieSecure ? '; secure' : '';
		document.cookie = name+'='+encodeURIComponent(value)+expires+path+domain+secure;
	},
	
	readCookie: function(name) {
		if(!document.cookie)
		   return null;
		var nameEQ = name + '=';
		var ca = document.cookie.split(';');
		for(var i=0; i<ca.length; i++) {
			var c = ca[i];
			while(c.charAt(0) === ' ') {
				c = c.substring(1, c.length);
			}
			if(c.indexOf(nameEQ) === 0) {
				return decodeURIComponent(c.substring(nameEQ.length, c.length));
			}
		}
		return null;
	},

	isCookieEnabled: function() {
		this.createCookie(this.sessionName + '_cookie_test', true, 1);
		var cookie = this.readCookie(this.sessionName + '_cookie_test');
		if(cookie) {
			// Unset the test cookie:
			this.createCookie(this.sessionName + '_cookie_test', true, -1);
			// Cookie test successfull, return true:
			return true;
		}
		return false;
	},
	
	finalize: function() {	
		if(typeof this.finalizeFunction === 'function') {
			this.finalizeFunction();
		}
		// Ensure the socket connection is closed on unload:
		if(this.socket) {
			try {
				this.socket.close();
				this.socket = null;
			} catch(e) {
				//alert(e);
			}
		}	
		this.persistSettings();
		this.persistStyle();		
		this.customFinalize();
	},

	// Override to perform custom actions on flash initialization:
	initializeCustomFlashInterface: function() {	
	},
	
	// Override to handle custom info messages
	handleCustomInfoMessage: function(infoType, infoData) {
	},

	// Override to add custom initialization code
	// This method is called on page load
	customInitialize: function() {		
	},

	// Override to add custom finalization code
	// This method is called on page unload
	customFinalize: function() {	
	},

	// Override to add custom user menu items:
	// Return a string with list items ( <li>menuItem</li> )
	// encodedUserName contains the userName ready to be used for javascript links
	// userID is only available for the online users menu - not for the inline user menu
	// use (encodedUserName == this.encodedUserName) to check for the current user
	getCustomUserMenuItems: function(encodedUserName, userID) {
		return '';
	},

	// Override to parse custom input messages:
	// Return replaced text
	// text contains the whole message
	parseCustomInputMessage: function(text) {
		return text;
	},
	
	// Override to parse custom input commands:
	// Return parsed text
	// text contains the whole message, textParts the message split up as words array
	parseCustomInputCommand: function(text, textParts) {
		return text;
	},
	
	// Override to replace custom text:
	// Return replaced text
	// text contains the whole message
	replaceCustomText: function(text) {
		return text;
	},
	
	// Override to replace custom commands:
	// Return replaced text for custom commands
	// text contains the whole message, textParts the message split up as words array
	replaceCustomCommands: function(text, textParts) {
		return text;
	},

	// Override to replace custom BBCodes:
	// Return replaced text and call replaceBBCode recursively for the content text
	// tag contains the BBCode tag, attribute the BBCode attribute and content the content text
	// This method is only called for BBCode tags which are in the bbCodeTags list
	replaceCustomBBCode: function(tag, attribute, content) {
		return '<' + tag + '>' + this.replaceBBCode(content) + '</' + tag + '>';
	},
	
	// Override to perform custom actions on new messages:
	// Return true if message is to be added to the chatList, else false
	customOnNewMessage: function(dateObject, userID, userName, userRole, messageID, messageText, channelID, ip) {
		return true;
	}

};