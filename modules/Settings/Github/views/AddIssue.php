<?php

/**
 * Show modal to add issue.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
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
		$viewer->assign('GITHUB_CLIENT_MODEL', $clientModel);
		$viewer->assign('PHP_VERSION', PHP_VERSION);
		$viewer->assign('CONF_REPORT', \App\Utils\ConfReport::getAll());
		$viewer->assign('BROWSER_INFO', $request->getServer('HTTP_USER_AGENT'));
		$viewer->assign('ERROR_STABILITY', \App\Utils\ConfReport::get('stability', true));
		$viewer->assign('ERROR_ENVIRONMENT', \App\Utils\ConfReport::get('environment', true));
		$viewer->assign('ERROR_WRITE', \App\Utils\ConfReport::get('writableFilesAndFolders', true));
		$viewer->assign('ERROR_DATABASE', \App\Utils\ConfReport::get('database', true));
		$viewer->assign('ERROR_SECURITY', \App\Utils\ConfReport::get('security', true));
		$viewer->assign('ERROR_LIBRARIES', \App\Utils\ConfReport::get('libraries', true));
		$viewer->assign('ERROR_PERFORMANCE', \App\Utils\ConfReport::get('performance', true));
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->view('AddIssueModal.tpl', $qualifiedModule);
	}
}
