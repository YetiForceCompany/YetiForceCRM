<?php

/**
 * Mail edit view
 * @package YetiForce.Settings.View
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_MailSmtp_Detail_View extends Settings_Vtiger_Index_View
{

	/**
	 * Checking permission 
	 * @param \App\Request $request
	 * @throws \Exception\NoPermittedForAdmin
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserModel = \App\User::getCurrentUserModel();
		if (!$currentUserModel->isAdmin() || empty($request->get('record'))) {
			throw new \Exception\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}
	
	/**
	 * Process
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$record = $request->get('record');
		$qualifiedModuleName = $request->getModule(false);
		$recordModel = Settings_MailSmtp_Record_Model::getInstanceById($record);

		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('DetailView.tpl', $qualifiedModuleName);
	}


}
