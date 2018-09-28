<?php

/**
 * Settings updates module model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_Updates_Module_Model extends Settings_Vtiger_Module_Model
{
	public static function getUpdates()
	{
		return (new App\Db\Query())->from('yetiforce_updates')->all();
	}
}
