<?php

/**
 * Login history modal view class.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class Users_LoginHistoryModal_View extends \App\Controller\Modal
{
	use \App\Controller\ExposeMethod;

	/**
	 * Columns to show on the list.
	 *
	 * @var array
	 */
	public static $columnsToShow = [
		'user_ip' => 'LBL_USER_IP_ADDRESS',
		'login_time' => 'LBL_LOGIN_TIME',
		'logout_time' => 'LBL_LOGGED_OUT_TIME',
		'status' => 'LBL_STATUS'
	];

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (true === $currentUserModel->isAdminUser() || (int) $currentUserModel->get('id') === $request->getInteger('record')) {
			return true;
		}
		throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
	}

	/**
	 * {@inheritdoc}
	 */
	public function preProcessAjax(App\Request $request)
	{
		$moduleName = $request->getModule();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$this->modalIcon = 'mdi mdi-lock-reset';
		$this->pageTitle = \App\Language::translate('LBL_LOGIN_HISTORY', $moduleName) . ' - ' . App\Fields\Owner::getUserLabel($currentUserModel->get('id'));
		parent::preProcessAjax($request);
	}

	/**
	 * Change user password.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('TABLE_COLUMNS', static::$columnsToShow);
		$viewer->assign('LOGIN_HISTORY_ENTRIES', \Users_Module_Model::getLoginHistory());
		$viewer->view('Modals/LoginHistoryModal.tpl', $moduleName);
	}
}
