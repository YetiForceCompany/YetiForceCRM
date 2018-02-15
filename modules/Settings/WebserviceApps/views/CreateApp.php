<?php

/**
 * Create Key.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_WebserviceApps_CreateApp_View extends Settings_Vtiger_BasicModal_View
{
	public function getSize(\App\Request $request)
	{
		return 'modal-lg';
	}

	public function process(\App\Request $request)
	{
		parent::preProcess($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$recordId = $request->get('record');
		if (!empty($recordId)) {
			$recordModel = Settings_WebserviceApps_Record_Model::getInstanceById($recordId);
			$accountId = $recordModel->get('accounts_id');
			if ($recordModel && !empty($accountId)) {
				$recordModel->set('accountsModel', Vtiger_Record_Model::getInstanceById($accountId));
			}
		} else {
			$recordModel = false;
		}
		$typesServers = Settings_WebserviceApps_Module_Model::getTypes();
		$viewer = $this->getViewer($request);
		$viewer->assign('MAPPING_RELATED_FIELD', \App\Json::encode(\App\ModuleHierarchy::getRelationFieldByHierarchy('SSingleOrders')));
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('TYPES_SERVERS', $typesServers);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('CreateApp.tpl', $qualifiedModuleName);
		parent::postProcess($request);
	}

	public function getModalScripts(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$scripts = [
			"modules.Settings.$moduleName.resources.Edit",
		];
		$scriptInstances = $this->checkAndConvertJsScripts($scripts);

		return $scriptInstances;
	}
}
