<?php
/**
 * Chat module config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'CHAT_ROWS_LIMIT' => [
		'default' => 20,
		'description' => 'Number of recent chat entries',
		'validation' => '\App\Validator::naturalNumber'
	],
	'REFRESH_ROOM_TIME' => [
		'default' => 100000,
		'description' => 'What time to update the rooms, number of milliseconds. Default: 10000',
		'validation' => '\App\Validator::naturalNumber'
	],
	'REFRESH_MESSAGE_TIME' => [
		'default' => 2000,
		'description' => 'What time to update the new message, number of milliseconds. Default: 2000',
		'validation' => '\App\Validator::naturalNumber'
	],
	'MAX_LENGTH_MESSAGE' => [
		'default' => 500,
		'description' => 'The maximum length of the message, If you want to increase the number of characters, you must also change it in the database (u_yf_chat_messages_crm, u_yf_chat_messages_group, u_yf_chat_messages_global).',
		'validation' => '\App\Validator::naturalNumber'
	],
	'REFRESH_TIME_GLOBAL' => [
		'default' => 5000,
		'description' => 'Refresh time for global timer.',
		'validation' => '\App\Validator::naturalNumber'
	],
	'DEFAULT_SOUND_NOTIFICATION' => [
		'default' => true,
		'description' => 'Default sound notification.',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'SHOW_NUMBER_OF_NEW_MESSAGES' => [
		'default' => true,
		'description' => 'Show the number of new messages.',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	]
];
