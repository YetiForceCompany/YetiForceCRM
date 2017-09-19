<?php
/**
 * Users module config
 * @package YetiForce.Config
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
return [
	// Show information about logged user in footer
	'IS_VISIBLE_USER_INFO_FOOTER' => false,
	// Password crypt type
	'PASSWORD_CRYPT_TYPE' => 'PHP5.3MD5', //'BLOWFISH', MD5;
	// Is it possible to edit a user's name
	'USER_NAME_IS_EDITABLE' => true,
	// Verify previously used usernames
	'CHECK_LAST_USERNAME' => true,
];
