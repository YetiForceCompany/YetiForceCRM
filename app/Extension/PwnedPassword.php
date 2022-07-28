<?php

/**
 * Pwned password file to check the password.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Extension;

/**
 * Pwned password class to check the password.
 */
class PwnedPassword
{
	/**
	 * Check the password function.
	 *
	 * @param string $password
	 *
	 * @return array ['message' => (string) , 'status' => (bool)]
	 */
	public static function check(string $password): array
	{
		return self::getDefaultProvider()->check($password);
	}

	/**
	 * Get all providers.
	 *
	 * @return \App\Extension\PwnedPassword\Base[]
	 */
	public static function getProviders(): array
	{
		$return = [];
		foreach (new \DirectoryIterator(ROOT_DIRECTORY . '/app/Extension/PwnedPassword/') as $item) {
			if ($item->isFile() && 'Base' !== $item->getBasename('.php')) {
				$fileName = $item->getBasename('.php');
				$className = "\\App\\Extension\\PwnedPassword\\$fileName";
				$instance = new $className();
				$return[$fileName] = $instance;
			}
		}
		return $return;
	}

	/**
	 * Get default provider.
	 *
	 * @return \App\Extension\PwnedPassword\Base
	 */
	public static function getDefaultProvider(): PwnedPassword\Base
	{
		$className = '\\App\\Extension\\PwnedPassword\\' . \App\Config::module('Users', 'pwnedPasswordProvider');
		if (!class_exists($className)) {
			throw new \App\Exceptions\AppException("ERR_CLASS_NOT_FOUND||{$className}");
		}
		return new $className();
	}

	/**
	 * UsersAfterLogin handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function usersAfterLogin(\App\EventHandler $eventHandler): void
	{
		if ('PASSWORD' === \App\Session::get('UserAuthMethod')) {
			$params = $eventHandler->getParams();
			self::afterLogin($params);
			if (!\App\Process::hasEvent('ShowUserPwnedPasswordChange')) {
				$eventHandler->getRecordModel()->verifyPasswordChange($params['userModel']);
			}
		}
	}

	/**
	 * Check the password after login.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public static function afterLogin(array $params): void
	{
		$file = ROOT_DIRECTORY . '/app_data/PwnedPassword.php';
		$userName = \App\Session::get('user_name');
		$config = \Settings_Password_Record_Model::getUserPassConfig();
		$time = (int) $config['pwned_time'] ?? 0;
		if ('false' === $config['pwned'] || !$time) {
			return;
		}
		$pwnedPassword = [];
		if (file_exists($file)) {
			$pwnedPassword = require $file;
		}
		if (empty($pwnedPassword['dates'][$userName]) || strtotime($pwnedPassword['dates'][$userName]) < strtotime("-$time day")) {
			if (($passStatus = self::check($params['password'])) && !$passStatus['status']) {
				$pwnedPassword['status'][$userName] = true;
				\App\Process::addEvent([
					'name' => 'ShowUserPwnedPasswordChange',
					'priority' => 4,
					'type' => 'modal',
					'url' => 'index.php?module=Users&view=PasswordModal&mode=change&type=pwned&record=' . $params['userModel']->getId(),
				]);
			}
			$pwnedPassword['dates'][$userName] = date('Y-m-d H:i:s');
			\App\Utils::saveToFile($file, $pwnedPassword, '', 0, true);
		} elseif (isset($pwnedPassword['status'][$userName]) && true === $pwnedPassword['status'][$userName]) {
			\App\Process::addEvent([
				'name' => 'ShowUserPwnedPasswordChange',
				'priority' => 4,
				'type' => 'modal',
				'url' => 'index.php?module=Users&view=PasswordModal&mode=change&type=pwned&record=' . $params['userModel']->getId(),
			]);
		}
	}

	/**
	 * UsersAfterPasswordChange handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function usersAfterPasswordChange(\App\EventHandler $eventHandler): void
	{
		if (\App\Process::hasEvent('ShowUserPwnedPasswordChange')) {
			\App\Process::removeEvent('ShowUserPwnedPasswordChange');
		}
		$file = ROOT_DIRECTORY . '/app_data/PwnedPassword.php';
		if (file_exists($file)) {
			$pwnedPassword = require $file;
			unset($pwnedPassword['status'][$eventHandler->getRecordModel()->get('user_name')]);
			\App\Utils::saveToFile($file, $pwnedPassword, '', 0, true);
		}
	}
}
