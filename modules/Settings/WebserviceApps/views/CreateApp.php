<?php

/**
 * Create Key
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_WebserviceApps_CreateApp_View extends Settings_Vtiger_BasicModal_View
{

	public function checkPermission(Vtiger_Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser()) {
			throw new \Exception\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	public function getSize(Vtiger_Request $request)
	{
		return 'modal-lg';
	}

	public function process(Vtiger_Request $request)
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

	public function getModalScripts(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$scripts = array(
			"modules.Settings.$moduleName.resources.Edit",
		);
		$scriptInstances = $this->checkAndConvertJsScripts($scripts);
		return $scriptInstances;
	}
}
