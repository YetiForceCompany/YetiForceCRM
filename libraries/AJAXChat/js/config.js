/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @copyright (c) Sebastian Tschan
 * @license Modified MIT License
 * @link https://blueimp.net/ajax/
 */

// Ajax Chat config parameters:
var ajaxChatConfig = {

	// The channelID of the channel to enter on login (the loginChannelName is used if set to null):
	loginChannelID: null,
	// The channelName of the channel to enter on login (the default channel is used if set to null):
	loginChannelName: null,	
	
	// The time in ms between update calls to retrieve new chat messages:
	timerRate: 2000,
	
	// The URL to retrieve the XML chat messages (must at least contain one parameter):
	ajaxURL: './?ajax=true',
	// The base URL of the chat directory, used to retrieve media files (images, sound files, etc.):
	baseURL: './',

	// A regular expression for allowed source URL's for media content (e.g. images displayed inline);
	regExpMediaUrl: '^((http)|(https)):\\/\\/',
	
	// If set to false the chat update is delayed until the event defined in ajaxChat.setStartChatHandler():
	startChatOnLoad: true,
	
	// Defines the IDs of DOM nodes accessed by the chat:
	domIDs: {
		// The ID of the chat messages list:
		chatList: 'chatList',
		// The ID of the online users list:
		onlineList: 'onlineList',
		// The ID of the message text input field:
		inputField: 'inputField',
		// The ID of the message text length counter:
		messageLengthCounter: 'messageLengthCounter',
		// The ID of the channel selection:
		channelSelection: 'channelSelection',
		// The ID of the style selection:
		styleSelection: 'styleSelection',
		// The ID of the emoticons container:
		emoticonsContainer: 'emoticonsContainer',
		// The ID of the color codes container:
		colorCodesContainer: 'colorCodesContainer',
		// The ID of the flash interface container:
		flashInterfaceContainer: 'flashInterfaceContainer'
	},

	// Defines the settings which can be modified by users:
	settings: {
		// Defines if BBCode tags are replaced with the associated HTML code tags:
		bbCode: true,
		// Defines if image BBCode is replaced with the associated image HTML code:
		bbCodeImages: true,
		// Defines if color BBCode is replaced with the associated color HTML code:
		bbCodeColors: true,
		// Defines if hyperlinks are made clickable:
		hyperLinks: true,
		// Defines if line breaks are enabled:
		lineBreaks: true,
		// Defines if emoticon codes are replaced with their associated images:
		emoticons: true,
	
		// Defines if the focus is automatically set to the input field on chat load or channel switch:
		autoFocus: true,
		// Defines if the chat list scrolls automatically to display the latest messages:
		autoScroll: true,	
		// The maximum count of messages displayed in the chat list (will be ignored if set to 0):
		maxMessages: 0,
		
		// Defines if long words are wrapped to avoid vertical scrolling:
		wordWrap: true,
		// Defines the maximum length before a word gets wrapped: 
		maxWordLength: 32,
		
		// Defines the format of the date and time displayed for each chat message:
		dateFormat: '(%H:%i:%s)',
		
		// Defines if font colors persist without the need to assign them to each message:
		persistFontColor: false,	
		// The default font color, uses the page default font color if set to null:
		fontColor: null,
		
		// Defines if sounds are played:
		audio: true,
		// Defines the sound volume (0.0 = mute, 1.0 = max):
		audioVolume: 0.5,

		// Defines the sound that is played when normal messages are reveived:
		soundReceive: 'sound_1',
		// Defines the sound that is played on sending normal messages:
		soundSend: 'sound_2',
		// Defines the sound that is played on channel enter or login:
		soundEnter: 'sound_3',
		// Defines the sound that is played on channel leave or logout:
		soundLeave: 'sound_4',
		// Defines the sound that is played on chatBot messages:
		soundChatBot: 'sound_5',
		// Defines the sound that is played on error messages:
		soundError: 'sound_6',
		
		// Defines if the document title blinks on new messages:
		blink: true,
		// Defines the blink interval in ms:
		blinkInterval: 500,
		// Defines the number of blink intervals:
		blinkIntervalNumber: 10
	},
	
	// Defines a list of settings which are not to be stored in a session cookie:
	nonPersistentSettings: [],

	// Defines the list of allowed BBCodes:
	bbCodeTags:[
		'b',
		'i',
		'u',
		'quote',
		'code',
		'color',
		'url',
		'img'
	],
	
	// Defines the list of allowed color codes:
	colorCodes: [
		'gray',
		'silver',
		'white',	
		'yellow',
		'orange',
		'red',
		'fuchsia',
		'purple',
		'navy',
		'blue',
		'aqua',
		'teal',
		'green',
		'lime',
		'olive',
		'maroon',
		'black'
	],
	
	// Defines the list of allowed emoticon codes:
	emoticonCodes: [
		':)',
		':(',
		';)',
		':P',
		':D',
		':|',
		':O',
		':?',
		'8)',
		'8o',
		'B)',
		':-)',
		':-(',
		':-*',
		'O:-D',
		'>:-D',
		':o)',
		':idea:',
		':important:',
		':help:',
		':error:',
		':warning:',
		':favorite:'		
 	],
	
 	// Defines the list of emoticon files associated with the emoticon codes:
	emoticonFiles: [
		'smile.png',
		'sad.png',
		'wink.png',
		'razz.png',
		'grin.png',
		'plain.png',
		'surprise.png',
		'confused.png',
		'glasses.png',
		'eek.png',
		'cool.png',
		'smile-big.png',
		'crying.png',
		'kiss.png',
		'angel.png',
		'devilish.png',
		'monkey.png',
		'idea.png',
		'important.png',
		'help.png',
		'error.png',
		'warning.png',
		'favorite.png'
	],

	// Defines the available sounds loaded on chat start:
	soundFiles: {
		sound_1: 'sound_1.mp3',
		sound_2: 'sound_2.mp3',
		sound_3: 'sound_3.mp3',
		sound_4: 'sound_4.mp3',
		sound_5: 'sound_5.mp3',
		sound_6: 'sound_6.mp3'
	},
	
	
	// Once users have been logged in, the following values are overridden by those in config.php.
	// You should set these to be the same as the ones in config.php to avoid confusion.
	
	// Session identification, used for style and setting cookies:
	sessionName: 'chat',

	// The time in days until the style and setting cookies expire:
	cookieExpiration: 365,
	// The path of the cookies, '/' allows to read the cookies from all directories:
	cookiePath: '/',
	// The domain of the cookies, defaults to the hostname of the server if set to null:
	cookieDomain: null,
	// If enabled, cookies must be sent over secure (SSL/TLS encrypted) connections:
	cookieSecure: null,
	
	// The name of the chat bot:
	chatBotName: 'OS',
	// The userID of the chat bot:
	chatBotID: 0,

	// Allow/Disallow registered users to delete their own messages:
	allowUserMessageDelete: true,
	
	// Minutes until a user is declared inactive (last status update) - the minimum is 2 minutes:
	inactiveTimeout: 5,

	// UserID plus this value are private channels (this is also the max userID and max channelID):
	privateChannelDiff: 500000000,
	// UserID plus this value are used for private messages:
	privateMessageDiff: 1000000000,

	// Defines if login/logout and channel enter/leave are displayed:
	showChannelMessages: true,

	// Max messageText length:
	messageTextMaxLength: 1040,
	
	// Defines if the socket server is enabled:
	socketServerEnabled: false,
	// Defines the hostname of the socket server used to connect from client side:
	socketServerHost: 'localhost',
	// Defines the port of the socket server:
	socketServerPort: 1935,
	// This ID can be used to distinguish between different chat installations using the same socket server:
	socketServerChatID: 0

}