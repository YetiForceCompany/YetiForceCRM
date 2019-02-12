<?php

/**
 * Reset password modal view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Users_PasswordModal_View extends \App\Controller\Modal
{
	use \App\Controller\ExposeMethod;

	/**
	 * {@inheritdoc}
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('reset');
		$this->exposeMethod('change');
		$this->exposeMethod('massReset');
	}

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		switch ($request->getMode()) {
			case 'reset':
			case 'change':
				if ($currentUserModel->isAdminUser() === true || (int) $currentUserModel->get('id') === $request->getInteger('record')) {
					return true;
				}
				break;
			case 'massReset':
				if ($currentUserModel->isAdminUser() === true) {
					return true;
				}
				break;
			default:
				break;
		}
		throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
	}

	/**
	 * {@inheritdoc}
	 */
	public function preProcessAjax(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->getInteger('record');
		switch ($request->getMode()) {
			case 'change':
				$modeTitle = 'LBL_CHANGE_PASSWORD';
				$this->modalIcon = 'fas fa-key mr-1';
				break;
			case 'reset':
				$modeTitle = 'LBL_RESET_PASSWORD_HEAD';
				$this->modalIcon = 'fas fa-redo-alt mr-1';
				break;
			case 'massReset':
				$modeTitle = 'LBL_MASS_RESET_PASSWORD_HEAD';
				$this->modalIcon = 'fas fa-redo-alt mr-1';
				break;
			default:
				break;
		}
		$title = \App\Language::translate($modeTitle, $moduleName);
		if ($record) {
			$title .= ' - ' . App\Fields\Owner::getUserLabel($record);
		}
		$this->pageTitle = $title;
		parent::preProcessAjax($request);
	}

	/**
	 * Reset user password.
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
		$viewer->view('PasswordModal.tpl', $moduleName);
	}

	/**
	 * Change user password.
	 */
	public function change(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('WARNING', '');
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('MODE', 'change');
		$viewer->assign('MODE_TITLE', 'LBL_CHANGE_PASSWORD');
		$viewer->assign('RECORD', $request->getInteger('record'));
		$passConfig = \Settings_Password_Record_Model::getUserPassConfig();
		$viewer->assign('PASS_CONFIG', $passConfig);
		if (App\User::getCurrentUserId() === $request->getInteger('record')) {
			$userModel = App\User::getCurrentUserModel();
			if ((int) $userModel->getDetail('force_password_change') === 1) {
				$this->modalClass = 'static';
				$viewer->assign('LOCK_EXIT', true);
				$viewer->assign('WARNING', \App\Language::translate('LBL_FORCE_PASSWORD_CHANGE_ALERT', 'Users'));
			} else {
				$time = (int) $passConfig['change_time'];
				if ($time !== 0) {
					$time += (int) $passConfig['lock_time'];
					if (date('Y-m-d') > date('Y-m-d', strtotime("+{$passConfig['change_time']} day", strtotime($userModel->getDetail('date_password_change'))))) {
						$viewer->assign('WARNING', \App\Language::translateArgs('LBL_YOUR_PASSWORD_WILL_EXPIRE', $moduleName, \App\Fields\Date::getDiff(date('Y-m-d'), date('Y-m-d', strtotime("+$time day", strtotime($userModel->getDetail('date_password_change')))), 'days')));
					}
				}
			}
		} else {
			$viewer->assign('WARNING', \App\Language::translate('LBL_CHANGING_PASSWORD_OF_ANOTHER_USER', 'Users'));
		}
		$viewer->view('PasswordModal.tpl', $moduleName);
	}

	/**
	 * Mass reset user password.
	 */
	public function massReset(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('MODE', 'massReset');
		$viewer->assign('MODE_TITLE', 'LBL_MASS_RESET_PASSWORD_HEAD');
		$viewer->assign('ACTIVE_SMTP', App\Mail::getDefaultSmtp());
		$viewer->assign('SELECTED_IDS', $request->getArray('selected_ids', 2));
		$viewer->assign('EXCLUDED_IDS', $request->getArray('excluded_ids', 2));
		$viewer->assign('SEARCH_PARAMS', App\Condition::validSearchParams($moduleName, $request->getArray('search_params')));
		$viewer->view('PasswordModal.tpl', $moduleName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function postProcessAjax(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		if (!$request->getBoolean('onlyBody')) {
			$viewer->view('Modals/PasswordModalFooter.tpl', $request->getModule());
		}
	}
}
