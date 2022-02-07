<?php

/**
 * Login forgot password action file.
 *
 * @package   Action
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Login forgot password action class.
 */
class Users_LoginForgotPassword_Action extends Users_Login_Action
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$response = new Vtiger_Response();
		$bruteForceInstance = Settings_BruteForce_Module_Model::getCleanInstance();
		try {
			$moduleName = $request->getModule();
			if ($bruteForceInstance->isActive() && $bruteForceInstance->isBlockedIp()) {
				$bruteForceInstance->incAttempts();
				throw new \App\Exceptions\Security('LBL_IP_IS_BLOCKED', 406);
			}
			$email = $request->getByType('email', 'Email');
			$row = (new \App\Db\Query())->select(['id', 'login_method'])->from('vtiger_users')->where(['email1' => $email, 'status' => 'Active', 'deleted' => 0])->one();
			if (empty($row)) {
				$response->setError(406, \App\Language::translate('LBL_USER_MAIL_NOT_EXIST', 'Users'));
			} elseif ('PLL_LDAP' === $row['login_method'] || 'PLL_LDAP_2FA' === $row['login_method']) {
				$response->setError(406, \App\Language::translate('LBL_NOT_CHANGE_PASS_AUTH_EXTERNAL_SYSTEM', 'Users'));
			} else {
				$id = (int) $row['id'];
				$userRecordModel = Users_Record_Model::getInstanceFromFile($id);
				\App\User::setCurrentUserId($id);
				$expirationDate = date('Y-m-d H:i:s', strtotime('+1 hour'));
				$token = \App\Utils\Tokens::generate('Users_LoginForgotPassword_Action', [$id], $expirationDate);
				\App\Mailer::sendFromTemplate([
					'template' => 'UsersResetPassword',
					'moduleName' => $moduleName,
					'recordId' => $id,
					'to' => $userRecordModel->get('email1'),
					'url' => \Config\Main::$site_URL . 'index.php?module=Users&view=LoginPassChange&token=' . $token,
					'expirationDate' => \App\Fields\DateTime::formatToDisplay($expirationDate),
					'token' => $token,
					'siteUrl' => \Config\Main::$site_URL,
				]);
				$response->setResult(\App\Language::translate('LBL_PASSWORD_LINK_SENT', 'Users'));
			}
		} catch (\Throwable $exc) {
			$message = $exc->getMessage();
			if ($exc instanceof \App\Exceptions\AppException) {
				$message = $exc->getDisplayMessage();
			}
			\App\Log::warning($exc->getMessage() . PHP_EOL . $exc->__toString());
			$response->setError(400, $message);
			$bruteForceInstance->updateBlockedIp();
			if ($bruteForceInstance->isBlockedIp()) {
				$bruteForceInstance->sendNotificationEmail();
			} else {
				Users_Module_Model::getInstance($moduleName)->saveLoginHistory('', 'ERR_RESET_IP_BLOCK');
			}
		}
		$response->emit();
	}
}
