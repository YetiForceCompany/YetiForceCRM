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
			while ($tabresrow = $adb->fetchArray($tabres)) {
				$tabrows[] = $tabresrow;
			}
			// Update cache for further re-use
			VTCacheUtils::updateAllTabsInfo($tabrows);
		}
	}

	return $tabrows;
}

/**
 * Setup mandatory (requried) module variable values in the module class.
 */
function vtlib_setup_modulevars($module, $focus)
{

	$checkfor = ['table_name', 'table_index', 'related_tables', 'popup_fields', 'IsCustomModule'];
	foreach ($checkfor as $check) {
		if (!isset($focus->$check))
			$focus->$check = __vtlib_get_modulevar_value($module, $check);
	}
}

function __vtlib_get_modulevar_value($module, $varname)
{
	$mod_var_mapping = [
		'Accounts' =>
		[
			'IsCustomModule' => false,
			'table_name' => 'vtiger_account',
			'table_index' => 'accountid',
			// related_tables variable should define the association (relation) between dependent tables
			// FORMAT: related_tablename => Array ( related_tablename_column[, base_tablename, base_tablename_column] )
			// Here base_tablename_column should establish relation with related_tablename_column
			// NOTE: If base_tablename and base_tablename_column are not specified, it will default to modules (table_name, related_tablename_column)
			'related_tables' => [
				'vtiger_accountaddress' => ['accountaddressid', 'vtiger_account', 'accountid'],
				'vtiger_accountscf' => ['accountid', 'vtiger_account', 'accountid'],
			],
			'popup_fields' => ['accountname'],
		],
		'Contacts' =>
		[
			'IsCustomModule' => false,
			'table_name' => 'vtiger_contactdetails',
			'table_index' => 'contactid',
			'related_tables' => [
				'vtiger_account' => ['parentid'],
				//REVIEW: Added these tables for displaying the data into relatedlist (based on configurable fields)
				'vtiger_contactaddress' => ['contactaddressid', 'vtiger_contactdetails', 'contactid'],
				'vtiger_contactsubdetails' => ['contactsubscriptionid', 'vtiger_contactdetails', 'contactid'],
				'vtiger_customerdetails' => ['customerid', 'vtiger_contactdetails', 'contactid'],
				'vtiger_contactscf' => ['contactid', 'vtiger_contactdetails', 'contactid']
			],
			'popup_fields' => ['lastname'],
		],
		'Leads' =>
		[
			'IsCustomModule' => false,
			'table_name' => 'vtiger_leaddetails',
			'table_index' => 'leadid',
			'related_tables' => [
				'vtiger_leadsubdetails' => ['leadsubscriptionid', 'vtiger_leaddetails', 'leadid'],
				'vtiger_leadaddress' => ['leadaddressid', 'vtiger_leaddetails', 'leadid'],
				'vtiger_leadscf' => ['leadid', 'vtiger_leaddetails', 'leadid'],
			],
			'popup_fields' => ['company'],
		],
		'Campaigns' =>
		[
			'IsCustomModule' => false,
			'table_name' => 'vtiger_campaign',
			'table_index' => 'campaignid',
			'popup_fields' => ['campaignname'],
		],
		'HelpDesk' =>
		[
			'IsCustomModule' => false,
			'table_name' => 'vtiger_troubletickets',
			'table_index' => 'ticketid',
			'related_tables' => ['vtiger_ticketcf' => ['ticketid']],
			'popup_fields' => ['ticket_title']
		],
		'Faq' =>
		[
			'IsCustomModule' => false,
			'table_name' => 'vtiger_faq',
			'table_index' => 'id',
		],
		'Documents' =>
		[
			'IsCustomModule' => false,
			'table_name' => 'vtiger_notes',
			'table_index' => 'notesid',
		],
		'Products' =>
		[
			'IsCustomModule' => false,
			'table_name' => 'vtiger_products',
			'table_index' => 'productid',
			'popup_fields' => ['productname'],
		],
		'PriceBooks' =>
		[
			'IsCustomModule' => false,
			'table_name' => 'vtiger_pricebook',
			'table_index' => 'pricebookid',
		],
		'Vendors' =>
		[
			'IsCustomModule' => false,
			'table_name' => 'vtiger_vendor',
			'table_index' => 'vendorid',
			'popup_fields' => ['vendorname'],
		],
		'Project' =>
		[
			'IsCustomModule' => false,
			'table_name' => 'vtiger_project',
			'table_index' => 'projectid',
			'related_tables' => [
				'vtiger_projectcf' => ['projectid', 'vtiger_project', 'projectid']
			],
		],
		'ProjectMilestone' =>
		[
			'IsCustomModule' => false,
			'table_name' => 'vtiger_projectmilestone',
			'table_index' => 'projectmilestoneid',
			'related_tables' => [
				'vtiger_projectmilestonecf' => ['projectmilestoneid', 'vtiger_projectmilestone', 'projectmilestoneid']
			],
		],
		'ProjectTask' =>
		[
			'IsCustomModule' => false,
			'table_name' => 'vtiger_projecttask',
			'table_index' => 'projecttaskid',
			'related_tables' => [
				'vtiger_projecttaskcf' => ['projecttaskid', 'vtiger_projecttask', 'projecttaskid']
			],
		],
		'Services' =>
		[
			'IsCustomModule' => false,
			'table_name' => 'vtiger_service',
			'table_index' => 'serviceid',
			'related_tables' => [
				'vtiger_servicecf' => ['serviceid']
			],
		],
		'ServiceContracts' =>
		[
			'IsCustomModule' => false,
			'table_name' => 'vtiger_servicecontracts',
			'table_index' => 'servicecontractsid',
			'related_tables' => [
				'vtiger_servicecontractscf' => ['servicecontractsid']
			],
		],
		'Assets' =>
		[
			'IsCustomModule' => false,
			'table_name' => 'vtiger_assets',
			'table_index' => 'assetsid',
			'related_tables' => [
				'vtiger_assetscf' => ['assetsid']
			],
		]
	];
	return $mod_var_mapping[$module][$varname];
}

/**
 * Check if give path is writeable.
 */
function vtlib_isWriteable($path)
{
	$path = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . $path;
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
