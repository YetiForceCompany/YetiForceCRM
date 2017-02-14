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
 * $Header: /advent/projects/wesat/vtiger_crm/sugarcrm/data/Tracker.php,v 1.15 2005/04/28 05:44:22 samk Exp $
 * Description:  Updates entries for the Last Viewed functionality tracking the
 * last viewed records on a per user basis.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 * ****************************************************************************** */

/** This class is used to track the recently viewed items on a per user basis.
 * It is intended to be called by each module when rendering the detail form.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 */
class Tracker
{

	public $db;
	public $table_name = "vtiger_tracker";
	// Tracker vtiger_table
	public $column_fields = Array(
		"id",
		"user_id",
		"module_name",
		"item_id",
		"item_summary"
	);

	public function __construct()
	{
		$adb = PearDatabase::getInstance();
		$this->db = $adb;
	}

	/**
	 * INTERNAL -- This method cleans out any entry for a record.
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
	 * All Rights Reserved.
	 * Contributor(s): ______________________________________..
	 */
	public function delete_item_history($item_id)
	{
		$query = "DELETE from $this->table_name WHERE item_id=?";
		$this->db->pquery($query, array($item_id), true);
	}
}
