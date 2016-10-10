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

//Fix postgres queries
function fixPostgresQuery($query, $log, $debug)
{
	// First select the query fields from the remaining query
	$queryFields = substr($query, strlen('SELECT'), stripos($query, 'FROM') - strlen('SELECT'));
	$queryRecord = substr($query, stripos($query, 'FROM'), strlen($query));
	$groupClause = "";
	$orderClause = "";

	if ($debug)
		$log->info("fixPostgresQuery: " . $query);

	// If we already have an order or group cluase separate ist for later use
	if (stripos($queryRecord, 'GROUP BY') > 0) {
		$groupClause = substr($queryRecord, stripos($queryRecord, 'GROUP BY') + strlen('GROUP BY'), strlen($queryRecord));
		if (stripos($groupClause, 'ORDER BY') > 0) {
			$orderClause = substr($groupClause, stripos($groupClause, 'ORDER BY'), strlen($groupClause));
			$groupClause = substr($groupClause, 0, stripos($groupClause, 'ORDER BY'));
		}
		$queryRecord = substr($queryRecord, 0, stripos($queryRecord, 'GROUP BY'));
	}

	if (stripos($queryRecord, 'ORDER BY') > 0) {
		$orderClause = substr($queryRecord, stripos($queryRecord, 'ORDER BY'), strlen($queryRecord));
		$queryRecord = substr($queryRecord, 0, stripos($queryRecord, 'ORDER BY'));
	}

	// Construkt the privateGroupList from the filed list by separating combined
	// record.field entries
	$privateGroupList = [];
	$token = strtok($queryFields, ", ()	");
	while ($token !== false) {
		if (strpos($token, ".") !== false)
			array_push($privateGroupList, $token);
		$token = strtok(", ()	");
	}
	sort($privateGroupList);
	$groupFields = "";
	$last = "";
	for ($i = 0; $i < count($privateGroupList); $i++) {
		if ($last != $privateGroupList[$i])
			if ($groupFields == "")
				$groupFields = $privateGroupList[$i];
			else
				$groupFields .= "," . $privateGroupList[$i];
		$last = $privateGroupList[$i];
	}

	// Rebuild the query
	$query = sprintf("SELECT %s%s", $queryFields, $queryRecord);
	if ($groupClause != "")
		$groupClause = $groupClause . "," . $groupFields;
	else
		$groupClause = $groupFields;
	$query .= expandStar($groupClause, $log) . " " . $orderClause;

	if ($debug)
		$log->info("fixPostgresQuery result: " . $query);

	return( $query);
}

// Postgres8 will not accept a "tablename.*" entry in the GROUP BY clause
function expandStar($fieldlist, $log)
{
	$expanded = "";
	$field = strtok($fieldlist, ",");
	while ($field != "") {
		//remove leading and trailing spaces
		$field = trim($field);

		//still spaces in the field indicate a complex structure
		if (strpos($field, " ") == 0) {

			//locate table- and fieldname
			$pos = strpos($field, ".");
			if ($pos > 0) {
				$table = substr($field, 0, $pos);
				$subfield = substr($field, $pos + 1, strlen($field) - $pos);

				//do we need to expand?
				if ($subfield == "*")
					$field = expandRecord($table, $log);
			}

			//add the propably expanded field to the querylist
			if ($expanded == "")
				$expanded = $field;
			else
				$expanded .= "," . $field;
		}

		//next field
		$field = strtok(",");
	}
	if ($expanded != '')
		$expanded = " GROUP BY " . trim($expanded, ",");
	//return the expanded fieldlist
	return( $expanded);
}

//return an expanded table field list
function expandRecord($table, $log)
{
	$result = "";
	$log->info("Debug: expandRecord");
	$subfields = [];

	//vtiger_products table
	if ($table == "vtiger_products")
		$subfields = array("productid", "productname", "productcode", "productcategory", "manufacturer", "qty_per_unit", "unit_price", "weight", "pack_size", "sales_start_date", "sales_end_date", "start_date", "expiry_date", "cost_factor", "commissionrate", "commissionmethod", "discontinued", "usageunit", "currency", "reorderlevel", "website", "taxclass", "mfr_part_no", "vendor_part_no", "serialno", "qtyinstock", "productsheet", "qtyindemand", "glacct", "vendor_id", "imagename");
	
	//vtiger_activity table
	elseif ($table == "vtiger_activity")
		$subfields = array("activityid", "subject", "semodule", "activitytype", "date_start", "due_date", "time_start", "time_end", "sendnotification", "duration_hours", "duration_minutes", "status", "eventstatus", "priority", "location", "notime", "visibility", "recurringtype");

	//vtiger_notes table
	elseif ($table == "vtiger_notes")
		$subfields = array("notesid", "contact_id", "title", "filename", "notecontent");

	//vtiger_faq table
	elseif ($table == "vtiger_faq")
		$subfields = array("id", "product_id", "question", "answer", "category", "status");

	//vtiger_profile2field
	elseif ($table == "vtiger_profile2field")
		$subfields = array("profileid", "tabid", "fieldid", "visible", "readonly");

	//vtiger_field
	elseif ($table == "vtiger_field")
		$subfields = array("tabid", "fieldid", "columnname", "tablename", "generatedtype", "uitype", "fieldname", "fieldlabel", "readonly", "presence", "selected", "maximumlength", "sequence", "block", "displaytype", "typeofdata", "quickcreate", "quickcreatesequence", "info_type");

	//vtiger_producttaxrel
	elseif ($table == "vtiger_producttaxrel")
		$subfields = array("productid", "taxid", "taxpercentage");

	//vtiger_inventorytaxinfo
	elseif ($table == "vtiger_inventorytaxinfo")
		$subfields = array("taxid", "taxname", "taxlabel", "percentage", "deleted");

	//vtiger_role2picklist
	elseif ($table == "vtiger_role2picklist")
		$subfields = array("roleid", "picklistid", "sortid");

	//vtiger_contactdetails
	elseif ($table == "vtiger_contactdetails")
		$subfields = array("lastname", "contactid", "accountid", "salutation", "firstname", "email", "phone", "mobile", "title", "department", "fax", "reportsto", "training", "usertype", "contacttype", "otheremail", "yahooid", "donotcall", "emailoptout", "imagename", "reference", "notify_owner");

	//vtiger_crmentity
	elseif ($table == "vtiger_crmentity")
		$subfields = array("crmid", "smcreatorid", "smownerid", "modifiedby", "setype", "description", "createdtime", "modifiedtime", "viewedtime", "status", "version", "presence", "deleted");

	//vtiger_seactivityrel
	elseif ($table == "vtiger_seactivityrel")
		$subfields = array("crmid", "activityid");

	//vtiger_cntactivityrel
	elseif ($table == "vtiger_cntactivityrel")
		$subfields = array("contactid", "activityid");

	//vtiger_leaddetails
	elseif ($table == "vtiger_leaddetails")
		$subfields = array("leadid", "email", "interest", "firstname", "salutation", "lastname", "company", "annualrevenue", "industry", "campaign", "rating", "leadstatus", "leadsource", "converted", "designation", "space", "comments", "priority", "demorequest", "partnercontact", "productversion", "product", "maildate", "nextstepdate", "fundingsituation", "purpose", "evaluationstatus", "transferdate", "revenuetype", "noofemployees", "yahooid", "assignleadchk");

	//vtiger_pricebook
	elseif ($table == "vtiger_pricebook")
		$subfields = array("pricebookid", "bookname", "active", "description");

	//fields of the requested array still undefined
	else
		$log->info("function expandRecord: please add structural information for table '" . $table . "'");

	//construct an entity string
	for ($i = 0; $i < count($subfields); $i++) {
		$result .= $table . "." . $subfields[$i] . ",";
	}

	//remove the trailiung ,
	if (strlen($result) > 0)
		$result = substr($result, 0, strlen($result) - 1);

	//return out new string
	return( $result);
}
