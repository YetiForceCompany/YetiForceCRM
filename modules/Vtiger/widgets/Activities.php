<?php

/**
 * Vtiger activities widget class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_Activities_Widget extends Vtiger_Basic_Widget
{
	public $allowedModules = ['Accounts', 'Leads', 'Contacts', 'Vendors', 'OSSEmployees', 'Campaigns', 'HelpDesk', 'Project', 'ServiceContracts', 'SSalesProcesses', 'SQuoteEnquiries', 'SRequirementsCards', 'SCalculations', 'SQuotes', 'SSingleOrders', 'SRecurringOrders', 'SVendorEnquiries'];

	public function getUrl()
	{
		return 'module=' . $this->Module . '&view=Detail&record=' . $this->Record . '&mode=getActivities&page=1&sortorder=ASC&type=current&limit=' . $this->Data['limit'];
	}

	public function getConfigTplName()
	{
		return 'ActivitiesConfig';
	}

	public function getWidget()
	{
		$widget = [];
		$model = Vtiger_Module_Model::getInstance('Calendar');
		if ($model->isPermitted('DetailView')) {
			$this->Config['url'] = $this->getUrl();
			$this->Config['tpl'] = 'Activities.tpl';
			$widget = $this->Config;
		}
		return $widget;
	}
}
