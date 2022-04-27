<?php
/**
 * SMSNotifier module config.
 *
 * @package Config
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
return [
	'maxMassSentSMS' => [
		'default' => 500,
		'description' => 'Max number of mass sms sent',
		'validation' => '\App\Validator::naturalNumber',
		'sanitization' => '\App\Purifier::integer',
		'docTags' => ['var' => 'int'],
	],
	'maxCronSentSMS' => [
		'default' => 100,
		'description' => 'The maximum number of sms that cron can send during a single execution',
		'validation' => '\App\Validator::naturalNumber',
		'sanitization' => '\App\Purifier::integer',
		'docTags' => ['var' => 'int'],
	],
];
