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
require_once 'include/events/SqlResultIterator.php';
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

/** Function to return a full name
 * @param $row -- row:: Type integer
 * @param $first_column -- first column:: Type string
 * @param $last_column -- last column:: Type string
 * @returns $fullname -- fullname:: Type string
 *
 */
function return_name(&$row, $first_column, $last_column)
{

	\App\Log::trace("Entering return_name(" . $row . "," . $first_column . "," . $last_column . ") method ...");
	$first_name = "";
	$last_name = "";
	$full_name = "";

	if (isset($row[$first_column])) {
		$first_name = stripslashes($row[$first_column]);
	}

	if (isset($row[$last_column])) {
		$last_name = stripslashes($row[$last_column]);
	}

	$full_name = $first_name;

	// If we have a first name and we have a last name
	if ($full_name != "" && $last_name != "") {
		// append a space, then the last name
		$full_name .= " " . $last_name;
	}
	// If we have no first name, but we have a last name
	else if ($last_name != "") {
		// append the last name without the space.
		$full_name .= $last_name;
	}

	\App\Log::trace("Exiting return_name method ...");
	return $full_name;
}
$toHtml = array(
	'"' => '&quot;',
	'<' => '&lt;',
	'>' => '&gt;',
	'& ' => '&amp; ',
	"'" => '&#039;',
	'' => '\r',
	'\r\n' => '\n',
);

/** Function to get column fields for a given module
 * @param $module -- module:: Type string
 * @returns $column_fld -- column field :: Type array
 *
 */
function getColumnFields($module)
{

	\App\Log::trace('Entering getColumnFields(' . $module . ') method ...');

	// Lookup in cache for information
	$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);

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
		$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);
	}

	if ($module == 'Calendar') {
		$cachedEventsFields = VTCacheUtils::lookupFieldInfo_Module('Events');
		if (!$cachedEventsFields) {
			getColumnFields('Events');
			$cachedEventsFields = VTCacheUtils::lookupFieldInfo_Module('Events');
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

	\App\Log::trace("Exiting getColumnFields method ...");
	return $column_fld;
}

/** Function to get a userid for outlook
 * @param $username -- username :: Type string
 * @returns $user_id -- user id :: Type integer
 */
//outlook security
function getUserId_Ol($username)
{

	\App\Log::trace("Entering getUserId_Ol(" . $username . ") method ...");
	\App\Log::trace("in getUserId_Ol " . $username);
	$cache = Vtiger_Cache::getInstance();
	if ($cache->getUserId($username) || $cache->getUserId($username) === 0) {
		return $cache->getUserId($username);
	} else {
		$adb = PearDatabase::getInstance();
		$sql = "select id from vtiger_users where user_name=?";
		$result = $adb->pquery($sql, array($username));
		$num_rows = $adb->num_rows($result);
		if ($num_rows > 0) {
			$user_id = $adb->query_result($result, 0, "id");
		} else {
			$user_id = 0;
		}
		\App\Log::trace("Exiting getUserId_Ol method ...");
		$cache->setUserId($username, $user_id);
		return $user_id;
	}
}

/** Function to get a action id for a given action name
 * @param $action -- action name :: Type string
 * @returns $actionid -- action id :: Type integer
 */
//outlook security

function getActionid($action)
{

	\App\Log::trace('Entering getActionid(' . $action . ') method ...');

	if (empty($action)) {
		return null;
	}
	$actionid = Vtiger_Cache::get('getActionid', $action);
	if ($actionid) {
		\App\Log::trace('Exiting getActionid method ... - ' . $actionid);
		return $actionid;
	}
	$actionIds = \App\Module::getTabData('actionId');
	if (isset($actionIds[$action])) {
		$actionid = $actionIds[$action];
	}
	if (empty($actionid)) {
		$db = PearDatabase::getInstance();
		$query = 'select actionid from vtiger_actionmapping where actionname=?';
		$result = $db->pquery($query, [$action]);
		$actionid = $db->getSingleValue($result);
	}
	Vtiger_Cache::set('getActionid', $action, $actionid);
	\App\Log::trace('Exiting getActionid method ... - ' . $actionid);
	return $actionid;
}

/** Function to get a action for a given action id
 * @param $action id -- action id :: Type integer
 * @returns $actionname-- action name :: Type string
 */
function getActionname($actionid)
{

	\App\Log::trace('Entering getActionname(' . $actionid . ') method ...');
	$adb = PearDatabase::getInstance();

	$actionName = Vtiger_Cache::get('getActionName', $actionid);
	if ($actionName) {
		\App\Log::trace('Exiting getActionname method ...');
		return $actionName;
	}
	if (file_exists('user_privileges/tabdata.php') && (filesize('user_privileges/tabdata.php') != 0)) {
		include('user_privileges/tabdata.php');
		$actionName = $action_name_array[$actionid];
	} else {
		$query = 'select actionname from vtiger_actionmapping where actionid=? and securitycheck=0';
		$result = $adb->pquery($query, array($actionid));
		$actionName = $adb->getSingleValue($result);
	}
	Vtiger_Cache::set('getActionName', $actionid, $actionName);
	\App\Log::trace('Exiting getActionname method ...');
	return $actionName;
}

/** Function to get a user id or group id for a given entity
 * @param $record -- entity id :: Type integer
 * @returns $ownerArr -- owner id :: Type array
 */
function getRecordOwnerId($record)
{

	\App\Log::trace("Entering getRecordOwnerId($record) method ...");
	$ownerArr = [];

	$recordMetaData = vtlib\Functions::getCRMRecordMetadata($record);
	if ($recordMetaData) {
		$ownerId = $recordMetaData['smownerid'];
		$type = \App\Fields\Owner::getType($ownerId);
		$ownerArr[$type] = $ownerId;
	}
	\App\Log::trace('Exiting getRecordOwnerId method ...');
	return $ownerArr;
}

/** Function to update product quantity
 * @param $product_id -- product id :: Type integer
 * @param $upd_qty -- quantity :: Type integer
 */
function updateProductQty($product_id, $upd_qty)
{

	\App\Log::trace("Entering updateProductQty(" . $product_id . "," . $upd_qty . ") method ...");
	$adb = PearDatabase::getInstance();
	$query = "update vtiger_products set qtyinstock=? where productid=?";
	$adb->pquery($query, array($upd_qty, $product_id));
	\App\Log::trace("Exiting updateProductQty method ...");
}

/**
 * simple HTML to UTF-8 conversion:
 */
function html_to_utf8($data)
{
	return preg_replace("/\\&\\#([0-9]{3,10})\\;/e", '_html_to_utf8("\\1")', $data);
}

function _html_to_utf8($data)
{
	if ($data > 127) {
		$i = 5;
		while (($i--) > 0) {
			if ($data != ($a = $data % ($p = pow(64, $i)))) {
				$ret = chr(base_convert(str_pad(str_repeat(1, $i + 1), 8, "0"), 2, 10) + (($data - $a) / $p));
				for ($i; $i > 0; $i--)
					$ret .= chr(128 + ((($data % pow(64, $i)) - ($data % ($p = pow(64, $i - 1)))) / $p));
				break;
			}
		}
	} else
		$ret = "&#$data;";
	return $ret;
}

// Return Question mark
function _questionify($v)
{
	return "?";
}

/**
 * Function to generate question marks for a given list of items
 */
function generateQuestionMarks($items_list)
{
	// array_map will call the function specified in the first parameter for every element of the list in second parameter
	if (is_array($items_list)) {
		return implode(",", array_map("_questionify", $items_list));
	} else {
		return implode(",", array_map("_questionify", explode(",", $items_list)));
	}
}

/**
 * Function to find the UI type of a field based on the uitype id
 */
function is_uitype($uitype, $reqtype)
{
	$ui_type_arr = array(
		'_date_' => array(5, 6, 23, 70),
		'_picklist_' => array(15, 16, 52, 53, 54, 55, 59, 62, 63, 66, 68, 76, 77, 78, 80, 98, 101, 115, 357),
		'_users_list_' => array(52),
	);

	if ($ui_type_arr[$reqtype] != null) {
		if (in_array($uitype, $ui_type_arr[$reqtype])) {
			return true;
		}
	}
	return false;
}

/**
 * Function to escape quotes
 * @param $value - String in which single quotes have to be replaced.
 * @return Input string with single quotes escaped.
 */
function escape_single_quotes($value)
{
	if (isset($value))
		$value = str_replace("'", "\'", $value);
	return $value;
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
	return $adb->sql_escape_string($str);
}

/** Function to get on clause criteria for duplicate check queries */
function get_on_clause($field_list, $uitype_arr, $module)
{
	$field_array = explode(",", $field_list);
	$ret_str = '';
	$i = 1;
	foreach ($field_array as $fld) {
		$sub_arr = explode(".", $fld);
		$tbl_name = $sub_arr[0];
		$col_name = $sub_arr[1];
		$fld_name = $sub_arr[2];

		$ret_str .= " ifnull($tbl_name.$col_name,'null') = ifnull(temp.$col_name,'null')";

		if (count($field_array) != $i)
			$ret_str .= " and ";
		$i++;
	}
	return $ret_str;
}

/**
 * this function searches for a given number in vtiger and returns the callerInfo in an array format
 * currently the search is made across only leads, accounts and contacts modules
 *
 * @param $number - the number whose information you want
 * @return array in format array(name=>callername, module=>module, id=>id);
 */
function getCallerInfo($number)
{
	$adb = PearDatabase::getInstance();

	if (empty($number)) {
		return false;
	}
	$caller = "Unknown Number (Unknown)"; //declare caller as unknown in beginning

	$params = [];
	$name = array('Contacts', 'Accounts', 'Leads');
	foreach ($name as $module) {
		$focus = CRMEntity::getInstance($module);
		$query = $focus->buildSearchQueryForFieldTypes(11, $number);
		if (empty($query))
			return;

		$result = $adb->pquery($query, []);
		if ($adb->num_rows($result) > 0) {
			$callerName = $adb->query_result($result, 0, "name");
			$callerID = $adb->query_result($result, 0, 'id');
			$data = array("name" => $callerName, "module" => $module, "id" => $callerID);
			return $data;
		}
	}
	return false;
}

/**
 * this function returns the value of use_asterisk from the database for the current user
 * @param string $id - the id of the current user
 */
function get_use_asterisk($id)
{
	$adb = PearDatabase::getInstance();
	if (!\App\Module::isModuleActive('PBXManager') || isPermitted('PBXManager', 'index') == 'no') {
		return false;
	}
	$sql = "select * from vtiger_asteriskextensions where userid = ?";
	$result = $adb->pquery($sql, array($id));
	if ($adb->num_rows($result) > 0) {
		$use_asterisk = $adb->query_result($result, 0, "use_asterisk");
		$asterisk_extension = $adb->query_result($result, 0, "asterisk_extension");
		if ($use_asterisk == 0 || empty($asterisk_extension)) {
			return 'false';
		} else {
			return 'true';
		}
	} else {
		return 'false';
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

	$ui10_query = $adb->pquery("SELECT vtiger_field.tabid AS tabid,vtiger_field.tablename AS tablename, vtiger_field.columnname AS columnname FROM vtiger_field INNER JOIN vtiger_fieldmodulerel ON vtiger_fieldmodulerel.fieldid = vtiger_field.fieldid WHERE (vtiger_fieldmodulerel.module=? && vtiger_fieldmodulerel.relmodule=?) || (vtiger_fieldmodulerel.module=? && vtiger_fieldmodulerel.relmodule=?)", array($module, $secmodule, $secmodule, $module));
	if ($adb->num_rows($ui10_query) > 0) {
		$ui10_tablename = $adb->query_result($ui10_query, 0, 'tablename');
		$ui10_columnname = $adb->query_result($ui10_query, 0, 'columnname');
		$ui10_tabid = $adb->query_result($ui10_query, 0, 'tabid');

		if ($primary_obj->table_name == $ui10_tablename) {
			$reltables = array($ui10_tablename => array("" . $primary_obj->table_index . "", "$ui10_columnname"));
		} else if ($secondary_obj->table_name == $ui10_tablename) {
			$reltables = array($ui10_tablename => array("$ui10_columnname", "" . $secondary_obj->table_index . ""), "" . $primary_obj->table_name . "" => "" . $primary_obj->table_index . "");
		} else {
			if (isset($secondary_obj->tab_name_index[$ui10_tablename])) {
				$rel_field = $secondary_obj->tab_name_index[$ui10_tablename];
				$reltables = array($ui10_tablename => array("$ui10_columnname", "$rel_field"), "" . $primary_obj->table_name . "" => "" . $primary_obj->table_index . "");
			} else {
				$rel_field = $primary_obj->tab_name_index[$ui10_tablename];
				$reltables = array($ui10_tablename => array("$rel_field", "$ui10_columnname"), "" . $primary_obj->table_name . "" => "" . $primary_obj->table_index . "");
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
		$rel_array = array("vtiger_crmentityrel" => array("crmid", "relcrmid"), "" . $primary_obj->table_name . "" => "" . $primary_obj->table_index . "");
	}
	return $rel_array;
}

/**
 * This function returns no value but handles the delete functionality of each entity.
 * Input Parameter are $module - module name, $return_module - return module name, $focus - module object, $record - entity id, $return_id - return entity id.
 */
function DeleteEntity($destinationModule, $sourceModule, $focus, $destinationRecordId, $sourceRecordId, $relatedName = false)
{
	\App\Log::trace("Entering DeleteEntity method ($destinationModule, $sourceModule, $destinationRecordId, $sourceRecordId)");
	require_once('include/events/include.php');
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
		$focus->trackUnLinkedInfo($sourceModule, $sourceRecordId, $destinationModule, $destinationRecordId);

		$eventHandler->trigger('EntityAfterUnLink');
	} else {
		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPrivilegesModel->isPermitted($destinationModule, 'Delete', $destinationRecordId)) {
			throw new \Exception\AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}
		$focus->trash($destinationModule, $destinationRecordId);
	}
	\App\Log::trace('Exiting DeleteEntity method ...');
}

/**
 * Function to related two records of different entity types
 */
function relateEntities($focus, $sourceModule, $sourceRecordId, $destinationModule, $destinationRecordIds, $relatedName = false)
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
		$focus->save_related_module($sourceModule, $sourceRecordId, $destinationModule, $destinationRecordId, $relatedName);
		CRMEntity::trackLinkedInfo($sourceRecordId);
		$eventHandler->trigger('EntityAfterLink');
	}
	\App\Log::trace("Exiting relateEntities method ...");
}

/**
 * Function to check if a given record exists (not deleted)
 * @param integer $recordId - record id
 */
function isRecordExists($recordId, $cache = true)
{
	$recordMetaData = vtlib\Functions::getCRMRecordMetadata($recordId);
	return (isset($recordMetaData) && $recordMetaData['deleted'] == 0 ) ? true : false;
}

/** Function to set date values compatible to database (YY_MM_DD)
 * @param $value -- value :: Type string
 * @returns $insert_date -- insert_date :: Type string
 */
function getValidDBInsertDateValue($value)
{

	\App\Log::trace("Entering getValidDBInsertDateValue(" . $value . ") method ...");
	$value = trim($value);
	$delim = array('/', '.');
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
	$value = implode('-', array($y, $m, $d));

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

/** Function to return block name
 * @param Integer -- $blockid
 * @return String - Block Name
 */
function getBlockName($blockid)
{
	$adb = PearDatabase::getInstance();

	$blockname = VTCacheUtils::lookupBlockLabelWithId($blockid);

	if (!empty($blockid) && $blockname === false) {
		$block_res = $adb->pquery('SELECT blocklabel FROM vtiger_blocks WHERE blockid = ?', array($blockid));
		if ($adb->num_rows($block_res)) {
			$blockname = $adb->query_result($block_res, 0, 'blocklabel');
		} else {
			$blockname = '';
		}
		VTCacheUtils::updateBlockLabelWithId($blockname, $blockid);
	}
	return $blockname;
}

/**
 * Function to get the approximate difference between two date time values as string
 */
function dateDiffAsString($d1, $d2)
{
	$currentModule = vglobal('currentModule');

	$dateDiff = dateDiff($d1, $d2);

	$years = $dateDiff['years'];
	$months = $dateDiff['months'];
	$days = $dateDiff['days'];
	$hours = $dateDiff['hours'];
	$minutes = $dateDiff['minutes'];
	$seconds = $dateDiff['seconds'];

	if ($years > 0) {
		$diffString = "$years " . \App\Language::translate('LBL_YEARS', $currentModule);
	} elseif ($months > 0) {
		$diffString = "$months " . \App\Language::translate('LBL_MONTHS', $currentModule);
	} elseif ($days > 0) {
		$diffString = "$days " . \App\Language::translate('LBL_DAYS', $currentModule);
	} elseif ($hours > 0) {
		$diffString = "$hours " . \App\Language::translate('LBL_HOURS', $currentModule);
	} elseif ($minutes > 0) {
		$diffString = "$minutes " . \App\Language::translate('LBL_MINUTES', $currentModule);
	} else {
		$diffString = "$seconds " . \App\Language::translate('LBL_SECONDS', $currentModule);
	}
	return $diffString;
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

function getInventoryModules()
{
	$inventoryModules = [];
	return $inventoryModules;
}

/**
 * Function to get the list of Contacts related to an activity
 * @param Integer $activityId
 * @return Array $contactsList - List of Contact ids, mapped to Contact Names
 */
function getActivityRelatedContacts($activityId)
{
	$adb = PearDatabase::getInstance();

	$query = 'SELECT link FROM vtiger_activity WHERE activityid=?';
	$result = $adb->pquery($query, array($activityId));

	$noOfContacts = $adb->num_rows($result);
	$contactsList = [];
	for ($i = 0; $i < $noOfContacts; ++$i) {
		$contactId = $adb->query_result($result, $i, 'link');
		$displayValueArray = getEntityName('Contacts', $contactId);
		if (!empty($displayValueArray)) {
			foreach ($displayValueArray as $key => $field_value) {
				$contact_name = $field_value;
			}
		} else {
			$contact_name = '';
		}
		$contactsList[$contactId] = $contact_name;
	}
	return $contactsList;
}

/** Function to get the difference between 2 datetime strings or millisecond values */
function dateDiff($d1, $d2)
{
	$d1 = (is_string($d1) ? strtotime($d1) : $d1);
	$d2 = (is_string($d2) ? strtotime($d2) : $d2);

	$diffSecs = abs($d1 - $d2);
	$baseYear = min(date("Y", $d1), date("Y", $d2));
	$diff = mktime(0, 0, $diffSecs, 1, 1, $baseYear);
	return array(
		"years" => date("Y", $diff) - $baseYear,
		"months_total" => (date("Y", $diff) - $baseYear) * 12 + date("n", $diff) - 1,
		"months" => date("n", $diff) - 1,
		"days_total" => floor($diffSecs / (3600 * 24)),
		"days" => date("j", $diff) - 1,
		"hours_total" => floor($diffSecs / 3600),
		"hours" => date("G", $diff),
		"minutes_total" => floor($diffSecs / 60),
		"minutes" => (int) date("i", $diff),
		"seconds_total" => $diffSecs,
		"seconds" => (int) date("s", $diff)
	);
}

/** call back function to change the array values in to lower case */
function lower_array(&$string)
{
	$string = strtolower(trim($string));
}
