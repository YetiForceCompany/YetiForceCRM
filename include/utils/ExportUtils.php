<?php
/* * *******************************************************************************
 * * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 * ****************************************************************************** */

/** 	function used to get the permitted blocks
 * 	@param string $module - module name
 * 	@param string $disp_view - view name, this may be create_view, edit_view or detail_view
 * 	@return string $blockid_list - list of block ids within the paranthesis with comma seperated
 */
function getPermittedBlocks($module, $disp_view)
{
	$adb = PearDatabase::getInstance();

	\App\Log::trace("Entering into the function getPermittedBlocks($module, $disp_view)");

	$tabid = \App\Module::getModuleId($module);
	$block_detail = [];
	$query = "select blockid,blocklabel,show_title from vtiger_blocks where tabid=? and $disp_view=0 and visible = 0 order by sequence";
	$result = $adb->pquery($query, array($tabid));
	$noofrows = $adb->num_rows($result);
	$blockid_list = '(';
	for ($i = 0; $i < $noofrows; $i++) {
		$blockid = $adb->query_result($result, $i, "blockid");
		if ($i != 0)
			$blockid_list .= ', ';
		$blockid_list .= $blockid;
		$block_label[$blockid] = $adb->query_result($result, $i, "blocklabel");
	}
	$blockid_list .= ')';

	\App\Log::trace("Exit from the function getPermittedBlocks($module, $disp_view). Return value = $blockid_list");
	return $blockid_list;
}

/** 	function used to get the query which will list the permitted fields
 * 	@param string $module - module name
 * 	@param string $disp_view - view name, this may be create_view, edit_view or detail_view
 * 	@return string $sql - query to get the list of fields which are permitted to the current user
 */
function getPermittedFieldsQuery($module, $disp_view)
{
	\App\Log::trace("Entering into the function getPermittedFieldsQuery($module, $disp_view)");

	//To get the permitted blocks
	$blockid_list = getPermittedBlocks($module, $disp_view);
	$tabid = \App\Module::getModuleId($module);

	$currentUser = Users_Privileges_Model::getCurrentUserPrivilegesModel();
	if ($module === 'Users') {
		$sql = sprintf("SELECT vtiger_field.columnname, vtiger_field.fieldlabel, vtiger_field.tablename FROM vtiger_field WHERE vtiger_field.tabid=%d && vtiger_field.block IN %s && vtiger_field.displaytype IN (1,2,4,5) and vtiger_field.presence in (0,2) ORDER BY block,sequence", $tabid, $blockid_list);
	} else {
		$profileList = $currentUser->getProfiles();
		$sql = sprintf("SELECT vtiger_field.columnname, vtiger_field.fieldlabel, vtiger_field.tablename FROM vtiger_field INNER JOIN vtiger_profile2field ON vtiger_profile2field.fieldid=vtiger_field.fieldid INNER JOIN vtiger_def_org_field ON vtiger_def_org_field.fieldid=vtiger_field.fieldid WHERE vtiger_field.tabid=%d && vtiger_field.block IN %s && vtiger_field.displaytype IN (1,2,4,5) && vtiger_profile2field.visible=0 && vtiger_def_org_field.visible=0 && vtiger_profile2field.profileid IN (%s) and vtiger_field.presence in (0,2) GROUP BY vtiger_field.fieldid ORDER BY block,sequence", $tabid, $blockid_list, implode(",", $profileList));
	}
	\App\Log::trace("Exit from the function getPermittedFieldsQuery($module, $disp_view). Return value = $sql");
	return $sql;
}

/** 	function used to get the list of fields from the input query as a comma seperated string
 * 	@param string $query - field table query which contains the list of fields
 * 	@return string $fields - list of fields as a comma seperated string
 */
function getFieldsListFromQuery($query)
{
	$adb = PearDatabase::getInstance();

	\App\Log::trace("Entering into the function getFieldsListFromQuery($query)");

	$result = $adb->query($query);
	$num_rows = $adb->num_rows($result);

	for ($i = 0; $i < $num_rows; $i++) {
		$columnName = $adb->query_result($result, $i, "columnname");
		$fieldlabel = $adb->query_result($result, $i, "fieldlabel");
		$tablename = $adb->query_result($result, $i, "tablename");

		//HANDLE HERE - Mismatch fieldname-tablename in field table, in future we have to avoid these if elses
		if ($columnName == 'smownerid') {//for all assigned to user name
			$fields .= "case when (vtiger_users.user_name not like '') then vtiger_users.user_name else vtiger_groups.groupname end as '" . $fieldlabel . "',";
		} elseif ($tablename == 'vtiger_account' && $columnName == 'parentid') {//Account - Member Of
			$fields .= "vtiger_account2.accountname as '" . $fieldlabel . "',";
		} elseif ($tablename == 'vtiger_contactdetails' && $columnName == 'parentid') {//Contact - Account Name
			$fields .= "vtiger_account.accountname as '" . $fieldlabel . "',";
		} elseif ($tablename == 'vtiger_contactdetails' && $columnName == 'reportsto') {//Contact - Reports To
			$fields .= " concat(vtiger_contactdetails2.lastname,' ',vtiger_contactdetails2.firstname) as 'Reports To Contact',";
		} elseif ($tablename == 'vtiger_seproductsrel' && $columnName == 'crmid') {//Product - Related To
			$fields .= "case vtiger_crmentityRelatedTo.setype
					when 'Leads' then concat('Leads :::: ',vtiger_ProductRelatedToLead.lastname,' ',vtiger_ProductRelatedToLead.firstname)
					when 'Accounts' then concat('Accounts :::: ',vtiger_ProductRelatedToAccount.accountname)
				    End as 'Related To',";
		} elseif ($tablename == 'vtiger_products' && $columnName == 'contactid') {//Product - Contact
			$fields .= " concat(vtiger_contactdetails.lastname,' ',vtiger_contactdetails.firstname) as 'Contact Name',";
		} elseif ($tablename == 'vtiger_products' && $columnName == 'vendor_id') {//Product - Vendor Name
			$fields .= "vtiger_vendor.vendorname as '" . $fieldlabel . "',";
		} elseif ($tablename == 'vtiger_attachments' && $columnName == 'name') {//Emails filename
			$fields .= $tablename . ".name as '" . $fieldlabel . "',";
		}
		//By Pavani...Handling mismatch field and table name for trouble tickets
		elseif ($tablename == 'vtiger_troubletickets' && $columnName == 'product_id') {//Ticket - Product
			$fields .= "vtiger_products.productname as '" . $fieldlabel . "',";
		} elseif ($tablename == 'vtiger_notes' && ($columnName == 'filename' || $columnName == 'filetype' || $columnName == 'filesize' || $columnName == 'filelocationtype' || $columnName == 'filestatus' || $columnName == 'filedownloadcount' || $columnName == 'folderid')) {
			continue;
		} else {
			$fields .= $tablename . "." . $columnName . " as '" . $fieldlabel . "',";
		}
	}
	$fields = trim($fields, ",");

	\App\Log::trace("Exit from the function getFieldsListFromQuery($query). Return value = $fields");
	return $fields;
}
