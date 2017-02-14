<?php

/**
 * Service contracts module model Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class ServiceContracts_Module_Model extends Vtiger_Module_Model
{

	/**
	 * Function to get list view query for popup window
	 * @param Vtiger_ListView_Model $listviewModel
	 * @param \App\QueryGenerator $queryGenerator
	 */
	public function getQueryByRelatedField(Vtiger_ListView_Model $listviewModel, \App\QueryGenerator $queryGenerator)
	{
		if ($listviewModel->get('src_module') == 'HelpDesk' && !$listviewModel->isEmpty('filterFields')) {
			$filterFields = $listviewModel->get('filterFields');
			if (!empty($filterFields['parent_id'])) {
				$queryGenerator->addNativeCondition(['sc_related_to' => $filterFields['parent_id']]);
			}
		}
	}
}
