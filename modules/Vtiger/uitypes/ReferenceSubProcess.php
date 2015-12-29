<?php

/**
 * UIType ReferenceSubProcess Field Class
 * @package YetiForce.Fields
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_ReferenceSubProcess_UIType extends Vtiger_ReferenceLink_UIType
{

	protected $referenceMap = [
		'ProjectTask' => 'Project',
		'ProjectMilestone' => 'Project',
		'SQuoteEnquiries' => 'SSalesProcesses',
		'SRequirementsCards' => 'SSalesProcesses',
		'SCalculations' => 'SSalesProcesses',
		'SQuotes' => 'SSalesProcesses',
		'SSingleOrders' => 'SSalesProcesses',
		'SRecurringOrders' => 'SSalesProcesses',
	];

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/ReferenceSubProcess.tpl';
	}

	public function getReferenceList()
	{
		return array_keys($this->referenceMap);
	}

	public function getParentModule($module)
	{
		if (key_exists($module, $this->referenceMap)) {
			return $this->referenceMap[$module];
		}
		return '';
	}
}
