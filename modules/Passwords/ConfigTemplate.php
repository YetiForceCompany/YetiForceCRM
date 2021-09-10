<?php
/**
 * Password module config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
return [
	'encryptionPass' => [
		'default' => 'yeti',
		'description' => 'Key to encrypt passwords, changing the key results in the loss of all encrypted data.',
		'validation' => '\App\Validator::text'
	],
	'encryptionMethod' => [
		'default' => 'aes-256-cbc',
		'description' => 'Encryption method.',
		'validation' => function () {
			$arg = func_get_arg(0);
			return empty($arg) || ($arg && \in_array($arg, \App\Encryption::getMethods()));
		}
	],
];
