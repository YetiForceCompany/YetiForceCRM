<?php

/**
 * Reset password modal view class.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Users_PasswordModal_View extends \App\Controller\Modal
{
	use \App\Controller\ExposeMethod;

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('reset');
		$this->exposeMethod('change');
		$this->exposeMethod('massReset');
	}

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		switch ($request->getMode()) {
			case 'reset':
				if (true === $currentUserModel->isAdminUser() || (int) $currentUserModel->get('id') === $request->getInteger('record')) {
					return true;
				}
				break;
			case 'change':
				if (\App\User::getCurrentUserId() === \App\User::getCurrentUserRealId() && (true === $currentUserModel->isAdminUser() || (int) $currentUserModel->get('id') === $request->getInteger('record'))) {
					return true;
				}
				break;
			case 'massReset':
				if (true === $currentUserModel->isAdminUser()) {
					return true;
				}
				break;
			default:
				break;
		}
		throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
	}

	/** {@inheritdoc} */
	public function getPageTitle(App\Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->getInteger('record');
		switch ($request->getMode()) {
			case 'change':
				$modeTitle = 'LBL_CHANGE_PASSWORD';
				$this->modalIcon = 'fas fa-key';
				break;
			case 'reset':
				$modeTitle = 'LBL_RESET_PASSWORD_HEAD';
				$this->modalIcon = 'fas fa-redo-alt';
				break;
			case 'massReset':
				$modeTitle = 'LBL_MASS_RESET_PASSWORD_HEAD';
				$this->modalIcon = 'fas fa-redo-alt';
				break;
			default:
				break;
		}
		$this->pageTitle = \App\Language::translate($modeTitle, $moduleName);
		if ($record) {
			$this->pageTitle .= ' - ' . App\Fields\Owner::getUserLabel($record);
		}

		return $this->pageTitle;
	}

	/** {@inheritdoc} */
	public function preProcessAjax(App\Request $request)
	{
		if (App\User::getCurrentUserId() === $request->getInteger('record')
			&& (1 === (int) App\User::getCurrentUserModel()->getDetail('force_password_change')
			|| 'pwned' === $request->getByType('type')
			|| 2 === (int) \App\Session::get('ShowUserPasswordChange'))
		) {
			$this->lockExit = true;
		}
		parent::preProcessAjax($request);
	}

	/**
	 * Reset user password.
	 *
	 * @param \App\Request $request
	 */
	public function reset(App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('MODE', 'reset');
		$viewer->assign('MODE_TITLE', 'LBL_RESET_PASSWORD_HEAD');
		$viewer->assign('RECORD', $request->getInteger('record'));
		$viewer->assign('ACTIVE_SMTP', App\Mail::getDefaultSmtp());
		$viewer->view('Modals/PasswordModal.tpl', $moduleName);
	}

	/**
	 * Change user password.
	 *
	 * @param \App\Request $request
	 */
	public function change(App\Request $request)
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
			if (1 === (int) $userModel->getDetail('force_password_change')) {
				$viewer->assign('WARNING', \App\Language::translate('LBL_FORCE_PASSWORD_CHANGE_ALERT', $moduleName));
			} elseif ('pwned' === $request->getByType('type')) {
				$viewer->assign('WARNING', \App\Language::translate('LBL_PWNED_PASSWORD_CHANGE_ALERT', $moduleName));
			} else {
				switch ((int) \App\Session::get('ShowUserPasswordChange')) {
					case 1:
						$time = (int) $passConfig['change_time'] + (int) $passConfig['lock_time'];
						$viewer->assign('WARNING', \App\Language::translateArgs('LBL_YOUR_PASSWORD_WILL_EXPIRE', $moduleName, \App\Fields\DateTime::getDiff(date('Y-m-d'), date('Y-m-d', strtotime("+$time day", strtotime($userModel->getDetail('date_password_change')))), 'days')));
						\App\Session::delete('ShowUserPasswordChange');
						break;
					case 2:
						$viewer->assign('WARNING', \App\Language::translate('LBL_YOUR_PASSWORD_HAS_EXPIRED', $moduleName));
						break;
					default: break;
				}
			}
		} else {
			$viewer->assign('WARNING', \App\Language::translate('LBL_CHANGING_PASSWORD_OF_ANOTHER_USER', $moduleName));
		}
		$viewer->view('Modals/PasswordModal.tpl', $moduleName);
	}

	/**
	 * Mass reset user password.
	 *
	 * @param \App\Request $request
	 */
	public function massReset(App\Request $request)
	{
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('RECORD', null);
		$viewer->assign('MODE', 'massReset');
		$viewer->assign('MODE_TITLE', 'LBL_MASS_RESET_PASSWORD_HEAD');
		$viewer->assign('ACTIVE_SMTP', App\Mail::getDefaultSmtp());
		$viewer->assign('SELECTED_IDS', $request->getArray('selected_ids', 2));
		$viewer->assign('EXCLUDED_IDS', $request->getArray('excluded_ids', 2));
		$viewer->assign('SEARCH_PARAMS', App\Condition::validSearchParams($moduleName, $request->getArray('search_params'), false));
		$viewer->view('Modals/PasswordModal.tpl', $moduleName);
	}

	/** {@inheritdoc} */
	public function postProcessAjax(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		if (!$request->getBoolean('onlyBody')) {
			$viewer->view('Modals/PasswordModalFooter.tpl', $request->getModule());
		}
	}
}
