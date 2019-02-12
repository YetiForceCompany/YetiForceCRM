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

	/** Field information caching */
	public static $_fieldinfo_cache = [];

	public static function updateFieldInfo($tabid, $fieldname, $fieldid, $fieldlabel, $columnname, $tablename, $uitype, $typeofdata, $presence)
	{
		self::$_fieldinfo_cache[$tabid][$fieldname] = [
			'tabid' => $tabid,
			'fieldid' => $fieldid,
			'fieldname' => $fieldname,
			'fieldlabel' => $fieldlabel,
			'columnname' => $columnname,
			'tablename' => $tablename,
			'uitype' => $uitype,
			'typeofdata' => $typeofdata,
			'presence' => $presence,
		];
		Vtiger_Cache::set('fieldInfo', $tabid, self::$_fieldinfo_cache[$tabid]);
	}

	public static function lookupFieldInfoModule($module, $presencein = ['0', '2'])
	{
		$tabid = \App\Module::getModuleId($module);
		$modulefields = false;
		$fieldInfo = Vtiger_Cache::get('fieldInfo', $tabid);
		if (isset($fldcache) && $fldcache) {
			$fldcache = $fieldInfo;
		} elseif (isset(self::$_fieldinfo_cache[$tabid])) {
			$fldcache = self::$_fieldinfo_cache[$tabid];
		}

		if (isset($fldcache)) {
			$modulefields = [];

			foreach ($fldcache as $fieldinfo) {
				if (in_array($fieldinfo['presence'], $presencein)) {
					$modulefields[] = $fieldinfo;
				}
			}
		}
		if (\App\Cache::has('ModuleFields', $tabid)) {
			$fieldInfo = \App\Cache::get('ModuleFields', $tabid);
			foreach ($fieldInfo as $blockFields) {
				foreach ($blockFields as $field) {
					if (in_array($field->get('presence'), $presencein)) {
						$cacheField = [
							'tabid' => $tabid,
							'fieldid' => $field->getId(),
							'fieldname' => $field->getName(),
							'fieldlabel' => $field->get('label'),
							'columnname' => $field->get('column'),
							'tablename' => $field->get('table'),
							'uitype' => (int) $field->get('uitype'),
							'typeofdata' => $field->get('typeofdata'),
							'presence' => $field->get('presence'),
						];
						$modulefields[] = $cacheField;
					}
				}
			}
		}
		return $modulefields;
	}
}
