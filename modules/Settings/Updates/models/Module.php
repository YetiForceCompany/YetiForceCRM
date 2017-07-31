<?php

/**
 * Settings updates module model class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Settings_Updates_Module_Model extends Settings_Vtiger_Module_Model
{

	public static function getUpdates()
	{
		return (new App\Db\Query())->from('yetiforce_updates')
				->all();
	}
}
