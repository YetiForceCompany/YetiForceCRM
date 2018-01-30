<?php
/* * *******************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * ****************************************************************************** */
/* * *******************************************************************************
 * $Header$
 * Description:  Includes generic helper functions used throughout the application.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * ****************************************************************************** */
require_once 'include/database/PearDatabase.php';
require_once 'include/utils/CommonUtils.php';
require_once 'include/fields/DateTimeField.php';
require_once 'include/fields/DateTimeRange.php';
require_once 'include/fields/CurrencyField.php';
require_once 'include/CRMEntity.php';
include_once 'modules/Vtiger/CRMEntity.php';
require_once 'include/runtime/Cache.php';
require_once 'modules/Vtiger/helpers/Util.php';
require_once 'modules/PickList/DependentPickListUtils.php';
require_once 'modules/Users/Users.php';
require_once 'include/Webservices/Utils.php';

// Constants to be defined here
// For Migration status.
define("MIG_CHARSET_PHP_UTF8_DB_UTF8", 1);
define("MIG_CHARSET_PHP_NONUTF8_DB_NONUTF8", 2);
define("MIG_CHARSET_PHP_NONUTF8_DB_UTF8", 3);
define("MIG_CHARSET_PHP_UTF8_DB_NONUTF8", 4);

// For Restoration.
define("RB_RECORD_DELETED", 'delete');
define("RB_RECORD_INSERTED", 'insert');
define("RB_RECORD_UPDATED", 'update');

/** Function to get column fields for a given module
 * @param $module -- module:: Type string
 * @returns $column_fld -- column field :: Type array
 *
 */
function getColumnFields($module)
{

	\App\Log::trace('Entering getColumnFields(' . $module . ') method ...');

	// Lookup in cache for information
	$cachedModuleFields = VTCacheUtils::lookupFieldInfoModule($module);

	if ($cachedModuleFields === false) {
		$fieldsInfo = vtlib\Functions::getModuleFieldInfos($module);
		if (!empty($fieldsInfo)) {
			foreach ($fieldsInfo as $resultrow) {
				// Update information to cache for re-use
				VTCacheUtils::updateFieldInfo(
					$resultrow['tabid'], $resultrow['fieldname'], $resultrow['fieldid'], $resultrow['fieldlabel'], $resultrow['columnname'], $resultrow['tablename'], $resultrow['uitype'], $resultrow['typeofdata'], $resultrow['presence']
				);
			}
		}
		// For consistency get information from cache
		$cachedModuleFields = VTCacheUtils::lookupFieldInfoModule($module);
	}

	if ($module == 'Calendar') {
		$cachedEventsFields = VTCacheUtils::lookupFieldInfoModule('Events');
		if (!$cachedEventsFields) {
			getColumnFields('Events');
			$cachedEventsFields = VTCacheUtils::lookupFieldInfoModule('Events');
		}

		if (!$cachedModuleFields) {
			$cachedModuleFields = $cachedEventsFields;
		} else {
			$cachedModuleFields = array_merge($cachedModuleFields, $cachedEventsFields);
		}
	}

	$column_fld = [];
	if ($cachedModuleFields) {
		foreach ($cachedModuleFields as $fieldinfo) {
			$column_fld[$fieldinfo['fieldname']] = '';
		}
	}

	\App\Log::trace('Exiting getColumnFields method ...');
	return $column_fld;
}

// Return Question mark
function _questionify($v)
{
	return '?';
}

/**
 * Function to generate question marks for a given list of items
 */
function generateQuestionMarks($items_list)
{
	// array_map will call the function specified in the first parameter for every element of the list in second parameter
	if (is_array($items_list)) {
		return implode(',', array_map('_questionify', $items_list));
	} else {
		return implode(',', array_map('_questionify', explode(',', $items_list)));
	}
}

//functions for asterisk integration end

/* Function to get the related tables data
 * @param - $module - Primary module name
 * @param - $secmodule - Secondary module name
 * return Array $rel_array tables and fields to be compared are sent
 * */
function getRelationTables($module, $secmodule)
{
	$adb = PearDatabase::getInstance();
	$primary_obj = CRMEntity::getInstance($module);
	$secondary_obj = CRMEntity::getInstance($secmodule);

	$ui10_query = $adb->pquery("SELECT vtiger_field.tabid AS tabid,vtiger_field.tablename AS tablename, vtiger_field.columnname AS columnname FROM vtiger_field INNER JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid WHERE (vtiger_fieldmodulerel.module=? && vtiger_fieldmodulerel.relmodule=?) || (vtiger_fieldmodulerel.module=? && vtiger_fieldmodulerel.relmodule=?)", [$module, $secmodule, $secmodule, $module]);
	if ($adb->numRows($ui10_query) > 0) {
		$ui10_tablename = $adb->queryResult($ui10_query, 0, 'tablename');
		$ui10_columnname = $adb->queryResult($ui10_query, 0, 'columnname');

		if ($primary_obj->table_name == $ui10_tablename) {
			$reltables = [$ui10_tablename => ["" . $primary_obj->table_index . "", "$ui10_columnname"]];
		} else if ($secondary_obj->table_name == $ui10_tablename) {
			$reltables = [$ui10_tablename => ["$ui10_columnname", "" . $secondary_obj->table_index . ""], "" . $primary_obj->table_name . "" => "" . $primary_obj->table_index . ""];
		} else {
			if (isset($secondary_obj->tab_name_index[$ui10_tablename])) {
				$rel_field = $secondary_obj->tab_name_index[$ui10_tablename];
				$reltables = [$ui10_tablename => ["$ui10_columnname", "$rel_field"], "" . $primary_obj->table_name . "" => "" . $primary_obj->table_index . ""];
			} else {
				$rel_field = $primary_obj->tab_name_index[$ui10_tablename];
				$reltables = [$ui10_tablename => ["$rel_field", "$ui10_columnname"], "" . $primary_obj->table_name . "" => "" . $primary_obj->table_index . ""];
			}
		}
	} else {
		if (method_exists($primary_obj, setRelationTables)) {
			$reltables = $primary_obj->setRelationTables($secmodule);
		} else {
			$reltables = '';
		}
	}
	if (is_array($reltables) && !empty($reltables)) {
		$rel_array = $reltables;
	} else {
		$rel_array = ["vtiger_crmentityrel" => ["crmid", "relcrmid"], "" . $primary_obj->table_name . "" => "" . $primary_obj->table_index . ""];
	}
	return $rel_array;
}

/**
 * This function returns no value but handles the delete functionality of each entity.
 * Input Parameter are $module - module name, $return_module - return module name, $focus - module object, $record - entity id, $return_id - return entity id.
 */
function DeleteEntity($destinationModule, $sourceModule, CRMEntity $focus, $destinationRecordId, $sourceRecordId, $relatedName = false)
{
	\App\Log::trace("Entering DeleteEntity method ($destinationModule, $sourceModule, $destinationRecordId, $sourceRecordId)");
	if ($destinationModule != $sourceModule && !empty($sourceModule) && !empty($sourceRecordId)) {
		$eventHandler = new App\EventHandler();
		$eventHandler->setModuleName($sourceModule);
		$eventHandler->setParams([
			'CRMEntity' => $focus,
			'sourceModule' => $sourceModule,
			'sourceRecordId' => $sourceRecordId,
			'destinationModule' => $destinationModule,
			'destinationRecordId' => $destinationRecordId,
		]);
		$eventHandler->trigger('EntityBeforeUnLink');

		$focus->unlinkRelationship($destinationRecordId, $sourceModule, $sourceRecordId, $relatedName);
		$focus->trackUnLinkedInfo($sourceRecordId);

		$eventHandler->trigger('EntityAfterUnLink');
	} else {
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->isPermitted($destinationModule, 'Delete', $destinationRecordId)) {
			throw new \App\Exceptions\AppException('LBL_PERMISSION_DENIED');
		}
		$focus->trash($destinationModule, $destinationRecordId);
	}
	\App\Log::trace('Exiting DeleteEntity method ...');
}

/**
 * Function to related two records of different entity types
 */
function relateEntities(CRMEntity $focus, $sourceModule, $sourceRecordId, $destinationModule, $destinationRecordIds, $relatedName = false)
{
	\App\Log::trace("Entering relateEntities method ($sourceModule, $sourceRecordId, $destinationModule, $destinationRecordIds)");
	if (!is_array($destinationRecordIds))
		$destinationRecordIds = [$destinationRecordIds];

	$data = [
		'CRMEntity' => $focus,
		'sourceModule' => $sourceModule,
		'sourceRecordId' => $sourceRecordId,
		'destinationModule' => $destinationModule,
	];
	$eventHandler = new App\EventHandler();
	$eventHandler->setModuleName($sourceModule);
	foreach ($destinationRecordIds as &$destinationRecordId) {
		$data['destinationRecordId'] = $destinationRecordId;
		$eventHandler->setParams($data);
		$eventHandler->trigger('EntityBeforeLink');
		$focus->saveRelatedModule($sourceModule, $sourceRecordId, $destinationModule, $destinationRecordId, $relatedName);
		CRMEntity::trackLinkedInfo($sourceRecordId);
		$eventHandler->trigger('EntityAfterLink');
	}
	\App\Log::trace("Exiting relateEntities method ...");
}
