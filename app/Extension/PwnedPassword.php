<?php

/**
 * Pwned password file to check the password.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
	 * @return []\App\Extension\PwnedPassword\Base
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
		$recordModel = $eventHandler->getRecordModel();
		$params = $eventHandler->getParams();
		if ('PASSWORD' === \App\Session::get('UserAuthMethod')) {
			self::afterLogin($params['password']);
			if (!\App\Session::has('ShowUserPwnedPasswordChange')) {
				$recordModel->verifyPasswordChange($params['userModel']);
			}
		}
	}

	/**
	 * Check the password after login.
	 *
	 * @param string $password
	 *
	 * @return void
	 */
	public static function afterLogin(string $password): void
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
			if (($passStatus = self::check($password)) && !$passStatus['status']) {
				\App\Session::set('ShowUserPwnedPasswordChange', 1);
				$pwnedPassword['status'][$userName] = true;
			}
			$pwnedPassword['dates'][$userName] = date('Y-m-d H:i:s');
			\App\Utils::saveToFile($file, $pwnedPassword, '', 0, true);
		} elseif (isset($pwnedPassword['status'][$userName]) && true === $pwnedPassword['status'][$userName]) {
			\App\Session::set('ShowUserPwnedPasswordChange', 1);
		}
	}

	/**
	 * UsersAfterPasswordChange handler function.
	 *
	 * @param App\EventHandler $eventHandler
	 */
	public function usersAfterPasswordChange(\App\EventHandler $eventHandler): void
	{
		if (\App\Session::has('ShowUserPwnedPasswordChange')) {
			\App\Session::delete('ShowUserPwnedPasswordChange');
		}
		$file = ROOT_DIRECTORY . '/app_data/PwnedPassword.php';
		if (file_exists($file)) {
			$pwnedPassword = require $file;
			unset($pwnedPassword['status'][$eventHandler->getRecordModel()->get('user_name')]);
			\App\Utils::saveToFile($file, $pwnedPassword, '', 0, true);
		}
	}
}
