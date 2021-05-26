<?php

/**
 * Login password change action file.
 *
 * @package   Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Login password change action class.
 */
class Users_LoginPassChange_Action extends Users_Login_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$response = new Vtiger_Response();
		$bruteForceInstance = Settings_BruteForce_Module_Model::getCleanInstance();
		try {
			$moduleName = $request->getModule();
			$bruteForceInstance = Settings_BruteForce_Module_Model::getCleanInstance();
			if ($bruteForceInstance->isActive() && $bruteForceInstance->isBlockedIp()) {
				$bruteForceInstance->incAttempts();
				throw new \App\Exceptions\Security('LBL_IP_IS_BLOCKED', 406);
			}
			if ($request->isEmpty('token')) {
				throw new \App\Exceptions\Security('ERR_NO_TOKEN', 405);
			}
			$token = $request->getByType('token', \App\Purifier::ALNUM);
			$tokenData = \App\Utils\Tokens::get($token);
			if (empty($tokenData)) {
				throw new \App\Exceptions\Security('ERR_TOKEN_DOES_NOT_EXIST', 405);
			}
			$password = $request->getRaw('password');
			if ($password !== $request->getRaw('confirm_password')) {
				$result = ['notify' => ['text' => \App\Language::translate('LBL_PASSWORD_SHOULD_BE_SAME', 'Users'), 'type' => 'error']];
			} else {
				$userRecordModel = Users_Record_Model::getInstanceById($tokenData['params'][0], $moduleName);
				$userRecordModel->set('changeUserPassword', true);
				$userRecordModel->set('user_password', $password);
				$userRecordModel->set('date_password_change', date('Y-m-d H:i:s'));
				$eventHandler = new \App\EventHandler();
				$eventHandler->setRecordModel($userRecordModel);
				$eventHandler->setModuleName('Users');
				$eventHandler->setParams(['action' => 'change']);
				$eventHandler->trigger('UsersBeforePasswordChange');
				$userRecordModel->save();
				$eventHandler->trigger('UsersAfterPasswordChange');
				$result = ['notify' => ['text' => \App\Language::translate('LBL_PASSWORD_SUCCESSFULLY_CHANGED', 'Users'), 'type' => 'success']];
			}
			$response->setResult($result);
		} catch (\Throwable $exc) {
			$message = $exc->getMessage();
			if ($exc instanceof \App\Exceptions\AppException) {
				$message = $exc->getDisplayMessage();
			}
			\App\Log::warning($exc->getMessage() . PHP_EOL . $exc->__toString());
			$response->setError(400, ['notify' => ['text' => $message, 'type' => 'error']]);
			$bruteForceInstance->updateBlockedIp();
			if ($bruteForceInstance->isBlockedIp()) {
				$bruteForceInstance->sendNotificationEmail();
			} else {
				Users_Module_Model::getInstance($moduleName)->saveLoginHistory('', 'ERR_PASS_CHANGE_IP_BLOCK');
			}
		}
		$response->emit();
	}
}
