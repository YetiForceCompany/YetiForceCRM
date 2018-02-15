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
	/** All tab information caching */
	public static $_alltabrows_cache = false;

	public static function lookupAllTabsInfo()
	{
		return self::$_alltabrows_cache;
	}

	public static function updateAllTabsInfo($tabrows)
	{
		self::$_alltabrows_cache = $tabrows;
	}

	/** Block information caching */
	public static $_blocklabel_cache = [];

	public static function updateBlockLabelWithId($label, $id)
	{
		self::$_blocklabel_cache[$id] = $label;
	}

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

			foreach ($fldcache as $fieldname => $fieldinfo) {
				if (in_array($fieldinfo['presence'], $presencein)) {
					$modulefields[] = $fieldinfo;
				}
			}
		}

		$fieldInfo = Vtiger_Cache::get('ModuleFields', $tabid);
		if ($fieldInfo) {
			foreach ($fieldInfo as $block => $blockFields) {
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

	/** Related module information for Report */
	public static $_report_listofmodules_cache = false;

	public static function lookupReportListOfModuleInfos()
	{
		return self::$_report_listofmodules_cache;
	}

	public static function updateReportListOfModuleInfos($module_list, $related_modules)
	{
		if (self::$_report_listofmodules_cache === false) {
			self::$_report_listofmodules_cache = [
				'module_list' => $module_list,
				'related_modules' => $related_modules,
			];
		}
	}

	/** Report module information based on used. */
	public static $_reportmodule_infoperuser_cache = [];

	public static function lookupReportInfo($userid, $reportid)
	{
		if (isset(self::$_reportmodule_infoperuser_cache[$userid])) {
			if (isset(self::$_reportmodule_infoperuser_cache[$userid][$reportid])) {
				return self::$_reportmodule_infoperuser_cache[$userid][$reportid];
			}
		}

		return false;
	}

	public static function updateReportInfo($userid, $reportid, $primarymodule, $secondarymodules, $reporttype, $reportname, $description, $folderid, $owner)
	{
		if (!isset(self::$_reportmodule_infoperuser_cache[$userid])) {
			self::$_reportmodule_infoperuser_cache[$userid] = [];
		}
		if (!isset(self::$_reportmodule_infoperuser_cache[$userid][$reportid])) {
			self::$_reportmodule_infoperuser_cache[$userid][$reportid] = [
				'reportid' => $reportid,
				'primarymodule' => $primarymodule,
				'secondarymodules' => $secondarymodules,
				'reporttype' => $reporttype,
				'reportname' => $reportname,
				'description' => $description,
				'folderid' => $folderid,
				'owner' => $owner,
			];
		}
	}

	/** Report module sub-ordinate users information. */
	public static $_reportmodule_subordinateuserid_cache = [];

	public static function lookupReportSubordinateUsers($reportid)
	{
		if (isset(self::$_reportmodule_subordinateuserid_cache[$reportid])) {
			return self::$_reportmodule_subordinateuserid_cache[$reportid];
		}

		return false;
	}

	public static function updateReportSubordinateUsers($reportid, $userids)
	{
		self::$_reportmodule_subordinateuserid_cache[$reportid] = $userids;
	}

	/** Report module information based on used. */
	public static $_reportmodule_scheduledinfoperuser_cache = [];

	public static function lookupReportScheduledInfo($userid, $reportid)
	{
		if (isset(self::$_reportmodule_scheduledinfoperuser_cache[$userid])) {
			if (isset(self::$_reportmodule_scheduledinfoperuser_cache[$userid][$reportid])) {
				return self::$_reportmodule_scheduledinfoperuser_cache[$userid][$reportid];
			}
		}

		return false;
	}

	public static function updateReportScheduledInfo($userid, $reportid, $isScheduled, $scheduledFormat, $scheduledInterval, $scheduledRecipients, $scheduledTime)
	{
		if (!isset(self::$_reportmodule_scheduledinfoperuser_cache[$userid])) {
			self::$_reportmodule_scheduledinfoperuser_cache[$userid] = [];
		}
		if (!isset(self::$_reportmodule_scheduledinfoperuser_cache[$userid][$reportid])) {
			self::$_reportmodule_scheduledinfoperuser_cache[$userid][$reportid] = [
				'reportid' => $reportid,
				'isScheduled' => $isScheduled,
				'scheduledFormat' => $scheduledFormat,
				'scheduledInterval' => $scheduledInterval,
				'scheduledRecipients' => $scheduledRecipients,
				'scheduledTime' => $scheduledTime,
			];
		}
	}

	public static $_report_field_bylabel = [];

	public static function getReportFieldByLabel($module, $label)
	{
		return self::$_report_field_bylabel[$module][$label];
	}

	public static function setReportFieldByLabel($module, $label, $fieldInfo)
	{
		self::$_report_field_bylabel[$module][$label] = $fieldInfo;
	}
}
