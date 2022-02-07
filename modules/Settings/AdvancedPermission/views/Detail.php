<?php

/**
 * Advanced permission detail view class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_AdvancedPermission_Detail_View extends Settings_Vtiger_Index_View
{
	public function process(App\Request $request)
	{
		$record = $request->getInteger('record');
		$qualifiedModuleName = $request->getModule(false);
		$recordModel = Settings_AdvancedPermission_Record_Model::getInstance($record);

		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('DetailView.tpl', $qualifiedModuleName);
	}
}
