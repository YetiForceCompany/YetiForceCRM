<?php

/**
 * Show modal to add issue.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_Github_AddIssue_View extends Vtiger_BasicModal_View
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedForAdmin
	 */
	public function checkPermission(\App\Request $request)
	{
		if (!\App\User::getCurrentUserModel()->isAdmin()) {
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
			if ($value['incorrect']) {
				$errorConfig[$key] = $value;
			}
		}
		$viewer->assign('GITHUB_CLIENT_MODEL', $clientModel);
		$viewer->assign('PHP_VERSION', PHP_VERSION);
		$viewer->assign('ERROR_CONFIGURATION', $errorConfig);
		$viewer->assign('ERROR_LIBRARIES', $errorLibraries);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->view('AddIssueModal.tpl', $qualifiedModule);
	}
}
