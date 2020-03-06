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
			throw new \App\Exceptions\AppException('ERR_CLASS_NOT_FOUND');
		}
		return new $className();
	}
}
