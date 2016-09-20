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
include_once('config/config.php');
require_once('include/logging.php');
require_once('include/database/PearDatabase.php');

/** This class is used to track the recently viewed items on a per user basis.
 * It is intended to be called by each module when rendering the detail form.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 */
class Tracker
{

	var $log;
	var $db;
	var $table_name = "vtiger_tracker";
	// Tracker vtiger_table
	var $column_fields = Array(
		"id",
		"user_id",
		"module_name",
		"item_id",
		"item_summary"
	);

	public function __construct()
	{
		$this->log = LoggerManager::getLogger('Tracker');
		$adb = PearDatabase::getInstance();
		$this->db = $adb;
	}

	/**
	 * Add this new item to the vtiger_tracker vtiger_table.  If there are too many items (global config for now)
	 * then remove the oldest item.  If there is more than one extra item, log an error.
	 * If the new item is the same as the most recent item then do not change the list
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
	 * All Rights Reserved.
	 * Contributor(s): ______________________________________..
	 */
	public function track_view($user_id, $current_module, $item_id, $item_summary)
	{
		$adb = PearDatabase::getInstance();
		$this->delete_history($user_id, $item_id);
		$log = vglobal('log');
		$log->info("in  track view method " . $current_module);

//No genius required. Just add an if case and change the query so that it puts the tracker entry whenever you touch on the DetailView of the required entity
		//get the first name and last name from the respective modules
		if ($current_module != '') {
			$query = "select fieldname,tablename,entityidfield from vtiger_entityname where modulename = ?";
			$result = $adb->pquery($query, array($current_module));
			$fieldsname = $adb->query_result($result, 0, 'fieldname');
			$tablename = $adb->query_result($result, 0, 'tablename');
			$entityidfield = $adb->query_result($result, 0, 'entityidfield');
			if (!(strpos($fieldsname, ',') === false)) {
				// concatenate multiple fields with an whitespace between them
				$fieldlists = explode(',', $fieldsname);
				$fl = [];
				foreach ($fieldlists as $w => $c) {
					if (count($fl))
						$fl[] = "' '";
					$fl[] = $c;
				}
				$fieldsname = $adb->concat($fl);
			}
			$query1 = "select $fieldsname as entityname from $tablename where $entityidfield = ?";
			$result = $adb->pquery($query1, array($item_id));
			$item_summary = $adb->query_result($result, 0, 'entityname');
			if (strlen($item_summary) > 30) {
				$item_summary = substr($item_summary, 0, 30) . '...';
			}
		}

		#if condition added to skip vtiger_faq in last viewed history
		$query = "INSERT into $this->table_name (user_id, module_name, item_id, item_summary) values (?,?,?,?)";
		$qparams = array($user_id, $current_module, $item_id, $item_summary);

		$this->log->info("Track Item View: " . $query);

		$this->db->pquery($query, $qparams, true);


		$this->prune_history($user_id);
	}

	/**
	 * param $user_id - The id of the user to retrive the history for
	 * param $module_name - Filter the history to only return records from the specified module.  If not specified all records are returned
	 * return - return the array of result set rows from the query.  All of the vtiger_table vtiger_fields are included
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
	 * All Rights Reserved.
	 * Contributor(s): ______________________________________..
	 */
	public function get_recently_viewed($userId, $moduleName = "")
	{
		if (empty($userId)) {
			return;
		}
		$query = "SELECT * from $this->table_name inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_tracker.item_id WHERE user_id=? and vtiger_crmentity.deleted=0 ORDER BY id DESC";
		$this->log->debug("About to retrieve list: $query");
		$result = $this->db->pquery($query, array($userId), true);
		$list = [];
		while ($row = $this->db->fetchByAssoc($result, -1, false)) {
			// If the module was not specified or the module matches the module of the row, add the row to the list
			if ($moduleName == "" || $row['module_name'] == $moduleName) {
				//Adding Security check
				require_once('include/utils/utils.php');
				require_once('include/utils/UserInfoUtil.php');
				$entityId = $row['item_id'];
				$module = $row['module_name'];
				if ($module == 'Users') {
					$currentUser = Users_Privileges_Model::getCurrentUserModel();
					if ($currentUser->isAdminUser()) {
						$per = true;
					}
				} else {
					$per = \includes\Privileges::isPermitted($module, 'DetailView', $entityId);
				}
				if ($per) {
					$list[] = $row;
				}
			}
		}
		return $list;
	}

	/**
	 * INTERNAL -- This method cleans out any entry for a record for a user.
	 * It is used to remove old occurances of previously viewed items.
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
	 * All Rights Reserved.
	 * Contributor(s): ______________________________________..
	 */
	public function delete_history($user_id, $item_id)
	{
		$query = "DELETE from $this->table_name WHERE user_id=? and item_id=?";
		$this->db->pquery($query, array($user_id, $item_id), true);
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

	/**
	 * INTERNAL -- This function will clean out old history records for this user if necessary.
	 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
	 * All Rights Reserved.
	 * Contributor(s): ______________________________________..
	 */
	public function prune_history($user_id)
	{
		global $history_max_viewed;

		// Check to see if the number of items in the list is now greater than the config max.
		$query = "SELECT count(*) from $this->table_name WHERE user_id='$user_id'";

		$this->log->debug("About to verify history size: $query");
		$count = $this->db->getOne($query);

		$this->log->debug("history size: (current, max)($count, $history_max_viewed)");
		while ($count > $history_max_viewed) {
			// delete the last one.  This assumes that entries are added one at a time.
			// we should never add a bunch of entries
			$query = "SELECT * from $this->table_name WHERE user_id='$user_id' ORDER BY id ASC LIMIT 0,1";
			$this->log->debug("About to try and find oldest item: $query");
			$result = $this->db->query($query);

			$oldest_item = $this->db->fetchByAssoc($result, -1, false);
			$query = "DELETE from $this->table_name WHERE id=?";
			$this->log->debug("About to delete oldest item: ");

			$result = $this->db->pquery($query, array($oldest_item['id']), true);
			$count--;
		}
	}
}
