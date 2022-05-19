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
 * Language Manager class for vtiger Modules.
 */
class Language extends LanguageImport
{
	/**
	 * Function to remove language files related to a module.
	 *
	 * @param ModuleBasic $moduleInstance
	 */
	public static function deleteForModule(ModuleBasic $moduleInstance)
	{
		$query = (new \App\Db\Query())->select(['prefix'])->from('vtiger_language');
		foreach ($query->column() as $lang) {
			$langFilePath = "languages/$lang/{$moduleInstance->name}.json";
			if (file_exists($langFilePath)) {
				unlink($langFilePath);
			}
			$langFilePath = "languages/$lang/Settings/{$moduleInstance->name}.json";
			if (file_exists($langFilePath)) {
				unlink($langFilePath);
			}
		}
	}
}
