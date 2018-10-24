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
	'ROWS_LIMIT' => 20,
	// What time to update the news, number of milliseconds
	'REFRESH_TIME' => 4000,
	// The maximum length of the message, If you want to increase the number of characters, you must also change it in the database (u_yf_chat_messages_crm, u_yf_chat_messages_group, u_yf_chat_messages_global).
	'MAX_LENGTH_MESSAGE' => 500
];
