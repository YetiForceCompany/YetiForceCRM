<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Users_Login_View extends \App\Controller\View
{
	/**
	 * {@inheritdoc}
	 */
	public function loginRequired()
	{
		return false;
	}

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
	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request, false);
		$viewer = $this->getViewer($request);

		$selectedModule = $request->getModule();
		$viewer->assign('MODULE', $selectedModule);
		$viewer->assign('MODULE_NAME', $selectedModule);
		$viewer->assign('QUALIFIED_MODULE', $selectedModule);
		$viewer->assign('VIEW', $request->getByType('view'));
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		if ($display) {
			$this->preProcessDisplay($request);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function postProcess(\App\Request $request, $display = true)
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $request->getModule());
		$viewer->assign('IS_BLOCKED_IP', Settings_BruteForce_Module_Model::getCleanInstance()->isBlockedIp());
		if (\App\Session::has('UserLoginMessage')) {
			$viewer->assign('MESSAGE', \App\Session::get('UserLoginMessage'));
			$viewer->assign('MESSAGE_TYPE', \App\Session::get('UserLoginMessageType'));
			\App\Session::delete('UserLoginMessage');
			\App\Session::delete('UserLoginMessageType');
		}
		if (\App\Session::get('LoginAuthyMethod') === '2fa') {
			$viewer->view('Login2faTotp.tpl', 'Users');
		} else {
			$viewer->assign('LANGUAGE_SELECTION', AppConfig::main('langInLoginView'));
			$viewer->assign('LAYOUT_SELECTION', AppConfig::main('layoutInLoginView'));
			$viewer->view('Login.tpl', 'Users');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaderCss(\App\Request $request)
	{
		return array_merge(parent::getHeaderCss($request), $this->checkAndConvertCssStyles([
			'modules.Users.Login'
		]));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaderScripts(\App\Request $request)
	{
		return array_merge(parent::getHeaderScripts($request), $this->checkAndConvertJsScripts([
			'~libraries/device-uuid/lib/device-uuid.js'
		]));
	}
}
