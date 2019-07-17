<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

/**
 * Class to handle Caching Mechanism and re-use information.
 */
require_once 'include/runtime/Cache.php';

class VTCacheUtils
{
	public static function lookupBlockLabelWithId($id)
	{
		if (isset(self::$_blocklabel_cache[$id])) {
			return self::$_blocklabel_cache[$id];
		}
		return false;
	}

	public static function lookupFieldInfoModule($module, $presence = ['0', '2'])
	{
		$moduleFields = [];
		$fieldsInfo = \vtlib\Functions::getModuleFieldInfos($module);
		foreach ($fieldsInfo as $fieldInfo) {
			if (\in_array($fieldInfo['presence'], $presence)) {
				$moduleFields[$fieldInfo['fieldname']] = $fieldInfo;
			}
		}
		return $moduleFields;
	}
}
