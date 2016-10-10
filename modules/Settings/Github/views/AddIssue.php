<?php

/**
 * Show modal to add issue 
 * @package YetiForce.View
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_Github_AddIssue_View extends Vtiger_BasicModal_View
{

	public function checkPermission(Vtiger_Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser()) {
			throw new \Exception\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$qualifiedModule = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$clientModel = Settings_Github_Client_Model::getInstance();
		$configuration = Settings_ConfReport_Module_Model::getConfigurationValue();
		$libraries = Settings_ConfReport_Module_Model::getConfigurationLibrary();
		$errorLibraries = [];
		foreach ($libraries as $key => $value) {
			if ($value['status'] == 'LBL_NO') {
				$errorLibraries[$key] = $value;
			}
		}
		$errorConfig = [];
		foreach ($configuration as $key => $value) {
			if ($value['status']) {
				$errorConfig[$key] = $value;
			}
		}
		$phpVersion = PHP_VERSION;
		$viewer->assign('GITHUB_CLIENT_MODEL', $clientModel);
		$viewer->assign('PHP_VERSION', $phpVersion);
		$viewer->assign('ERROR_CONFIGURATION', $errorConfig);
		$viewer->assign('ERROR_LIBRARIES', $errorLibraries);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->view('AddIssueModal.tpl', $qualifiedModule);
	}
}
