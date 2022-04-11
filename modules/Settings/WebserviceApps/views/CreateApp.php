<?php

/**
 * Create Key.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
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
			$type = $recordModel->get('type');
		} else {
			$type = $request->getByType('type') ?: current(\Api\Core\Containers::$list);
			$recordModel = false;
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('APP_TYPE', $type);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('API_FIELDS', ['SMS' => ['name' => 'M', 'status' => 'M', 'type' => 'M', 'ips' => 'M']]);
		$viewer->view('CreateApp.tpl', $qualifiedModuleName);
		parent::postProcess($request);
	}
}
