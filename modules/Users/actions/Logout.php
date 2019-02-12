<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Users_Logout_Action extends \App\Controller\Action
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$eventHandler = new App\EventHandler();
		$eventHandler->trigger('UserLogoutBefore');
		if (AppConfig::main('session_regenerate_id')) {
			App\Session::regenerateId(true); // to overcome session id reuse.
		}
		OSSMail_Logout_Model::logoutCurrentUser();
		App\Session::destroy();

		//Track the logout History
		$moduleName = $request->getModule();
		$moduleModel = Users_Module_Model::getInstance($moduleName);
		$moduleModel->saveLogoutHistory();
		//End
		header('location: index.php');
	}

	/**
	 * {@inheritdoc}
	 */
	public function validateRequest(\App\Request $request)
	{
		$request->validateReadAccess();
	}
}
