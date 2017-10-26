<?php

/**
 * Reset password modal view class
 * @package YetiForce.Modal
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Users_PasswordModal_View extends Vtiger_BasicModal_View
{

	/**
	 * {@inheritDoc}
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('reset');
		$this->exposeMethod('change');
	}

	/**
	 * {@inheritDoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		switch ($request->getMode()) {
			case 'reset':
				if ($currentUserModel->isAdminUser() === true || (AppConfig::security('SHOW_MY_PREFERENCES') && (int) $currentUserModel->get('id') === $request->getInteger('record'))) {
					return true;
				}
				break;
			case 'change':
				if ((int) $currentUserModel->get('id') === $request->getInteger('record')) {
					return true;
				}
				break;
		}
		throw new \App\Exceptions\AppException('LBL_PERMISSION_DENIED');
	}

	/**
	 * {@inheritDoc}
	 */
	public function process(\App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	/**
	 * Reset user password
	 */
	public function reset(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('MODE', 'reset');
		$viewer->assign('MODE_TITLE', 'LBL_RESET_PASSWORD_HEAD');
		$viewer->assign('RECORD', $request->getInteger('record'));
		$viewer->assign('ACTIVE_SMTP', App\Mail::getDefaultSmtp());
		$this->preProcess($request);
		$viewer->view('PasswordModal.tpl', $moduleName);
		$this->postProcess($request);
	}

	/**
	 * Change user password
	 */
	public function change(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('MODE', 'change');
		$viewer->assign('MODE_TITLE', 'LBL_CHANGE_PASSWORD');
		$viewer->assign('RECORD', $request->getInteger('record'));
		$this->preProcess($request);
		$viewer->view('PasswordModal.tpl', $moduleName);
		$this->postProcess($request);
	}
}
