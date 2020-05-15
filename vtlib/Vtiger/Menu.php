<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

namespace vtlib;

/**
 * Provides API to work with vtiger CRM Menu.
 */
class Menu
{
	/** ID of this menu instance */
	public $id = false;
	public $label = false;
	public $sequence = false;
	public $visible = 0;

	/**
	 * Initialize this instance.
	 *
	 * @param array Map
	 * @param mixed $valuemap
	 */
	public function initialize($valuemap)
	{
		$this->id = $valuemap[parenttabid];
		$this->label = $valuemap[parenttab_label];
		$this->sequence = $valuemap[sequence];
		$this->visible = $valuemap[visible];
	}

	/**
	 * Delete all menus associated with module.
	 *
	 * @param ModuleBasic $moduleInstance
	 */
	public static function deleteForModule(ModuleBasic $moduleInstance)
	{
		$id = (new \App\Db\Query())->select(['id'])->from('yetiforce_menu')->where(['module' => $moduleInstance->id])->scalar();
		if ($id) {
			\App\Db::getInstance()->createCommand()->delete('yetiforce_menu', ['module' => $moduleInstance->id])->execute();
			$menuRecordModel = new \Settings_Menu_Record_Model();
			$menuRecordModel->refreshMenuFiles();
		}
	}
}
