<?php

/**
 * UIType ReferenceSubProcess Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Calendar_ReferenceSubProcess_UIType extends Vtiger_ReferenceSubProcess_UIType
{

	protected $referenceMap = [
		'SQuoteEnquiries' => 'SSalesProcesses',
		'SRequirementsCards' => 'SSalesProcesses',
		'SCalculations' => 'SSalesProcesses',
		'SQuotes' => 'SSalesProcesses',
		'SSingleOrders' => 'SSalesProcesses',
		'SRecurringOrders' => 'SSalesProcesses',
	];
}
