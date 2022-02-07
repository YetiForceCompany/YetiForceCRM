<?php

/**
 * Service contracts module model Class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class ServiceContracts_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Function to get list view query for popup window.
	 *
	 * @param Vtiger_ListView_Model $listviewModel
	 * @param \App\QueryGenerator   $queryGenerator
	 */
	public function getQueryByRelatedField(Vtiger_ListView_Model $listviewModel, App\QueryGenerator $queryGenerator)
	{
		if ('HelpDesk' == $listviewModel->get('src_module') && !$listviewModel->isEmpty('filterFields')) {
			$filterFields = $listviewModel->get('filterFields');
			if (!empty($filterFields['parent_id'])) {
				$queryGenerator->addNativeCondition(['sc_related_to' => (int) $filterFields['parent_id']]);
			}
		}
	}
}
