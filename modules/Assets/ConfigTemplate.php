<?php
/**
 * Assets module config.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Sołek <a.solek@yetiforce.com>
 */
return [
	'RENEWAL_TIME' => [
		'default' => '2 month',
		'description' => 'How long before the renewal date should the status be changed
	 ex. 2 month, 1 day https://secure.php.net/manual/en/datetime.formats.php',
		'validation' => '\App\Validator::standard'
	],
	'SHOW_RELATION_IN_MODAL' => [
		'default' => ['relationField' => 'parent_id', 'module' => 'Accounts', 'relatedModule' => ['FInvoice', 'ModComments', 'Calendar', 'Documents']],
		'description' => 'Show relations in the modal',
		'validation' => function () {
			return false;
		}
	],
	'SHOW_FIELD_IN_MODAL' => [
		'default' => [],
		'description' => 'Show fields in the modal'
	],
	'SHOW_HIERARCHY_IN_MODAL' => [
		'default' => [],
		'description' => 'false, [] - inherit fields, [ label => column name, .. ]',
		'validation' => function () {
			$args = func_get_arg(0);
			$moduleModel = Vtiger_Module_Model::getInstance('Assets');
			$fields = $moduleModel->getFields();
			foreach ($fields as $field => $key) {
				return \in_array($field, $args);
			}
		}
	],
	'RENEWAL_CUSTOMER_FUNCTION' => [
		'default' => [],
		'description' => 'Call a callback: ["class" => "", "method" => "", "hierarchy" => ""]',
		'validation' => ''
	],
];
