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


function vtiger_imageurl($imagename, $themename)
{
	static $cacheVtigerImagepath = [];
	if ($cacheVtigerImagepath[$imagename]) {
		$imagepath = $cacheVtigerImagepath[$imagename];
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
		$cacheVtigerImagepath[$imagename] = $imagepath;
	}
	return $imagepath;
}

/**
 * Fetch module active information at one shot, but return all the information fetched.
 */
function vtlib_prefetchModuleActiveInfo($force = true)
{
	// Look up if cache has information
	$tabrows = VTCacheUtils::lookupAllTabsInfo();

	// Initialize from DB if cache information is not available or force flag is set
	if ($tabrows === false || $force) {
		$adb = PearDatabase::getInstance();
		$tabres = $adb->query("SELECT * FROM vtiger_tab");
		$tabrows = [];
		if ($tabres) {
			while ($tabresrow = $adb->fetch_array($tabres)) {
				$tabrows[] = $tabresrow;
			}
			// Update cache for further re-use
			VTCacheUtils::updateAllTabsInfo($tabrows);
		}
	}

	return $tabrows;
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
			'popup_fields' => Array('accountname'),
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
 * Get picklist values that is accessible by all roles.
 */
function vtlib_getPicklistValues_AccessibleToAll($fieldColumnname)
{

	\App\Log::trace('Entering ' . __METHOD__ . '(' . print_r($fieldColumnname, true) . ') method ...');
	$adb = PearDatabase::getInstance();

	$columnname = $adb->quote($fieldColumnname, false);
	$tablename = 'vtiger_' . $fieldColumnname;
	// Gather all the roles (except H1 which is organization role)
	$roleres = $adb->query("SELECT roleid FROM vtiger_role WHERE roleid != 'H1'");
	$roleresCount = $adb->num_rows($roleres);
	$allroles = [];
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

	$picklistval_roles = [];
	if ($picklistresCount) {
		while ($row = $adb->getRow($picklistres)) {
			$picklistval_roles[$row['pickvalue']][] = $row['roleid'];
		}
	}
	// Collect picklist value which is associated to all the roles.
	$allrolevalues = [];
	foreach ($picklistval_roles as $picklistval => $pickvalroles) {
		sort($pickvalroles);
		$diff = array_diff($pickvalroles, $allroles);
		if (empty($diff))
			$allrolevalues[] = $picklistval;
	}

	\App\Log::trace('Exiting ' . __METHOD__ . ' method ...');
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

	$picklistvalues = [];
	if ($picklistresCount) {
		for ($index = 0; $index < $picklistresCount; ++$index) {
			$picklistvalues[] = $adb->query_result($picklistres, $index, 'pickvalue');
		}
	}
	return $picklistvalues;
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
