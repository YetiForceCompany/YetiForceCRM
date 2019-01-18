<?php
/**
 * FInvoice module config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'UPDATE_LAST_INVOICE_DATE' => [
		'default' => true,
		'description' => 'Update the date of the last invoice in Account while saving invoice',
		'validation' => '\App\Validator::bool',
		'sanitization' => '\App\Purifier::bool'
	]
];
