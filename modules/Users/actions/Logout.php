<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

namespace Modules\Users\Actions;

class Logout extends \App\Controller\Action
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission()
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function process()
	{
		$response = new \App\Response();
		$eventHandler = new \App\EventHandler();
		$eventHandler->trigger('UserLogoutBefore');
		if (\App\Config::main('session_regenerate_id')) {
			\App\Session::regenerateId(true); // to overcome session id reuse.
		}
		\OSSMail_Logout_Model::logoutCurrentUser();
		\App\Session::destroy();

		$moduleName = $this->request->getModule();
		$moduleModel = \Users_Module_Model::getInstance($moduleName);
		$moduleModel->saveLogoutHistory();
		$response->setResult(!\App\Session::has('authenticated_user_id'));
		return $response;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validateRequest()
	{
		$this->request->validateReadAccess();
	}
}
