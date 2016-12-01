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
 * Provides API to work with vtiger CRM Menu
 * @package vtlib
 */
class Menu
{

	/** ID of this menu instance */
	public $id = false;
	public $label = false;
	public $sequence = false;
	public $visible = 0;

	/**
	 * Initialize this instance
	 * @param Array Map 
	 * @access private
	 */
	public function initialize($valuemap)
	{
		$this->id = $valuemap[parenttabid];
		$this->label = $valuemap[parenttab_label];
		$this->sequence = $valuemap[sequence];
		$this->visible = $valuemap[visible];
	}

	/**
	 * Get instance of menu by label
	 * @param String Menu label
	 */
	static function getInstance($value)
	{
		return false;
	}

	/**
	 * Helper function to log messages
	 * @param String Message to log
	 * @param Boolean true appends linebreak, false to avoid it
	 * @access private
	 */
	static function log($message, $delim = true)
	{
		Utils::Log($message, $delim);
	}

	/**
	 * Delete all menus associated with module
	 * @param Module Instnace of module to use
	 */
	static function deleteForModule($moduleInstance)
	{
		$id = (new \App\Db\Query)->select('id')->from('yetiforce_menu')->where(['module' => $moduleInstance->id])->scalar();
		if ($id) {
			\App\Db::getInstance()->createCommand()->delete('yetiforce_menu', ['module' => $moduleInstance->id])->execute();
			$menuRecordModel = new \Settings_Menu_Record_Model();
			$menuRecordModel->refreshMenuFiles();
		}
	}
}
