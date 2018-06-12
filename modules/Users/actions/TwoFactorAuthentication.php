<?php

/**
 * Two factor authentication action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Users_TwoFactorAuthentication_Action extends \App\Controller\Action
{
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
		$secret = $request->getByType('secret', 'Alnum');
		$userCode = $request->getInteger('user_code');
		$checkResult = Users_Totp_Authmethod::verifyCode($secret, $userCode);
		if ($checkResult) {
			$userRecordModel = Users_Record_Model::getInstanceById(\App\User::getCurrentUserRealId(), $moduleName);
			$userRecordModel->set('authy_secret_totp', $secret);
			$userRecordModel->set('authy_methods', 'PLL_AUTHY_TOTP');
			$userRecordModel->save();
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'message'=> \App\Language::translate('LBL_AUTHY_SECRET_TOTP_SUCCESS', 'Users'),
			'success' => $checkResult
		]);
		$response->emit();
	}
}
