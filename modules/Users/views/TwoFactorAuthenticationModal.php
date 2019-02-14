<?php

/**
 * Two factor authentication modal view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Users_TwoFactorAuthenticationModal_View extends \App\Controller\Modal
{
	/**
	 * {@inheritdoc}
	 */
	public $modalSize = 'modal-dialog-centered';

	/**
	 * {@inheritdoc}
	 */
	public $lockExit = true;

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		if (\App\Config::security('USER_AUTHY_MODE') === 'TOTP_OFF') {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
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
		$viewer->assign('IS_INIT', !empty($userModel->getDetail('authy_secret_totp')));
		$viewer->view('TwoFactorAuthenticationModal.tpl', $moduleName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function preProcessAjax(\App\Request $request)
	{
		$this->modalIcon = 'fa fa-key';
		$this->pageTitle = \App\Language::translate('LBL_TWO_FACTOR_AUTHENTICATION', $request->getModule());
		$this->lockExit = \App\Config::security('USER_AUTHY_MODE') === 'TOTP_OBLIGATORY';
		parent::preProcessAjax($request);
	}

	/**
	 * {@inheritdoc} - Override parent method for custom footer
	 */
	public function postProcessAjax(\App\Request $request)
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function getModalScripts(\App\Request $request)
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
		if (\App\Config::security('USER_AUTHY_MODE') === 'TOTP_OPTIONAL') {
			return !empty(\App\User::getUserModel(\App\User::getCurrentUserRealId())->getDetail('authy_secret_totp'));
		}
		return false;
	}
}
