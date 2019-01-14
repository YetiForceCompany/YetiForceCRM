<?php
/**
 * OSSMail module config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'db_prefix' => [
		'default' => 'roundcube_',
		'description' => '',
		'validation' => '\App\Validator::standard'
	],
	'default_host' => [
		'default' => ['ssl://imap.gmail.com' => 'ssl://imap.gmail.com'],
		'description' => '',
		'validation' => '\App\Validator::standard',
		'sanitization' => function () {
			$values = func_get_arg(0);
			$values = is_array($values) ? array_map('\App\Purifier::encodeHtml', $values) : \App\Purifier::encodeHtml($values);
			if (!is_array($values)) {
				$values = [$values];
			}
			$saveValue = [];
			foreach ($values as $value) {
				$saveValue[$value] = $value;
			}
			return $saveValue;
		}
	],
	'validate_cert' => [
		'default' => false,
		'description' => 'Sample generated class',
		'validation' => '\App\Validator::isBool'
	],
];
