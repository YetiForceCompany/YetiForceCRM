<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

/*
 * Check for image existence in themes orelse
 * use the common one.
 */
// Let us create cache to improve performance
if (!isset($__cache_vtiger_imagepath)) {
	$__cache_vtiger_imagepath = Array();
}

function vtiger_imageurl($imagename, $themename)
{
	global $__cache_vtiger_imagepath;
	if ($__cache_vtiger_imagepath[$imagename]) {
		$imagepath = $__cache_vtiger_imagepath[$imagename];
	} else {
		$imagepath = false;
		// Check in theme specific folder
		if (file_exists("themes/$themename/images/$imagename")) {
			$imagepath = "themes/$themename/images/$imagename";
		} else if (file_exists("themes/images/$imagename")) {
			// Search in common image folder
			$imagepath = "themes/images/$imagename";
		} else {
			// Not found anywhere? Return whatever is sent
			$imagepath = $imagename;
		}
		$__cache_vtiger_imagepath[$imagename] = $imagepath;
	}
	return $imagepath;
}

/**
 * Get module name by id.
 */
function vtlib_getModuleNameById($tabid)
{
	$adb = PearDatabase::getInstance();
	$sqlresult = $adb->pquery("SELECT name FROM vtiger_tab WHERE tabid = ?", array($tabid));
	if ($adb->num_rows($sqlresult))
		return $adb->query_result($sqlresult, 0, 'name');
	return null;
}

/**
 * Get module names for which sharing access can be controlled.
 * NOTE: Ignore the standard modules which is already handled.
 */
function vtlib_getModuleNameForSharing()
{
	$adb = PearDatabase::getInstance();
	$std_modules = array('Calendar', 'Leads', 'Accounts', 'Contacts', 'Potentials',
		'HelpDesk', 'Campaigns', 'Quotes', 'PurchaseOrder', 'SalesOrder', 'Invoice', 'Events');
	$modulesList = getSharingModuleList($std_modules);
	return $modulesList;
}
/**
 * Cache the module active information for performance
 */
$__cache_module_activeinfo = Array();

/**
 * Fetch module active information at one shot, but return all the information fetched.
 */
function vtlib_prefetchModuleActiveInfo($force = true)
{
	global $__cache_module_activeinfo;

	// Look up if cache has information
	$tabrows = VTCacheUtils::lookupAllTabsInfo();

	// Initialize from DB if cache information is not available or force flag is set
	if ($tabrows === false || $force) {
		$adb = PearDatabase::getInstance();
		$tabres = $adb->query("SELECT * FROM vtiger_tab");
		$tabrows = array();
		if ($tabres) {
			while ($tabresrow = $adb->fetch_array($tabres)) {
				$tabrows[] = $tabresrow;
				$__cache_module_activeinfo[$tabresrow['name']] = $tabresrow['presence'];
			}
			// Update cache for further re-use
			VTCacheUtils::updateAllTabsInfo($tabrows);
		}
	}

	return $tabrows;
}

/**
 * Check if module is set active (or enabled)
 */
function vtlib_isModuleActive($module)
{
	global $adb, $__cache_module_activeinfo;

	if (in_array($module, vtlib_moduleAlwaysActive())) {
		return true;
	}

	if (!isset($__cache_module_activeinfo[$module])) {
		include 'user_privileges/tabdata.php';
		$tabId = $tab_info_array[$module];
		$presence = $tab_seq_array[$tabId];
		$__cache_module_activeinfo[$module] = $presence;
	} else {
		$presence = $__cache_module_activeinfo[$module];
	}

	$active = false;
	//Fix for http://trac.vtiger.com/cgi-bin/trac.cgi/ticket/7991
	if ($presence === 0 || $presence === '0')
		$active = true;

	return $active;
}

/**
 * Recreate user privileges files.
 */
function vtlib_RecreateUserPrivilegeFiles()
{
	$adb = PearDatabase::getInstance();
	$userres = $adb->query('SELECT id FROM vtiger_users WHERE deleted = 0');
	if ($userres && $adb->num_rows($userres)) {
		while ($userrow = $adb->fetch_array($userres)) {
			createUserPrivilegesfile($userrow['id']);
		}
	}
}

/**
 * Get list module names which are always active (cannot be disabled)
 */
function vtlib_moduleAlwaysActive()
{
	$modules = Array(
		'Administration', 'CustomView', 'Settings', 'Users', 'Migration',
		'Utilities', 'uploads', 'Import', 'System', 'com_vtiger_workflow', 'PickList'
	);
	return $modules;
}

/**
 * Toggle the module (enable/disable)
 */
function vtlib_toggleModuleAccess($module, $enable_disable)
{
	global $adb, $__cache_module_activeinfo;

	include_once('vtlib/Vtiger/Module.php');

	$event_type = false;

	if ($enable_disable === true) {
		$enable_disable = 0;
		$event_type = Vtiger_Module::EVENT_MODULE_ENABLED;
	} else if ($enable_disable === false) {
		$enable_disable = 1;
		$event_type = Vtiger_Module::EVENT_MODULE_DISABLED;
	}

	$adb->pquery("UPDATE vtiger_tab set presence = ? WHERE name = ?", array($enable_disable, $module));

	$__cache_module_activeinfo[$module] = $enable_disable;

	create_tab_data_file();
	vtlib_RecreateUserPrivilegeFiles();
	Vtiger_Module::fireEvent($module, $event_type);
}

/**
 * Get list of module with current status which can be controlled.
 */
function vtlib_getToggleModuleInfo()
{
	$adb = PearDatabase::getInstance();

	$modinfo = Array();

	$sqlresult = $adb->query("SELECT name, presence, customized, isentitytype FROM vtiger_tab WHERE name NOT IN ('Users','Home') AND presence IN (0,1) ORDER BY name");
	$num_rows = $adb->num_rows($sqlresult);
	for ($idx = 0; $idx < $num_rows; ++$idx) {
		$module = $adb->query_result($sqlresult, $idx, 'name');
		$presence = $adb->query_result($sqlresult, $idx, 'presence');
		$customized = $adb->query_result($sqlresult, $idx, 'customized');
		$isentitytype = $adb->query_result($sqlresult, $idx, 'isentitytype');
		$hassettings = file_exists("modules/$module/Settings.php");

		$modinfo[$module] = Array('customized' => $customized, 'presence' => $presence, 'hassettings' => $hassettings, 'isentitytype' => $isentitytype);
	}
	return $modinfo;
}

/**
 * Get list of language and its current status.
 */
function vtlib_getToggleLanguageInfo()
{
	$adb = PearDatabase::getInstance();

	// The table might not exists!
	$old_dieOnError = $adb->dieOnError;
	$adb->dieOnError = false;

	$langinfo = Array();
	$sqlresult = $adb->query("SELECT * FROM vtiger_language");
	if ($sqlresult) {
		for ($idx = 0; $idx < $adb->num_rows($sqlresult); ++$idx) {
			$row = $adb->fetch_array($sqlresult);
			$langinfo[$row['prefix']] = Array('label' => $row['label'], 'active' => $row['active']);
		}
	}
	$adb->dieOnError = $old_dieOnError;
	return $langinfo;
}

/**
 * Toggle the language (enable/disable)
 */
function vtlib_toggleLanguageAccess($langprefix, $enable_disable)
{
	$adb = PearDatabase::getInstance();

	// The table might not exists!
	$old_dieOnError = $adb->dieOnError;
	$adb->dieOnError = false;

	if ($enable_disable === true)
		$enable_disable = 1;
	else if ($enable_disable === false)
		$enable_disable = 0;

	$adb->pquery('UPDATE vtiger_language set active = ? WHERE prefix = ?', Array($enable_disable, $langprefix));

	$adb->dieOnError = $old_dieOnError;
}
/**
 * Get help information set for the module fields.
 */
/*
  function vtlib_getFieldHelpInfo($module) {
  $adb = PearDatabase::getInstance();
  $fieldhelpinfo = Array();
  if(in_array('helpinfo', $adb->getColumnNames('vtiger_field'))) {
  $result = $adb->pquery('SELECT fieldname,helpinfo FROM vtiger_field WHERE tabid=?', Array(getTabid($module)));
  if($result && $adb->num_rows($result)) {
  while($fieldrow = $adb->fetch_array($result)) {
  $helpinfo = decode_html($fieldrow['helpinfo']);
  if(!empty($helpinfo)) {
  $fieldhelpinfo[$fieldrow['fieldname']] = getTranslatedString($helpinfo, $module);
  }
  }
  }
  }
  return $fieldhelpinfo;
  }
 */

/**
 * Setup mandatory (requried) module variable values in the module class.
 */
function vtlib_setup_modulevars($module, $focus)
{

	$checkfor = Array('table_name', 'table_index', 'related_tables', 'popup_fields', 'IsCustomModule');
	foreach ($checkfor as $check) {
		if (!isset($focus->$check))
			$focus->$check = __vtlib_get_modulevar_value($module, $check);
	}
}

function __vtlib_get_modulevar_value($module, $varname)
{
	$mod_var_mapping = Array(
		'Accounts' =>
		Array(
			'IsCustomModule' => false,
			'table_name' => 'vtiger_account',
			'table_index' => 'accountid',
			// related_tables variable should define the association (relation) between dependent tables
			// FORMAT: related_tablename => Array ( related_tablename_column[, base_tablename, base_tablename_column] )
			// Here base_tablename_column should establish relation with related_tablename_column
			// NOTE: If base_tablename and base_tablename_column are not specified, it will default to modules (table_name, related_tablename_column)
			'related_tables' => Array(
				'vtiger_accountaddress' => Array('accountaddressid', 'vtiger_account', 'accountid'),
				'vtiger_accountscf' => Array('accountid', 'vtiger_account', 'accountid'),
			),
			'popup_fields' => Array('accountname'), // TODO: Add this initialization to all the standard module
		),
		'Contacts' =>
		Array(
			'IsCustomModule' => false,
			'table_name' => 'vtiger_contactdetails',
			'table_index' => 'contactid',
			'related_tables' => Array(
				'vtiger_account' => Array('parentid'),
				//REVIEW: Added these tables for displaying the data into relatedlist (based on configurable fields)
				'vtiger_contactaddress' => Array('contactaddressid', 'vtiger_contactdetails', 'contactid'),
				'vtiger_contactsubdetails' => Array('contactsubscriptionid', 'vtiger_contactdetails', 'contactid'),
				'vtiger_customerdetails' => Array('customerid', 'vtiger_contactdetails', 'contactid'),
				'vtiger_contactscf' => Array('contactid', 'vtiger_contactdetails', 'contactid')
			),
			'popup_fields' => Array('lastname'),
		),
		'Leads' =>
		Array(
			'IsCustomModule' => false,
			'table_name' => 'vtiger_leaddetails',
			'table_index' => 'leadid',
			'related_tables' => Array(
				'vtiger_leadsubdetails' => Array('leadsubscriptionid', 'vtiger_leaddetails', 'leadid'),
				'vtiger_leadaddress' => Array('leadaddressid', 'vtiger_leaddetails', 'leadid'),
				'vtiger_leadscf' => Array('leadid', 'vtiger_leaddetails', 'leadid'),
			),
			'popup_fields' => Array('company'),
		),
		'Campaigns' =>
		Array(
			'IsCustomModule' => false,
			'table_name' => 'vtiger_campaign',
			'table_index' => 'campaignid',
			'popup_fields' => Array('campaignname'),
		),
		'Potentials' =>
		Array(
			'IsCustomModule' => false,
			'table_name' => 'vtiger_potential',
			'table_index' => 'potentialid',
			// NOTE: UIType 10 is being used instead of direct relationship from 5.1.0
			//'related_tables' => Array ('vtiger_account' => Array('accountid')),
			'popup_fields' => Array('potentialname'),
			'related_tables' => Array(
				'vtiger_potentialscf' => Array('potentialid', 'vtiger_potential', 'potentialid'),
			),
		),
		'Quotes' =>
		Array(
			'IsCustomModule' => false,
			'table_name' => 'vtiger_quotes',
			'table_index' => 'quoteid',
			'related_tables' => Array('vtiger_account' => Array('accountid')),
			'popup_fields' => Array('subject'),
		),
		'SalesOrder' =>
		Array(
			'IsCustomModule' => false,
			'table_name' => 'vtiger_salesorder',
			'table_index' => 'salesorderid',
			'related_tables' => Array('vtiger_account' => Array('accountid')),
			'popup_fields' => Array('subject'),
		),
		'PurchaseOrder' =>
		Array(
			'IsCustomModule' => false,
			'table_name' => 'vtiger_purchaseorder',
			'table_index' => 'purchaseorderid',
			'popup_fields' => Array('subject'),
		),
		'Invoice' =>
		Array(
			'IsCustomModule' => false,
			'table_name' => 'vtiger_invoice',
			'table_index' => 'invoiceid',
			'popup_fields' => Array('subject'),
			'related_tables' => Array(
				'vtiger_invoicecf' => Array('invoiceid', 'vtiger_invoice', 'invoiceid')
			),
		),
		'HelpDesk' =>
		Array(
			'IsCustomModule' => false,
			'table_name' => 'vtiger_troubletickets',
			'table_index' => 'ticketid',
			'related_tables' => Array('vtiger_ticketcf' => Array('ticketid')),
			'popup_fields' => Array('ticket_title')
		),
		'Faq' =>
		Array(
			'IsCustomModule' => false,
			'table_name' => 'vtiger_faq',
			'table_index' => 'id',
		),
		'Documents' =>
		Array(
			'IsCustomModule' => false,
			'table_name' => 'vtiger_notes',
			'table_index' => 'notesid',
		),
		'Products' =>
		Array(
			'IsCustomModule' => false,
			'table_name' => 'vtiger_products',
			'table_index' => 'productid',
			'popup_fields' => Array('productname'),
		),
		'PriceBooks' =>
		Array(
			'IsCustomModule' => false,
			'table_name' => 'vtiger_pricebook',
			'table_index' => 'pricebookid',
		),
		'Vendors' =>
		Array(
			'IsCustomModule' => false,
			'table_name' => 'vtiger_vendor',
			'table_index' => 'vendorid',
			'popup_fields' => Array('vendorname'),
		),
		'Project' =>
		Array(
			'IsCustomModule' => false,
			'table_name' => 'vtiger_project',
			'table_index' => 'projectid',
			'related_tables' => Array(
				'vtiger_projectcf' => Array('projectid', 'vtiger_project', 'projectid')
			),
		),
		'ProjectMilestone' =>
		Array(
			'IsCustomModule' => false,
			'table_name' => 'vtiger_projectmilestone',
			'table_index' => 'projectmilestoneid',
			'related_tables' => Array(
				'vtiger_projectmilestonecf' => Array('projectmilestoneid', 'vtiger_projectmilestone', 'projectmilestoneid')
			),
		),
		'ProjectTask' =>
		Array(
			'IsCustomModule' => false,
			'table_name' => 'vtiger_projecttask',
			'table_index' => 'projecttaskid',
			'related_tables' => Array(
				'vtiger_projecttaskcf' => Array('projecttaskid', 'vtiger_projecttask', 'projecttaskid')
			),
		),
		'Services' =>
		Array(
			'IsCustomModule' => false,
			'table_name' => 'vtiger_service',
			'table_index' => 'serviceid',
			'related_tables' => Array(
				'vtiger_servicecf' => Array('serviceid')
			),
		),
		'ServiceContracts' =>
		Array(
			'IsCustomModule' => false,
			'table_name' => 'vtiger_servicecontracts',
			'table_index' => 'servicecontractsid',
			'related_tables' => Array(
				'vtiger_servicecontractscf' => Array('servicecontractsid')
			),
		),
		'Assets' =>
		Array(
			'IsCustomModule' => false,
			'table_name' => 'vtiger_assets',
			'table_index' => 'assetsid',
			'related_tables' => Array(
				'vtiger_assetscf' => Array('assetsid')
			),
		)
	);
	return $mod_var_mapping[$module][$varname];
}

/**
 * Convert given text input to singular.
 */
function vtlib_tosingular($text)
{
	$lastpos = strripos($text, 's');
	if ($lastpos == strlen($text) - 1)
		return substr($text, 0, -1);
	return $text;
}

/**
 * Get picklist values that is accessible by all roles.
 */
function vtlib_getPicklistValues_AccessibleToAll($fieldColumnname)
{
	$log = vglobal('log');
	$log->debug('Entering ' . __METHOD__ . '(' . print_r($fieldColumnname, true) . ') method ...');
	$adb = PearDatabase::getInstance();

	$columnname = $adb->sql_escape_string($fieldColumnname);
	$tablename = 'vtiger_' . $fieldColumnname;
	
	// Gather all the roles (except H1 which is organization role)
	$roleres = $adb->query("SELECT roleid FROM vtiger_role WHERE roleid != 'H1'");
	$roleresCount = $adb->num_rows($roleres);
	$allroles =[];
	if ($roleresCount) {
		for ($index = 0; $index < $roleresCount; ++$index)
			$allroles[] = $adb->query_result($roleres, $index, 'roleid');
	}
	sort($allroles);

	// Get all the picklist values associated to roles (except H1 - organization role).
	$picklistres = $adb->query(
		"SELECT $columnname as pickvalue, roleid FROM $tablename
		INNER JOIN vtiger_role2picklist ON $tablename.picklist_valueid=vtiger_role2picklist.picklistvalueid
		WHERE roleid != 'H1'");

	$picklistresCount = $adb->num_rows($picklistres);

	$picklistval_roles = Array();
	if ($picklistresCount) {
		for ($index = 0; $index < $picklistresCount; ++$index) {
			$picklistval = $adb->query_result($picklistres, $index, 'pickvalue');
			$pickvalroleid = $adb->query_result($picklistres, $index, 'roleid');
			$picklistval_roles[$picklistval][] = $pickvalroleid;
		}
	}
	// Collect picklist value which is associated to all the roles.
	$allrolevalues = Array();
	foreach ($picklistval_roles as $picklistval => $pickvalroles) {
		sort($pickvalroles);
		$diff = array_diff($pickvalroles, $allroles);
		if (empty($diff))
			$allrolevalues[] = $picklistval;
	}

	$log->debug('Exiting ' . __METHOD__ . ' method ...');
	return $allrolevalues;
}

/**
 * Get all picklist values for a non-standard picklist type.
 */
function vtlib_getPicklistValues($columnname)
{
	$adb = PearDatabase::getInstance();

	$tablename = "vtiger_$columnname";
	$tablename = $adb->quote($tablename, false);

	$picklistres = $adb->query("SELECT $columnname as pickvalue FROM $tablename");
	$picklistresCount = $adb->num_rows($picklistres);

	$picklistvalues = Array();
	if ($picklistresCount) {
		for ($index = 0; $index < $picklistresCount; ++$index) {
			$picklistvalues[] = $adb->query_result($picklistres, $index, 'pickvalue');
		}
	}
	return $picklistvalues;
}

/**
 * Check for custom module by its name.
 */
function vtlib_isCustomModule($moduleName)
{
	$moduleFile = "modules/$moduleName/$moduleName.php";
	if (file_exists($moduleFile)) {
		if (function_exists('checkFileAccessForInclusion')) {
			checkFileAccessForInclusion($moduleFile);
		}
		include_once($moduleFile);
		$focus = new $moduleName();
		return (isset($focus->IsCustomModule) && $focus->IsCustomModule);
	}
	return false;
}

/**
 * Get module specific smarty template path.
 */
function vtlib_getModuleTemplate($module, $templateName)
{
	return ("modules/$module/$templateName");
}

/**
 * Check if give path is writeable.
 */
function vtlib_isWriteable($path)
{
	if (is_dir($path)) {
		return vtlib_isDirWriteable($path);
	} else {
		return is_writable($path);
	}
}

/**
 * Check if given directory is writeable.
 * NOTE: The check is made by trying to create a random file in the directory.
 */
function vtlib_isDirWriteable($dirpath)
{
	if (is_dir($dirpath)) {
		do {
			$tmpfile = 'vtiger' . time() . '-' . rand(1, 1000) . '.tmp';
			// Continue the loop unless we find a name that does not exists already.
			$usefilename = "$dirpath/$tmpfile";
			if (!file_exists($usefilename))
				break;
		} while (true);
		$fh = @fopen($usefilename, 'a');
		if ($fh) {
			fclose($fh);
			unlink($usefilename);
			return true;
		}
	}
	return false;
}
/** HTML Purifier global instance */
$__htmlpurifier_instance = false;

/**
 * Purify (Cleanup) malicious snippets of code from the input
 *
 * @param String $value
 * @param Boolean $ignore Skip cleaning of the input
 * @return String
 */
function vtlib_purify($input, $ignore = false)
{
	global $__htmlpurifier_instance, $root_directory, $default_charset;

	static $purified_cache = array();
	$value = $input;

	if (!is_array($input)) {
		$md5OfInput = md5($input);
		if (array_key_exists($md5OfInput, $purified_cache)) {
			$value = $purified_cache[$md5OfInput];
			//to escape cleaning up again
			$ignore = true;
		}
	} else {
		$md5OfInput = md5(json_encode($input));
	}
	$use_charset = $default_charset;
	$use_root_directory = $root_directory;


	if (!$ignore) {
		// Initialize the instance if it has not yet done
		if ($__htmlpurifier_instance == false) {
			if (empty($use_charset))
				$use_charset = 'UTF-8';
			if (empty($use_root_directory))
				$use_root_directory = dirname(__FILE__) . '/../..';

			include_once ('libraries/htmlpurifier/library/HTMLPurifier.auto.php');

			$config = HTMLPurifier_Config::createDefault();
			$config->set('Core', 'Encoding', $use_charset);
			$config->set('Cache', 'SerializerPath', "$use_root_directory/cache/vtlib");

			$__htmlpurifier_instance = new HTMLPurifier($config);
		}
		if ($__htmlpurifier_instance) {
			// Composite type
			if (is_array($input)) {
				$value = array();
				foreach ($input as $k => $v) {
					$value[$k] = vtlib_purify($v, $ignore);
				}
			} else { // Simple type
				$value = $__htmlpurifier_instance->purify($input);
			}
		}
		$purified_cache[$md5OfInput] = $value;
	}

	$value = str_replace('&amp;', '&', $value);
	return $value;
}

/**
 * Function to return the valid SQl input.
 * @param <String> $string
 * @param <Boolean> $skipEmpty Skip the check if string is empty.
 * @return <String> $string/false
 */
function vtlib_purifyForSql($string, $skipEmpty = true)
{
	$pattern = "/^[_a-zA-Z0-9.,]+$/";
	if ((empty($string) && $skipEmpty) || preg_match($pattern, $string)) {
		return $string;
	}
	return false;
}

/**
 * Process the UI Widget requested
 * @param Vtiger_Link $widgetLinkInfo
 * @param Current Smarty Context $context
 * @return
 */
function vtlib_process_widget($widgetLinkInfo, $context = false)
{
	if (preg_match("/^block:\/\/(.*)/", $widgetLinkInfo->linkurl, $matches)) {
		list($widgetControllerClass, $widgetControllerClassFile) = explode(':', $matches[1]);
		if (!class_exists($widgetControllerClass)) {
			checkFileAccessForInclusion($widgetControllerClassFile);
			include_once $widgetControllerClassFile;
		}
		if (class_exists($widgetControllerClass)) {
			$widgetControllerInstance = new $widgetControllerClass;
			$widgetInstance = $widgetControllerInstance->getWidget($widgetLinkInfo->linklabel);
			if ($widgetInstance) {
				return $widgetInstance->process($context);
			}
		}
	}
	return "";
}

function vtlib_module_icon($modulename)
{
	if ($modulename == 'Events') {
		return "modules/Calendar/Events.png";
	}
	if (file_exists("modules/$modulename/$modulename.png")) {
		return "modules/$modulename/$modulename.png";
	}
	return "modules/Vtiger/Vtiger.png";
}

?>
