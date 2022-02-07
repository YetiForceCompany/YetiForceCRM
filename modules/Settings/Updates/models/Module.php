<?php

/**
 * Settings updates module model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Updates_Module_Model extends Settings_Vtiger_Module_Model
{
	public static function getUpdates()
	{
		return (new App\Db\Query())->from('yetiforce_updates')->orderBy(['id' => SORT_DESC])->all();
	}
}
