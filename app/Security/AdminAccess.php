<?php
/**
 * Admin privilege basic file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Security;

/**
 * Admin privilege basic class.
 */
class AdminAccess
{
	/**
	 * Table name with accesses.
	 */
	public const ACCESS_TABLE_NAME = 'a_#__settings_access';
	/**
	 * Table name with settings modules.
	 */
	public const MODULES_TABLE_NAME = 'a_#__settings_modules';
	/**
	 * Module status active.
	 */
	public const MODULE_STATUS_ACTIVE = 1;
	/**
	 * Exceptions. Modules without authorization.
	 */
	public const EXCEPTIONS = ['Vtiger', 'YetiForce'];

	/**
	 * Function to check permission.
	 *
	 * @param string $moduleName
	 * @param int    $userId
	 *
	 * @return bool
	 */
	public static function isPermitted(string $moduleName, int $userId = null): bool
	{
		if (null === $userId) {
			$userId = \App\User::getCurrentUserId();
		}
		$userModel = \App\User::getUserModel($userId);
		return ($userModel->isAdmin() || $userModel->isSuperUser()) && (
				\in_array($moduleName, self::EXCEPTIONS)
				|| (
					\in_array($moduleName, self::getActiveModules()) && (
						$userModel->isAdmin()
						|| \in_array($moduleName, self::getPermittedModulesByUser($userId))
					)
				)
				|| ($userModel->isAdmin() && 'AdminAccess' === $moduleName)
			);
	}

	/**
	 * Gets permitted modules by user ID.
	 *
	 * @param int $userId
	 *
	 * @return string[]
	 */
	private static function getPermittedModulesByUser(int $userId): array
	{
		$cacheName = 'AdminPermittedModulesByUser';
		$permissions = \App\Cache::has($cacheName, $userId) ? \App\Cache::get($cacheName, $userId) : null;
		if (null === $permissions) {
			$permissions = (new \App\Db\Query())->select(['name'])
				->from(self::MODULES_TABLE_NAME)
				->innerJoin(self::ACCESS_TABLE_NAME, self::MODULES_TABLE_NAME . '.id=' . self::ACCESS_TABLE_NAME . '.module_id')
				->where(['user' => $userId, 'status' => self::MODULE_STATUS_ACTIVE])->column();
			\App\Cache::save($cacheName, $userId, $permissions, \App\Cache::MEDIUM);
		}
		return $permissions;
	}

	/**
	 * Gets active setting modules.
	 *
	 * @return string[]
	 */
	private static function getActiveModules(): array
	{
		$cacheName = 'AdminActiveModules';
		$modules = \App\Cache::has($cacheName, '') ? \App\Cache::get($cacheName, '') : null;
		if (null === $modules) {
			$modules = (new \App\Db\Query())->select(['name'])
				->from(self::MODULES_TABLE_NAME)
				->where(['status' => self::MODULE_STATUS_ACTIVE])->column();
			\App\Cache::save($cacheName, '', $modules, \App\Cache::MEDIUM);
		}
		return $modules;
	}
}
