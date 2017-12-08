<?php

/**
 * UIType ReferenceSubProcess Field Class
 * @package YetiForce.Uitype
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Calendar_ReferenceSubProcess_UIType extends Vtiger_ReferenceSubProcess_UIType
{

	/**
	 * {@inheritDoc}
	 */
	public function getReferenceList()
	{
		return ['SQuoteEnquiries', 'SRequirementsCards', 'SCalculations', 'SQuotes', 'SSingleOrders', 'SRecurringOrders', 'HelpDesk', 'SVendorEnquiries'];
	}
}
