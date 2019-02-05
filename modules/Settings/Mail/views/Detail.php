<?php

/**
 * Mail edit view.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_Mail_Detail_View extends Settings_Vtiger_Index_View
{
	/**
	 * Page title.
	 *
	 * @var type
	 */
	protected $pageTitle = 'LBL_MAIL_QUEUE_PAGE_TITLE';

	/**
	 * Checking permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedForAdmin
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserModel = \App\User::getCurrentUserModel();
		if (!$currentUserModel->isAdmin() || $request->isEmpty('record')) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$record = $request->getInteger('record');
		$qualifiedModuleName = $request->getModule(false);
		$recordModel = Settings_Mail_Record_Model::getInstance($record);
		$viewer = $this->getViewer($request);
		if ($recordModel === false) {
			$moduleModel = new Settings_Mail_Module_Model();
			$viewer->assign('MODULE_MODEL', $moduleModel);
		}
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('DetailView.tpl', $qualifiedModuleName);
	}
}
