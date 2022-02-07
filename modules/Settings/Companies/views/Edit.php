<?php

/**
 * Companies edit view class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_Companies_Edit_View extends Settings_Vtiger_Index_View
{
	/**
	 * Process function.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$record = !$request->isEmpty('record') ? $request->getInteger('record') : '';
		if ($record) {
			$recordModel = Settings_Companies_Record_Model::getInstance($record);
		} else {
			$recordModel = new Settings_Companies_Record_Model();
		}
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('EditView.tpl', $qualifiedModuleName);
	}

	/**
	 * Get footer JS scripts.
	 *
	 * @param \App\Request $request
	 *
	 * @return Vtiger_JsScript_Model[]
	 */
	public function getFooterScripts(App\Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$jsFileNames = [
			'modules.Vtiger.resources.AdvanceFilterEx',
			'modules.Settings.Companies.resources.Edit',
		];
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

		return array_merge($headerScriptInstances, $jsScriptInstances);
	}
}
