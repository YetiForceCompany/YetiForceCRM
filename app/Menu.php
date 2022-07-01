<?php
/**
 * Menu file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Menu basic class.
 */
class Menu
{
	/** @var string[] Menu types */
	public const TYPES = [
		0 => 'Module',
		1 => 'Shortcut',
		2 => 'Label',
		3 => 'Separator',
		5 => 'QuickCreate',
		6 => 'HomeIcon',
		7 => 'CustomFilter',
		8 => 'Profile',
		9 => 'RecycleBin',
	];

	/**
	 * Reload menu.
	 *
	 * @param int|null $menuRoleId
	 *
	 * @return void
	 */
	public static function reloadMenu(?int $menuRoleId = null): void
	{
		$menuRecordModel = new \Settings_Menu_Record_Model();
		if (null === $menuRoleId) {
			$menuRecordModel->refreshMenuFiles();
		} else {
			$menuRecordModel->generateFileMenu($menuRoleId);
		}
	}
}
