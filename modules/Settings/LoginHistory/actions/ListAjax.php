<?php

/**
 * 
 * @package YetiForce.Actions
 * @license licenses/License.html
 * @author Mriusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_LoginHistory_ListAjax_Action extends Settings_Vtiger_ListAjax_Action
{

	public function getListViewCount(Vtiger_Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);

		$listViewModel = Settings_Vtiger_ListView_Model::getInstance($qualifiedModuleName);

		$searchField = $request->get('search_key');
		$value = $request->get('search_value');

		if (!empty($searchField) && !empty($value)) {
			$listViewModel->set('search_key', $searchField);
			$listViewModel->set('search_value', $value);
		}

		return $listViewModel->getListViewCount();
	}
}
