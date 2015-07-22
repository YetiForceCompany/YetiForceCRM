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
 * Contributor(s): ______________________________________.
 * ****************************************************************************** */

require_once('config/config.php');
require_once('include/logging.php');
require_once('include/database/PearDatabase.php');
require_once('modules/Accounts/Accounts.php');
require_once('modules/Contacts/Contacts.php');
require_once('modules/Leads/Leads.php');
require_once('modules/Contacts/Contacts.php');
require_once('modules/Emails/Emails.php');
require_once('modules/Calendar/Activity.php');
require_once('modules/Documents/Documents.php');
require_once('modules/Potentials/Potentials.php');
require_once('modules/Users/Users.php');
require_once('modules/Products/Products.php');
require_once('modules/HelpDesk/HelpDesk.php');
require_once('modules/Vendors/Vendors.php');
require_once('include/utils/UserInfoUtil.php');
require_once('modules/CustomView/CustomView.php');
require_once 'modules/PickList/PickListUtils.php';
require_once('modules/Invoice/Invoice.php');
require_once('modules/Quotes/Quotes.php');
require_once('modules/PurchaseOrder/PurchaseOrder.php');
require_once('modules/SalesOrder/SalesOrder.php');

// Set the current language and the language strings, if not already set.
setCurrentLanguage();

global $allow_exports, $app_strings;

session_start();

$current_user = new Users();

if (isset($_SESSION['authenticated_user_id'])) {
	$result = $current_user->retrieveCurrentUserInfoFromFile($_SESSION['authenticated_user_id'], "Users");
	if ($result == null) {
		session_destroy();
		header("Location: index.php?action=Login&module=Users");
		exit;
	}
}

//Security Check
if (isPermitted($_REQUEST['module'], "Export") == "no") {
	$allow_exports = "none";
}

if ($allow_exports == 'none' || ( $allow_exports == 'admin' && !is_admin($current_user) )) {

	?>
	<script type='text/javascript'>
		alert("<?php echo $app_strings['NOT_PERMITTED_TO_EXPORT'] ?>");
		window.location = "index.php?module=<?php echo vtlib_purify($_REQUEST['module']) ?>&action=index";
	</script>

	<?php exit; ?>
	<?php
}

/* * Function convert line breaks to space in description during export
 * Pram $str - text
 * retrun type string
 */

function br2nl_vt($str)
{
	$log = vglobal('log');
	$log->debug("Entering br2nl_vt(" . $str . ") method ...");
	$str = preg_replace("/(\r\n)/", " ", $str);
	$log->debug("Exiting br2nl_vt method ...");
	return $str;
}

/**
 * This function exports all the data for a given module
 * Param $type - module name
 * Return type text
 */
function export($type)
{
	global $log, $list_max_entries_per_page;
	$log->debug("Entering export(" . $type . ") method ...");
	$adb = PearDatabase::getInstance();

	$focus = 0;
	$content = '';

	if ($type != "") {
		// vtlib customization: Hook to dynamically include required module file.
		// Refer to the logic in setting $currentModule in index.php
		$focus = CRMEntity::getInstance($type);
	}
	$log = LoggerManager::getLogger('export_' . $type);
	$db = PearDatabase::getInstance();

	$oCustomView = new CustomView("$type");
	$viewid = $oCustomView->getViewId("$type");
	$sorder = $focus->getSortOrder();
	$order_by = $focus->getOrderBy();

	$search_type = vtlib_purify($_REQUEST['search_type']);
	$export_data = vtlib_purify($_REQUEST['export_data']);

	if (isset($_SESSION['export_where']) && $_SESSION['export_where'] != '' && $search_type == 'includesearch') {
		$where = $_SESSION['export_where'];
	}

	$query = $focus->create_export_query($where);
	if ($search_type != 'includesearch' && $type != 'Calendar') {
		$stdfiltersql = $oCustomView->getCVStdFilterSQL($viewid);
		$advfiltersql = $oCustomView->getCVAdvFilterSQL($viewid);
		if (isset($stdfiltersql) && $stdfiltersql != '') {
			$query .= ' and ' . $stdfiltersql;
		}
		if (isset($advfiltersql) && $advfiltersql != '') {
			$query .= ' and ' . $advfiltersql;
		}
	}
	$params = array();

	list($idstring, $export_data) = split("#@@#", getExportRecordIds($type, $viewid, $_REQUEST));

	if (($search_type == 'withoutsearch' || $search_type == 'includesearch') && $export_data == 'selecteddata') {
		$idstring = getSelectedRecords($_REQUEST, $type, $idstring, vtlib_purify($_REQUEST['excludedRecords']));
		if ($type == 'Accounts' && count($idstring) > 0) {
			$query .= ' and vtiger_account.accountid in (' . generateQuestionMarks($idstring) . ')';
			array_push($params, $idstring);
		} elseif ($type == 'Contacts' && count($idstring) > 0) {
			$query .= ' and vtiger_contactdetails.contactid in (' . generateQuestionMarks($idstring) . ')';
			array_push($params, $idstring);
		} elseif ($type == 'Potentials' && count($idstring) > 0) {
			$query .= ' and vtiger_potential.potentialid in (' . generateQuestionMarks($idstring) . ')';
			array_push($params, $idstring);
		} elseif ($type == 'Leads' && count($idstring) > 0) {
			$query .= ' and vtiger_leaddetails.leadid in (' . generateQuestionMarks($idstring) . ')';
			array_push($params, $idstring);
		} elseif ($type == 'Products' && count($idstring) > 0) {
			$query .= ' and vtiger_products.productid in (' . generateQuestionMarks($idstring) . ')';
			array_push($params, $idstring);
		} elseif ($type == 'Documents' && count($idstring) > 0) {
			$query .= ' and vtiger_notes.notesid in (' . generateQuestionMarks($idstring) . ')';
			array_push($params, $idstring);
		} elseif ($type == 'HelpDesk' && count($idstring) > 0) {
			$query .= ' and vtiger_troubletickets.ticketid in (' . generateQuestionMarks($idstring) . ')';
			array_push($params, $idstring);
		} elseif ($type == 'Vendors' && count($idstring) > 0) {
			$query .= ' and vtiger_vendor.vendorid in (' . generateQuestionMarks($idstring) . ')';
			array_push($params, $idstring);
		} elseif ($type == 'Invoice' && count($idstring) > 0) {
			$query .= ' and vtiger_invoice.invoiceid in (' . generateQuestionMarks($idstring) . ')';
			array_push($params, $idstring);
		} elseif ($type == 'Quotes' && count($idstring) > 0) {
			$query .= ' and vtiger_quotes.quoteid in (' . generateQuestionMarks($idstring) . ')';
			array_push($params, $idstring);
		} elseif ($type == 'SalesOrder' && count($idstring) > 0) {
			$query .= ' and vtiger_salesorder.salesorderid in (' . generateQuestionMarks($idstring) . ')';
			array_push($params, $idstring);
		} elseif ($type == 'PurchaseOrder' && count($idstring) > 0) {
			$query .= ' and vtiger_purchaseorder.purchaseorderid in (' . generateQuestionMarks($idstring) . ')';
			array_push($params, $idstring);
		} else if (count($idstring) > 0) {
			// vtlib customization: Hook to make the export feature available for custom modules.
			$query .= " and $focus->table_name.$focus->table_index in (" . generateQuestionMarks($idstring) . ')';
			array_push($params, $idstring);
			// END
		}
	}

	if (isset($order_by) && $order_by != '') {
		if ($order_by == 'smownerid') {
			$query .= ' ORDER BY user_name ' . $sorder;
		} elseif ($order_by == 'lastname' && $type == 'Documents') {
			$query .= ' ORDER BY vtiger_contactdetails.lastname  ' . $sorder;
		} elseif ($order_by == 'crmid' && $type == 'HelpDesk') {
			$query .= ' ORDER BY vtiger_troubletickets.ticketid  ' . $sorder;
		} else {
			$tablename = getTableNameForField($type, $order_by);
			$tablename = (($tablename != '') ? ($tablename . ".") : '');
			if ($adb->isPostgres()) {
				$query .= ' GROUP BY ' . $tablename . $order_by;
			}
			$query .= ' ORDER BY ' . $tablename . $order_by . ' ' . $sorder;
		}
	}

	if ($export_data == 'currentpage') {
		$current_page = ListViewSession::getCurrentPage($type, $viewid);
		$limit_start_rec = ($current_page - 1) * $list_max_entries_per_page;
		if ($limit_start_rec < 0)
			$limit_start_rec = 0;
		$query .= ' LIMIT ' . $limit_start_rec . ',' . $list_max_entries_per_page;
	}

	$result = $adb->pquery($query, $params, true, "Error exporting $type: " . "<BR>$query");
	$fields_array = $adb->getFieldsArray($result);
	$fields_array = array_diff($fields_array, array("user_name"));

	$__processor = new ExportUtils($type, $fields_array);

	// Translated the field names based on the language used.
	$translated_fields_array = array();
	for ($i = 0; $i < count($fields_array); $i++) {
		$translated_fields_array[$i] = getTranslatedString($fields_array[$i], $type);
	}
	$header = implode("\",\"", array_values($translated_fields_array));
	$header = "\"" . $header;
	$header .= "\"\r\n";

	/** Output header information */
	echo $header;

	$column_list = implode(",", array_values($fields_array));

	while ($val = $adb->fetchByAssoc($result, -1, false)) {
		$new_arr = array();
		$val = $__processor->sanitizeValues($val);
		foreach ($val as $key => $value) {
			if ($type == 'Documents' && $key == 'description') {
				$value = strip_tags($value);
				$value = str_replace('&nbsp;', '', $value);
				array_push($new_arr, $value);
			} elseif ($key != "user_name") {
				// Let us provide the module to transform the value before we save it to CSV file
				$value = $focus->transform_export_value($key, $value);

				array_push($new_arr, preg_replace("/\"/", "\"\"", $value));
			}
		}
		$line = implode("\",\"", $new_arr);
		$line = "\"" . $line;
		$line .= "\"\r\n";

		/** Output each row information */
		echo $line;
	}
	$log->debug("Exiting export method ...");
	return true;
}
/** Send the output header and invoke function for contents output */
$moduleName = vtlib_purify($_REQUEST['module']);
$moduleName = getTranslatedString($moduleName, $moduleName);
$moduleName = str_replace(" ", "_", $moduleName);
header("Content-Disposition:attachment;filename=$moduleName.csv");
header("Content-Type:text/csv;charset=UTF-8");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: post-check=0, pre-check=0", false);

export(vtlib_purify($_REQUEST['module']));

exit;

/**
 * this class will provide utility functions to process the export data.
 * this is to make sure that the data is sanitized before sending for export
 */
class ExportUtils
{

	var $fieldsArr = array();
	var $picklistValues = array();

	function ExportUtils($module, $fields_array)
	{
		self::__init($module, $fields_array);
	}

	function __init($module, $fields_array)
	{
		$infoArr = self::getInformationArray($module);

		//attach extra fields related information to the fields_array; this will be useful for processing the export data
		foreach ($infoArr as $fieldname => $fieldinfo) {
			if (in_array($fieldinfo["fieldlabel"], $fields_array)) {
				$this->fieldsArr[$fieldname] = $fieldinfo;
			}
		}
	}

	/**
	 * this function takes in an array of values for an user and sanitizes it for export
	 * @param array $arr - the array of values
	 */
	function sanitizeValues($arr)
	{
		global $current_user, $adb;
		$roleid = fetchUserRole($current_user->id);

		foreach ($arr as $fieldlabel => &$value) {
			$fieldInfo = $this->fieldsArr[$fieldlabel];

			$uitype = $fieldInfo['uitype'];
			$fieldname = $fieldInfo['fieldname'];
			if ($uitype == 15 || $uitype == 16 || $uitype == 33) {
				//picklists
				if (empty($this->picklistValues[$fieldname])) {
					$this->picklistValues[$fieldname] = getAssignedPicklistValues($fieldname, $roleid, $adb);
				}
				$value = trim($value);
			} elseif ($uitype == 10) {
				//have to handle uitype 10
				$value = trim($value);
				if (!empty($value)) {
					$parent_module = getSalesEntityType($value);
					$displayValueArray = getEntityName($parent_module, $value);
					if (!empty($displayValueArray)) {
						foreach ($displayValueArray as $k => $v) {
							$displayValue = $v;
						}
					}
					if (!empty($parent_module) && !empty($displayValue)) {
						$value = $parent_module . "::::" . $displayValue;
					} else {
						$value = "";
					}
				} else {
					$value = '';
				}
			} elseif ($uitype == 71) {
				$value = CurrencyField::convertToUserFormat($value);
			} elseif ($uitype == 72) {
				$value = CurrencyField::convertToUserFormat($value, null, true, true);
			} elseif ($uitype == 7 && $fieldInfo['typeofdata'] == 'N~O' || $uitype == 9) {
				$value = decimalFormat($value);
			}
		}
		return $arr;
	}

	/**
	 * this function takes in a module name and returns the field information for it
	 */
	function getInformationArray($module)
	{
		require_once 'include/utils/utils.php';
		$adb = PearDatabase::getInstance();
		$tabid = getTabid($module);

		$result = $adb->pquery("select * from vtiger_field where tabid=?", array($tabid));
		$count = $adb->num_rows($result);
		$arr = array();
		$data = array();

		for ($i = 0; $i < $count; $i++) {
			$arr['uitype'] = $adb->query_result($result, $i, "uitype");
			$arr['fieldname'] = $adb->query_result($result, $i, "fieldname");
			$arr['columnname'] = $adb->query_result($result, $i, "columnname");
			$arr['tablename'] = $adb->query_result($result, $i, "tablename");
			$arr['fieldlabel'] = $adb->query_result($result, $i, "fieldlabel");
			$arr['typeofdata'] = $adb->query_result($result, $i, "typeofdata");
			$fieldlabel = strtolower($arr['fieldlabel']);
			$data[$fieldlabel] = $arr;
		}
		return $data;
	}
}

?>
