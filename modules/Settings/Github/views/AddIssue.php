<?php

/**
 * Show modal to add issue
 * @package YetiForce.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_Github_AddIssue_View extends Vtiger_BasicModal_View
{

	public function checkPermission(\App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	public function process(\App\Request $request)
	{
		$qualifiedModule = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$clientModel = Settings_Github_Client_Model::getInstance();
		$configuration = Settings_ConfReport_Module_Model::getStabilityConf();
		$libraries = Settings_ConfReport_Module_Model::getLibrary();
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
