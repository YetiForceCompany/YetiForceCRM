<?php

/**
 * UIType ReferenceSubProcess Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Calendar_ReferenceSubProcess_UIType extends Vtiger_ReferenceSubProcess_UIType
{

	public function getReferenceList()
	{
		return ['SQuoteEnquiries', 'SRequirementsCards', 'SCalculations', 'SQuotes', 'SSingleOrders', 'SRecurringOrders', 'HelpDesk', 'SVendorEnquiries'];
	}
}
