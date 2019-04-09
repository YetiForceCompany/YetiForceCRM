<?php
/**
 * Menu model file.
 *
 * @package   Model
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace Modules\Base\Models;

/**
 * Menu model class.
 */
class Menu
{
	/**
	 * Gets menu by role.
	 *
	 * @param string $roleId
	 *
	 * @return array
	 */
	public static function getMenu(string $roleId): array
	{
		$roleId = (int) substr($roleId, 1);
		if (!\App\Cache::has(__FUNCTION__, $roleId)) {
			$menuData = (new \App\Db\Query())->select(['yetiforce_menu.role', 'yetiforce_menu.*', 'vtiger_tab.name'])->from('yetiforce_menu')
				->leftJoin('vtiger_tab', 'vtiger_tab.tabid = yetiforce_menu.module')
				->where(['role' => $roleId])
				->orderBy(['yetiforce_menu.sequence' => \SORT_ASC, 'yetiforce_menu.parentid' => \SORT_ASC])->all();
			if (empty($menuData) && 0 !== $roleId) {
				$menuData = self::getMenu('H0');
			}
			\App\Cache::save(__FUNCTION__, $roleId, $menuData);
		}
		return \App\Cache::get(__FUNCTION__, $roleId);
	}
}
