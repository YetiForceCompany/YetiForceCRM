<?php
/**
 * Users module config.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'IS_VISIBLE_USER_INFO_FOOTER' => [
		'default' => false,
		'description' => 'Show information about logged user in footer',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'USER_NAME_IS_EDITABLE' => [
		'default' => true,
		'description' => 'Is it possible to edit usernames?',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'CHECK_LAST_USERNAME' => [
		'default' => true,
		'description' => 'Verify previously used usernames',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'SHOW_ROLE_NAME' => [
		'default' => true,
		'description' => 'Show role name',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'FAVORITE_OWNERS' => [
		'default' => false,
		'description' => 'Activation of favorite owners',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'pwnedPasswordProvider' => [
		'default' => 'YetiForce',
		'description' => 'Provider to the check password is in the stolen passwords database',
		'validation' => '\App\Validator::text',
	],
];
