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

		\App\Log::trace("in  track view method " . $current_module);

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

		\App\Log::trace("Track Item View: " . $query);

		$this->db->pquery($query, $qparams, true);


		$this->prune_history($user_id);
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

		\App\Log::trace("About to verify history size: $query");
		$count = $this->db->getOne($query);

		\App\Log::trace("history size: (current, max)($count, $history_max_viewed)");
		while ($count > $history_max_viewed) {
			// delete the last one.  This assumes that entries are added one at a time.
			// we should never add a bunch of entries
			$query = "SELECT * from $this->table_name WHERE user_id='$user_id' ORDER BY id ASC LIMIT 0,1";
			\App\Log::trace("About to try and find oldest item: $query");
			$result = $this->db->query($query);

			$oldest_item = $this->db->fetchByAssoc($result, -1, false);
			$query = "DELETE from $this->table_name WHERE id=?";
			\App\Log::trace("About to delete oldest item: ");

			$result = $this->db->pquery($query, array($oldest_item['id']), true);
			$count--;
		}
	}
}
