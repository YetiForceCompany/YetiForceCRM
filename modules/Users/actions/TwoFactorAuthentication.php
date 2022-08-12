<?php

/**
 * Two factor authentication action class.
 *
 * @package   Action
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Users_TwoFactorAuthentication_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('secret');
		$this->exposeMethod('off');
		$this->exposeMethod('massOff');
	}

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if ('TOTP_OFF' === App\Config::security('USER_AUTHY_MODE')) {
			throw new \App\Exceptions\NoPermitted('ERR_PERMISSION_DENIED', 403);
		}
		$mode = $request->getMode();
		if ('off' === $mode && 'TOTP_OPTIONAL' !== App\Config::security('USER_AUTHY_MODE')) {
			throw new \App\Exceptions\NoPermitted('ERR_PERMISSION_DENIED', 403);
		}
		$userModel = \App\User::getCurrentUserModel();

		if ('massOff' === $mode && !$userModel->isAdmin()) {
			throw new \App\Exceptions\NoPermitted('ERR_PERMISSION_DENIED', 403);
		}
		if ('massOff' !== $mode && (\App\User::getCurrentUserRealId() !== $userModel->getId() || !\in_array($userModel->getDetail('login_method'), ['PLL_PASSWORD_2FA', 'PLL_LDAP_2FA']))) {
			throw new \App\Exceptions\NoPermitted('ERR_PERMISSION_DENIED', 403);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode) && $this->isMethodExposed($mode)) {
			return $this->{$mode}($request);
		}
	}

	/**
	 * Setting the secret code.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\IllegalValue
	 */
	public function secret(App\Request $request)
	{
		$secret = $request->getByType('secret', 'Alnum');
		try {
			$authMethod = new Users_Totp_Authmethod(\App\User::getCurrentUserRealId());
			$checkResult = $authMethod->verifyCode($secret, $request->getByType('user_code', \App\Purifier::DIGITS));
			if ($checkResult) {
				$userRecordModel = Users_Record_Model::getInstanceById(\App\User::getCurrentUserRealId(), 'Users');
				$userRecordModel->set('authy_secret_totp', $secret);
				$userRecordModel->set('authy_methods', 'PLL_AUTHY_TOTP');
				$userRecordModel->save();
				if (\App\Process::hasEvent('ShowAuthy2faModal')) {
					\App\Process::removeEvent('ShowAuthy2faModal');
				}
			}
		} catch (\Throwable $e) {
			$checkResult = false;
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'message' => \App\Language::translate('LBL_AUTHY_SECRET_TOTP_SUCCESS', 'Users'),
			'success' => $checkResult
		]);
		$response->emit();
	}

	/**
	 * Turning off the 2FA.
	 *
	 * @param \App\Request $request
	 */
	public function off(App\Request $request)
	{
		$userId = \App\User::getCurrentUserRealId();
		$userRecordModel = Users_Record_Model::getInstanceById($userId, 'Users');
		$userRecordModel->set('authy_secret_totp', '');
		$userRecordModel->set('authy_methods', '');
		$userRecordModel->save();
		$response = new Vtiger_Response();
		$response->setResult([
			'message' => \App\Language::translate('LBL_AUTHY_SECRET_TOTP_SUCCESS', 'Users'),
			'success' => true
		]);
		$response->emit();
	}

	/**
	 * Mass turning off the 2FA.
	 *
	 * @param \App\Request $request
	 */
	public function massOff(App\Request $request)
	{
		$recordsList = Vtiger_Mass_Action::getRecordsListFromRequest($request);
		foreach ($recordsList as $userId) {
			$userRecordModel = Users_Record_Model::getInstanceById($userId, 'Users');
			if ('PLL_AUTHY_TOTP' === $userRecordModel->get('authy_methods') && !empty($userRecordModel->get('authy_secret_totp'))) {
				$userRecordModel->set('authy_secret_totp', '');
				$userRecordModel->set('authy_methods', '');
				$userRecordModel->save();
			}
		}
		$response = new Vtiger_Response();
		$response->setResult([
			'message' => \App\Language::translate('LBL_AUTHY_SECRET_TOTP_SUCCESS', 'Users'),
			'success' => true
		]);
		$response->emit();
	}
}
