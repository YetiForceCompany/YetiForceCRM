<?php
/**
 * Users cli file.
 *
 * @package Cli
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Cli;

/**
 * Users cli class.
 */
class Users extends Base
{
	/** {@inheritdoc} */
	public $moduleName = 'Users';

	/** @var string[] Methods list */
	public $methods = [
		'resetPassword' => 'Reset user password',
		'passwordAuth' => 'Disable 2FA or LDAP auth',
		'resetAllPasswords' => 'Reset all user passwords',
	];

	/**
	 * Reset user password.
	 *
	 * @return void
	 */
	public function resetPassword(): void
	{
		$this->climate->arguments->add([
			'login' => [
				'prefix' => 'l',
				'description' => 'Login/User name',
			],
			'password' => [
				'prefix' => 'p',
				'description' => 'New password',
			],
			'confirmation' => [
				'prefix' => 'c',
				'description' => 'Don\'t ask for confirmation',
			],
		]);
		if ($this->helpMode) {
			return;
		}
		$this->climate->arguments->parse();
		if ($this->climate->arguments->defined('login')) {
			$userName = $this->climate->arguments->get('login');
		} else {
			$input = $this->climate->input('Enter login/username:');
			$userName = $input->prompt();
		}
		$row = (new \App\Db\Query())->select(['id', 'deleted'])->from('vtiger_users')->where(['or', ['user_name' => $userName], ['user_name' => strtolower($userName)]])->limit(1)->one();
		if (!$row) {
			$this->climate->red('User not found');
			if ($this->climate->arguments->defined('confirmation') || $this->climate->confirm('Re-enter login?')->confirmed()) {
				$this->resetPassword();
			} else {
				$this->cli->actionsList('Users');
			}
			return;
		}
		$userRecordModel = \Users_Record_Model::getInstanceById($row['id'], 'Users');
		$this->climate->lightBlue($userRecordModel->getDisplayName() . ' (' . $userRecordModel->getDisplayValue('roleid', false, true) . ')');
		if (0 !== (int) $row['deleted']) {
			$this->climate->lightGreen('User inactive!!!');
		}
		if ($this->climate->arguments->defined('password')) {
			$password = $this->climate->arguments->get('password');
		} else {
			$password = \App\Encryption::generateUserPassword();
			$this->climate->lightGreen('New password: ' . $password);
		}
		$userRecordModel->set('changeUserPassword', true);
		$userRecordModel->set('user_password', $password);
		$userRecordModel->set('date_password_change', date('Y-m-d H:i:s'));

		$eventHandler = new \App\EventHandler();
		$eventHandler->setRecordModel($userRecordModel);
		$eventHandler->setModuleName('Users');
		$eventHandler->setParams(['action' => 'reset']);
		$eventHandler->trigger('UsersBeforePasswordChange');
		$userRecordModel->save();
		$eventHandler->trigger('UsersAfterPasswordChange');
		if ($this->climate->arguments->defined('confirmation') || $this->climate->confirm('Send password reset link to user\'s email address?')->confirmed()) {
			$expirationDate = date('Y-m-d H:i:s', strtotime('+24 hour'));
			$token = \App\Utils\Tokens::generate('Users_LoginForgotPassword_Action', [$userRecordModel->getId()], $expirationDate);
			\App\Mailer::sendFromTemplate([
				'template' => 'UsersResetPassword',
				'moduleName' => 'Users',
				'recordId' => $userRecordModel->getId(),
				'to' => $userRecordModel->get('email1'),
				'url' => \Config\Main::$site_URL . 'index.php?module=Users&view=LoginPassChange&token=' . $token,
				'expirationDate' => \App\Fields\DateTime::formatToDisplay($expirationDate),
				'token' => $token,
			]);
		}
		if (!$this->climate->arguments->defined('action')) {
			$this->cli->actionsList('Users');
		}
	}

	/**
	 * Reset all user passwords.
	 *
	 * @return void
	 */
	public function resetAllPasswords(): void
	{
		$this->climate->arguments->add([
			'confirmation' => [
				'prefix' => 'c',
				'description' => 'Don\'t ask for confirmation',
			],
		]);
		if ($this->helpMode) {
			return;
		}
		$this->climate->lightBlue('New passwords will be sent to the user\'s e-mail, it is required that the e-mail sending works properly.');
		if (!$this->climate->arguments->defined('confirmation') && !$this->climate->confirm('Do you want to reset the passwords of all active users?')->confirmed()) {
			$this->cli->actionsList('Users');
			return;
		}
		$userIds = (new \App\Db\Query())->select(['id'])->from('vtiger_users')->where(['deleted' => 0])->column();
		$progress = $this->climate->progress()->total(\count($userIds));
		$i = 0;
		foreach ($userIds as $userId) {
			$userRecordModel = \Users_Record_Model::getInstanceById($userId, 'Users');
			$userRecordModel->set('changeUserPassword', true);
			$userRecordModel->set('user_password', \App\Encryption::generateUserPassword());
			$userRecordModel->set('date_password_change', date('Y-m-d H:i:s'));

			$eventHandler = new \App\EventHandler();
			$eventHandler->setRecordModel($userRecordModel);
			$eventHandler->setModuleName('Users');
			$eventHandler->setParams(['action' => 'massReset']);
			$eventHandler->trigger('UsersBeforePasswordChange');
			$userRecordModel->save();
			$eventHandler->trigger('UsersAfterPasswordChange');

			$expirationDate = date('Y-m-d H:i:s', strtotime('+24 hour'));
			$token = \App\Utils\Tokens::generate('Users_LoginForgotPassword_Action', [$userRecordModel->getId()], $expirationDate);
			\App\Mailer::sendFromTemplate([
				'template' => 'UsersResetPassword',
				'moduleName' => 'Users',
				'recordId' => $userRecordModel->getId(),
				'to' => $userRecordModel->get('email1'),
				'url' => \Config\Main::$site_URL . 'index.php?module=Users&view=LoginPassChange&token=' . $token,
				'expirationDate' => \App\Fields\DateTime::formatToDisplay($expirationDate),
				'token' => $token,
			]);
			$progress->advance();
			++$i;
		}
		$this->climate->lightGreen('Number of passwords reset: ' . $i);
	}

	/**
	 * Disable 2FA or LDAP auth of any user.
	 *
	 * @return void
	 */
	public function passwordAuth(): void
	{
		$this->climate->arguments->add([
			'login' => [
				'prefix' => 'l',
				'description' => 'Login/User name',
			],
		]);
		if ($this->helpMode) {
			return;
		}
		$this->climate->arguments->parse();
		if ($this->climate->arguments->defined('login')) {
			$userName = $this->climate->arguments->get('login');
		} else {
			$input = $this->climate->input('Enter login/username:');
			$userName = $input->prompt();
		}
		$row = (new \App\Db\Query())->select(['id', 'deleted'])->from('vtiger_users')->where(['or', ['user_name' => $userName], ['user_name' => strtolower($userName)]])->limit(1)->one();
		if (!$row) {
			$this->climate->red('User not found');
			if ($this->climate->confirm('Re-enter login?')->confirmed()) {
				$this->passwordAuth();
			} else {
				$this->cli->actionsList('Users');
			}
			return;
		}
		$userRecordModel = \Users_Record_Model::getInstanceById($row['id'], 'Users');
		$this->climate->lightBlue($userRecordModel->getDisplayName() . ' (' . $userRecordModel->getDisplayValue('roleid', false, true) . ')');
		if (0 !== (int) $row['deleted']) {
			$this->climate->lightGreen('User inactive!!!');
		}
		$userRecordModel->set('login_method', 'PLL_PASSWORD');
		$userRecordModel->set('authy_secret_totp', '');
		$userRecordModel->set('authy_methods', '');
		$userRecordModel->save();
		if (!$this->climate->arguments->defined('action')) {
			$this->cli->actionsList('Users');
		}
	}
}
