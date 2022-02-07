<?php

/**
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_Inventory_ModalAjax_View extends Settings_Inventory_CreditLimits_View
{
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$qualifiedModuleName = $request->getModule(false);
		$type = $request->getByType('type', 'Standard');

		if ($request->isEmpty('id')) {
			$recordModel = new Settings_Inventory_Record_Model();
		} else {
			$recordModel = Settings_Inventory_Record_Model::getInstanceById($request->getInteger('id'), $type);
		}

		$viewer->assign('PAGE_LABELS', $this->getPageLabels($request));
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('TYPE', $type);
		$viewer->assign('CURRENCY', Vtiger_Util_Helper::getBaseCurrency());
		echo $viewer->view('Modal.tpl', $qualifiedModuleName, true);
	}
}
