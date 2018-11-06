<?php
/**
 * Chat module config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
return [
	// Number of recent chat entries
	'CHAT_ROWS_LIMIT' => 20,
	// What time to update the rooms, number of milliseconds. Default: 10000
	'REFRESH_ROOM_TIME' => 100000,
	// What time to update the new message, number of milliseconds. Default: 2000
	'REFRESH_MESSAGE_TIME' => 10000,
	// The maximum length of the message, If you want to increase the number of characters, you must also change it in the database (u_yf_chat_messages_crm, u_yf_chat_messages_group, u_yf_chat_messages_global).
	'MAX_LENGTH_MESSAGE' => 500,
	// Refresh time for global timer.
	'REFRESH_TIME_GLOBAL' => 5000,
	// Default sound notification.
	'DEFAULT_SOUND_NOTIFICATION' => true,
	// Show the number of new messages.
	'SHOW_NUMBER_OF_NEW_MESSAGES' => true,
];
