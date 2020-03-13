<?php

/**
 * Create Key.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_WebserviceApps_CreateApp_View extends Settings_Vtiger_BasicModal_View
{
	public function getSize(App\Request $request)
	{
		return 'modal-lg';
	}

	public function process(App\Request $request)
	{
		parent::preProcess($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		if (!$request->isEmpty('record')) {
			$recordModel = Settings_WebserviceApps_Record_Model::getInstanceById($request->getInteger('record'));
		} else {
			$recordModel = false;
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('TYPES_SERVERS', Settings_WebserviceApps_Module_Model::getTypes());
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('CreateApp.tpl', $qualifiedModuleName);
		parent::postProcess($request);
	}
}
