<?php
/**
 * Two factor authentication modal view class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Users_TwoFactorAuthenticationModal_View extends Vtiger_BasicModal_View
{
	use \App\Controller\ExposeMethod;

	/**
	 * {@inheritdoc}
	 */
	public function __construct()
	{
		parent::__construct();
	}

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
		$userRecordModel = Users_Record_Model::getInstanceById(\App\User::getCurrentUserRealId(), $moduleName);
		$secret = Users_Totp_Authmethod::createSecret();
		$otpAuthUrl = Users_Totp_Authmethod::getOtpAuthUrl($secret, $userRecordModel->getDisplayName());
		$moduleName = $request->getModule();

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('MODE_TITLE', 'LBL_RESET_PASSWORD_HEAD');
		$viewer->assign('RECORD', $request->getInteger('record'));
		$viewer->assign('SECRET', $secret);
		$viewer->assign('QR_CODE_HTML', Users_Totp_Authmethod::createQrCode($otpAuthUrl));
		$viewer->assign('LOCK_EXIT', AppConfig::security('USER_AUTHY_MODE') === 'TOTP_OBLIGATORY');
		$this->preProcess($request);
		$viewer->view('TwoFactorAuthenticationModal.tpl', $moduleName);
		$this->postProcess($request);
	}
}
