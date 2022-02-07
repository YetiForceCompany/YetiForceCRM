<?php

/**
 * Two factor authentication modal view class.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Users_TwoFactorAuthenticationModal_View extends \App\Controller\Modal
{
	/** {@inheritdoc} */
	public $modalSize = 'modal-lg';

	/** {@inheritdoc} */
	public $lockExit = true;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if ('TOTP_OFF' === \App\Config::security('USER_AUTHY_MODE') || \App\User::getCurrentUserRealId() !== \App\User::getCurrentUserId()) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		return true;
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$userModel = \App\User::getUserModel(\App\User::getCurrentUserRealId());
		$moduleName = $request->getModule();
		$authMethod = new Users_Totp_Authmethod(\App\User::getCurrentUserRealId());
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('RECORD', \App\User::getCurrentUserRealId());
		$viewer->assign('SECRET', $authMethod->createSecret());
		$viewer->assign('QR_CODE_HTML', $authMethod->createQrCodeForUser());
		$viewer->assign('LOCK_EXIT', $this->lockExit);
		$viewer->assign('SHOW_OFF', $this->showOff());
		$viewer->assign('SECRET_OLD', $userModel->getDetail('authy_secret_totp'));
		$viewer->view('TwoFactorAuthenticationModal.tpl', $moduleName);
	}

	/** {@inheritdoc} */
	public function preProcessAjax(App\Request $request)
	{
		$userModel = \App\User::getCurrentUserModel();
		$this->modalIcon = 'fa fa-key';
		$this->pageTitle = \App\Language::translate('LBL_TWO_FACTOR_AUTHENTICATION', $request->getModule());
		$this->lockExit = 'TOTP_OBLIGATORY' === \App\Config::security('USER_AUTHY_MODE') && (empty($userModel->getDetail('authy_secret_totp')) || empty($userModel->getDetail('authy_methods')));
		parent::preProcessAjax($request);
	}

	/**
	 * {@inheritdoc} - Override parent method for custom footer
	 */
	public function postProcessAjax(App\Request $request)
	{
	}

	/** {@inheritdoc} */
	public function getModalScripts(App\Request $request)
	{
		return array_merge(parent::getModalScripts($request), $this->checkAndConvertJsScripts([
			'modules.Users.resources.TwoFactorAuthenticationModal'
		]));
	}

	/**
	 * Check if the user can disable 2FA.
	 *
	 * @return bool
	 */
	private function showOff()
	{
		$userModel = \App\User::getCurrentUserModel();
		return 'TOTP_OPTIONAL' === \App\Config::security('USER_AUTHY_MODE') && !empty($userModel->getDetail('authy_secret_totp')) && $userModel->getDetail('authy_methods');
	}
}
