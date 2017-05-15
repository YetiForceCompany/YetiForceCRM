<?php
/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
$CONFIG = [
	// How long before the renewal date should the status be changed
	// ex. 2 month, 1 day https://secure.php.net/manual/en/datetime.formats.php
	'RENEWAL_TIME' => '2 month',
	'SHOW_RELATION_IN_MODAL' => ['relationField' => 'parent_id', 'module' => 'Accounts', 'relatedModule' => ['FInvoice', 'ModComments', 'Calendar', 'Documents']],
	'SHOW_FIELD_IN_MODAL' => [],
	// false, [] - inherit fields, [ label => column name, .. ]
	'SHOW_HIERARCHY_IN_MODAL' => [],
	// ['class' => '', 'method' => '', 'hierarchy' => ''],
	'RENEWAL_CUSTOMER_FUNCTION' => [],
];
