<?php
/**
 * OSSMailScanner module config.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'ONE_MAIL_FOR_MULTIPLE_RECIPIENTS' => [
		'default' => false,
		'description' => "Add only one mail for multiple recipients.\n@var bool",
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'attachHtmlAndTxtToMessageBody' => [
		'default' => false,
		'description' => "Attach the content of HTML and TXT files to the email message body.\nThe content of all attachments will be added at the very end of the email body.\n@var bool",
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'mailBodyGraphicDocumentsFolder' => [
		'default' => 'T2',
		'description' => "Folder for documents with graphic files\n@var string",
		'validation' => '\App\Validator::alnum'
	],
	'attachMailBodyGraphicUrl' => [
		'default' => true,
		'description' => "Do you want to attach graphic files from the email body as documents: From URL src=https://www.example.domain/image_file ?\n@var bool When the option is disabled, graphic files aren't saved in the CRM",
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'searchPrefixInBody' => [
		'default' => false,
		'description' => 'Search prefix in body, type: boolean',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'helpdeskCreateWithoutNoRelation' => [
		'default' => true,
		'description' => 'Create ticket when contact and account does not exist, type: boolean',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	],
	'helpdeskCreateDefaultStatus' => [
		'default' => 'Open',
		'description' => 'What status should be set when a ticket is created.'
	],
	'helpdeskBindNextWaitForResponseStatus' => [
		'default' => 'Answered',
		'description' => 'What status should be set when a new mail is received regarding a ticket, whose status is awaiting response.'
	],
	'helpdeskBindOpenStatus' => [
		'default' => 'Answered',
		'description' => 'What status should be set when a ticket is closed, but a new mail regarding the ticket is received.'
	],
];
