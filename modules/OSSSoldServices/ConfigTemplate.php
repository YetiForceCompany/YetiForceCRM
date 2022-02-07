<?php
/**
 * OSSSoldServices module config.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'RENEWAL_TIME' => [
		'default' => '2 month',
		'description' => 'How long before the renewal date should the status be changed. Ex. 2 month, 1 day https://secure.php.net/manual/en/datetime.formats.php',
		'validation' => '\App\Validator::standard'
	],
	'SHOW_RELATION_IN_MODAL' => [
		'default' => ['relationField' => 'parent_id', 'module' => 'Accounts', 'relatedModule' => ['FInvoice', 'ModComments', 'Calendar', 'Documents']],
		'description' => 'Show relations in the modal'
	],
	'SHOW_HIERARCHY_IN_MODAL' => [
		'type' => 'false, [] - inherit fields, [ label => column name, .. ]',
		'default' => [],
		'description' => 'Show hierarchy in modal'
	],
	'RENEWAL_CUSTOMER_FUNCTION' => [
		'type' => '["class" => "", "method" => "", "hierarchy" => ""]',
		'default' => [],
		'description' => 'Renewing the customer function'
	],
];
