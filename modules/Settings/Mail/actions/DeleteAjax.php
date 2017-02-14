<?php

/**
 * Mail delete action model class
 * @package YetiForce.Settings.Action
 * @license licenses/License.html
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Settings_Mail_DeleteAjax_Action extends Settings_Vtiger_Delete_Action
{
	/**
	 * Checking permission 
	 * @param Vtiger_Request $request
	 * @throws \Exception\NoPermittedForAdmin
	 */
	public function checkPermission(Vtiger_Request $request)
	{
		$currentUserModel = \App\User::getCurrentUserModel();
		if (!$currentUserModel->isAdmin()) {
			throw new \Exception\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}
	
	/**
	 * Process
	 * @param Vtiger_Request $request
	 */
	public function process(Vtiger_Request $request)
	{
		$record = $request->get('record');
		$qualifiedModuleName = $request->getModule(false);
		$recordModel = Settings_Mail_Record_Model::getInstance($record);
		$recordModel->delete();

		$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
		header("Location: {$moduleModel->getDefaultUrl()}");
	}
	
	/**
	 * Validate Request
	 * @param Vtiger_Request $request
	 */
	public function validateRequest(Vtiger_Request $request)
	{
		$request->validateReadAccess();
	}
}
