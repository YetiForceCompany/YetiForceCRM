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
require_once 'include/utils/ListViewUtils.php';
require_once 'include/utils/CommonUtils.php';
require_once 'include/utils/InventoryUtils.php';
require_once 'include/utils/SearchUtils.php';
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

/**
 * Function to format the input value for SQL like clause.
 * @param $str - Input string value to be formatted.
 * @param $flag - By default set to 0 (Will look for cases %string%).
 *                If set to 1 - Will look for cases %string.
 *                If set to 2 - Will look for cases string%.
 * @return String formatted as per the SQL like clause requirement
 */
function formatForSqlLike($str, $flag = 0, $is_field = false)
{
	$adb = PearDatabase::getInstance();
	if (isset($str)) {
		if ($is_field === false) {
			$str = str_replace('%', '\%', $str);
			$str = str_replace('_', '\_', $str);
			if ($flag == 0) {
				// If value what to search is null then we should not add % which will fail
				if (empty($str))
					$str = '' . $str . '';
				else
					$str = '%' . $str . '%';
			} elseif ($flag == 1) {
				$str = '%' . $str;
			} elseif ($flag == 2) {
				$str = $str . '%';
			}
		} else {
			if ($flag == 0) {
				$str = 'concat("%",' . $str . ',"%")';
			} elseif ($flag == 1) {
				$str = 'concat("%",' . $str . ')';
			} elseif ($flag == 2) {
				$str = 'concat(' . $str . ',"%")';
			}
		}
	}
	return $adb->sqlEscapeString($str);
}

/** Function to get on clause criteria for duplicate check queries */
function get_on_clause($field_list)
{
	$field_array = explode(',', $field_list);
	$ret_str = '';
	$i = 1;
	foreach ($field_array as $fld) {
		$sub_arr = explode('.', $fld);
		$tbl_name = $sub_arr[0];
		$col_name = $sub_arr[1];

		$ret_str .= " ifnull($tbl_name.$col_name,'null') = ifnull(temp.$col_name,'null')";

		if (count($field_array) != $i)
			$ret_str .= " and ";
		$i++;
	}
	return $ret_str;
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

/** Function to set date values compatible to database (YY_MM_DD)
 * @param $value -- value :: Type string
 * @returns $insert_date -- insert_date :: Type string
 */
function getValidDBInsertDateValue($value)
{

	\App\Log::trace("Entering getValidDBInsertDateValue(" . $value . ") method ...");
	$value = trim($value);
	$delim = ['/', '.'];
	foreach ($delim as $delimiter) {
		$x = strpos($value, $delimiter);
		if ($x === false)
			continue;
		else {
			$value = str_replace($delimiter, '-', $value);
			break;
		}
	}
	list($y, $m, $d) = explode('-', $value);
	if (strlen($y) == 1)
		$y = '0' . $y;
	if (strlen($m) == 1)
		$m = '0' . $m;
	if (strlen($d) == 1)
		$d = '0' . $d;
	$value = implode('-', [$y, $m, $d]);

	if (strlen($y) < 4) {
		$insert_date = DateTimeField::convertToDBFormat($value);
	} else {
		$insert_date = $value;
	}

	if (preg_match("/^[0-9]{2,4}[-][0-1]{1,2}?[0-9]{1,2}[-][0-3]{1,2}?[0-9]{1,2}$/", $insert_date) == 0) {
		return '';
	}

	\App\Log::trace("Exiting getValidDBInsertDateValue method ...");
	return $insert_date;
}

function getValidDBInsertDateTimeValue($value)
{
	$value = trim($value);
	$valueList = explode(' ', $value);
	if (count($valueList) == 2) {
		$dbDateValue = getValidDBInsertDateValue($valueList[0]);
		$dbTimeValue = $valueList[1];
		if (!empty($dbTimeValue) && strpos($dbTimeValue, ':') === false) {
			$dbTimeValue = $dbTimeValue . ':';
		}
		$timeValueLength = strlen($dbTimeValue);
		if (!empty($dbTimeValue) && strrpos($dbTimeValue, ':') == ($timeValueLength - 1)) {
			$dbTimeValue = $dbTimeValue . '00';
		}
		try {
			$dateTime = new DateTimeField($dbDateValue . ' ' . $dbTimeValue);
			return $dateTime->getDBInsertDateTimeValue();
		} catch (Exception $ex) {
			return '';
		}
	} elseif (count($valueList == 1)) {
		return getValidDBInsertDateValue($value);
	}
}

//Get the User selected NumberOfCurrencyDecimals
function getCurrencyDecimalPlaces()
{
	$current_user = vglobal('current_user');
	$currency_decimal_places = $current_user->no_of_currency_decimals;
	if (isset($currency_decimal_places)) {
		return $currency_decimal_places;
	} else {
		return 2;
	}
}
