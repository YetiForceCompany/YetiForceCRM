<?php

/**
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mriusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_LoginHistory_ListAjax_Action extends Settings_Vtiger_ListAjax_Action
{
	public function getListViewCount(\App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);

		$listViewModel = Settings_Vtiger_ListView_Model::getInstance($qualifiedModuleName);

		$searchField = $request->getByType('search_key', 'Alnum');
		$value = $request->getByType('search_value', 'Text');

		if (!empty($searchField) && !empty($value)) {
			$listViewModel->set('search_key', $searchField);
			$listViewModel->set('search_value', $value);
		}
		return $listViewModel->getListViewCount();
	}
}
