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
	use \App\Controller\ExposeMethod;

	/**
	 * {@inheritdoc}
	 */
	public $modalSize = 'modal-dialog-centered';

	/**
	 * {@inheritdoc}
	 */
	public $lockExit = ture;

	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		if (AppConfig::security('USER_AUTHY_MODE') === 'TOTP_OFF') {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$authMethod = new Users_Totp_Authmethod(\App\User::getCurrentUserRealId());
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('RECORD', \App\User::getCurrentUserRealId());
		$viewer->assign('SECRET', $authMethod->createSecret());
		$viewer->assign('QR_CODE_HTML', $authMethod->createQrCodeForUser());
		$viewer->assign('LOCK_EXIT', $this->lockExit);
		$viewer->view('TwoFactorAuthenticationModal.tpl', $moduleName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function preProcessAjax(\App\Request $request)
	{
		$this->modalIcon = 'fa fa-key';
		$this->pageTitle = \App\Language::translate('LBL_TWO_FACTOR_AUTHENTICATION', $request->getModule());
		$this->lockExit = AppConfig::security('USER_AUTHY_MODE') === 'TOTP_OBLIGATORY';
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
}
