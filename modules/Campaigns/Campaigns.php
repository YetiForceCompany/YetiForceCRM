<?php
/* * *******************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of txhe License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 * ****************************************************************************** */

class Campaigns extends CRMEntity
{

	var $log;
	var $db;
	var $table_name = "vtiger_campaign";
	var $table_index = 'campaignid';
	var $tab_name = Array('vtiger_crmentity', 'vtiger_campaign', 'vtiger_campaignscf', 'vtiger_entity_stats');
	var $tab_name_index = Array('vtiger_crmentity' => 'crmid', 'vtiger_campaign' => 'campaignid', 'vtiger_campaignscf' => 'campaignid', 'vtiger_entity_stats' => 'crmid');

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_campaignscf', 'campaignid');
	var $column_fields = Array();
	var $sortby_fields = Array('campaignname', 'smownerid', 'campaigntype', 'productname', 'expectedrevenue', 'closingdate', 'campaignstatus', 'expectedresponse', 'targetaudience', 'expectedcost');
	var $list_fields = Array(
		'Campaign Name' => Array('campaign' => 'campaignname'),
		'Campaign Type' => Array('campaign' => 'campaigntype'),
		'Campaign Status' => Array('campaign' => 'campaignstatus'),
		'Expected Revenue' => Array('campaign' => 'expectedrevenue'),
		'Expected Close Date' => Array('campaign' => 'closingdate'),
		'Assigned To' => Array('crmentity' => 'smownerid')
	);
	var $list_fields_name = Array(
		'Campaign Name' => 'campaignname',
		'Campaign Type' => 'campaigntype',
		'Campaign Status' => 'campaignstatus',
		'Expected Revenue' => 'expectedrevenue',
		'Expected Close Date' => 'closingdate',
		'Assigned To' => 'assigned_user_id'
	);
	var $list_link_field = 'campaignname';
	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = '';
	var $default_sort_order = 'DESC';
	var $search_fields = Array(
		'Campaign Name' => Array('vtiger_campaign' => 'campaignname'),
		'Campaign Type' => Array('vtiger_campaign' => 'campaigntype'),
	);
	var $search_fields_name = Array(
		'Campaign Name' => 'campaignname',
		'Campaign Type' => 'campaigntype',
	);
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('campaignname', 'createdtime', 'modifiedtime', 'assigned_user_id');
	// For Alphabetical search
	var $def_basicsearch_col = 'campaignname';

	/** Function to handle module specific operations when saving a entity
	 */
	public function save_module($module)
	{
		
	}

	/**
	 * Function to get Campaign related Contacts
	 * @param  integer   $id      - campaignid
	 * returns related Contacts record in array format
	 */
	public function get_campaigns_records($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		$log = LoggerManager::getInstance();
		$singlepane_view = vglobal('singlepane_view');
		$currentModule = vglobal('currentModule');
		$log->debug("Entering get_campaigns_records(" . $id . ") method ...");
		$this_module = $currentModule;

		$related_module = vtlib\Functions::getModuleName($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();

		$is_CampaignStatusAllowed = false;
		$current_user = vglobal('current_user');
		if (getFieldVisibilityPermission($related_module, $current_user->id, 'campaignrelstatus') == '0') {
			$other->list_fields['Status'] = array('vtiger_campaignrelstatus' => 'campaignrelstatus');
			$other->list_fields_name['Status'] = 'campaignrelstatus';
			$other->sortby_fields[] = 'campaignrelstatus';
			$is_CampaignStatusAllowed = (getFieldVisibilityPermission($related_module, $current_user->id, 'campaignrelstatus', 'readwrite') == '0') ? true : false;
		}

		vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		if ($singlepane_view == 'true')
			$returnset = '&return_module=' . $this_module . '&return_action=DetailView&return_id=' . $id;
		else
			$returnset = '&return_module=' . $this_module . '&return_action=CallRelatedList&return_id=' . $id;

		$button = '';

		// Send mail button for selected Leads
		$button .= "<input title='" . \includes\Language::translate('LBL_SEND_MAIL_BUTTON') . "' class='crmbutton small edit' value='" . \includes\Language::translate('LBL_SEND_MAIL_BUTTON') . "' type='button' name='button' onclick='rel_eMail(\"$this_module\",this,\"$related_module\")'>";
		$button .= '&nbsp;&nbsp;&nbsp;&nbsp';

		/* To get Leads CustomView -START */
		require_once('modules/CustomView/CustomView.php');
		$lhtml = "<select id='" . $related_module . "_cv_list' class='small'><option value='None'>-- " . \includes\Language::translate('Select One') . " --</option>";
		$oCustomView = new CustomView($related_module);
		$viewid = $oCustomView->getViewId($related_module);
		$customviewcombo_html = $oCustomView->getCustomViewCombo($viewid, false);
		$lhtml .= $customviewcombo_html;
		$lhtml .= "</select>";
		/* To get Leads CustomView -END */

		$button .= $lhtml . "<input title='" . \includes\Language::translate('LBL_LOAD_LIST', $this_module) . "' class='crmbutton small edit' value='" . \includes\Language::translate('LBL_LOAD_LIST', $this_module) . "' type='button' name='button' onclick='loadCvList(\"$related_module\",\"$id\")'>";
		$button .= '&nbsp;&nbsp;&nbsp;&nbsp';

		if ($actions) {
			if (is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
				$button .= "<input title='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . \includes\Language::translate('LBL_SELECT') . " " . \includes\Language::translate($related_module) . "'>&nbsp;";
			}
			if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
				$button .= "<input type='hidden' name='createmode' id='createmode' value='link' />" .
					"<input title='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname) . "' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='" . \includes\Language::translate('LBL_ADD_NEW') . " " . \includes\Language::translate($singular_modname) . "'>&nbsp;";
			}
		}

		$query = "SELECT vtiger_crmentity.*, $other->table_name.*";

		$userNameSql = \vtlib\Deprecated::getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name',
				'last_name' => 'vtiger_users.last_name'), 'Users');
		$query .= ", CASE WHEN (vtiger_users.user_name NOT LIKE '') THEN $userNameSql ELSE vtiger_groups.groupname END AS user_name";

		$moreRelation = '';
		if (!empty($other->related_tables)) {
			foreach ($other->related_tables as $tname => $relmap) {
				$query .= ", $tname.*";
				// Setup the default JOIN conditions if not specified
				if (empty($relmap[1]))
					$relmap[1] = $other->table_name;
				if (empty($relmap[2]))
					$relmap[2] = $relmap[0];
				$moreRelation .= " LEFT JOIN $tname ON $tname.$relmap[0] = $relmap[1].$relmap[2]";
			}
		}
		$query .= ',vtiger_campaignrelstatus.*';
		$query .= " FROM $other->table_name";
		$query .= " INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $other->table_name.$other->table_index";
		$query .= ' INNER JOIN vtiger_campaign_records ON vtiger_campaign_records.crmid = vtiger_crmentity.crmid';
		$query .= $moreRelation;
		$query .= ' LEFT JOIN vtiger_users  ON vtiger_users.id = vtiger_crmentity.smownerid';
		$query .= ' LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid';
		$query .= ' LEFT JOIN vtiger_campaignrelstatus ON vtiger_campaignrelstatus.campaignrelstatusid = vtiger_campaign_records.campaignrelstatusid';
		$query .= ' WHERE vtiger_crmentity.deleted = 0 && vtiger_campaign_records.campaignid = %d';

		$query = sprintf($query, $id);
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = Array();
		else if ($is_CampaignStatusAllowed) {
			$statusPos = count($return_value['header']) - 2; // Last column is for Actions, exclude that. Also the index starts from 0, so reduce one more count.
			$return_value = $this->add_status_popup($return_value, $statusPos, $related_module);
		}

		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_campaigns_records method ...");
		return $return_value;
	}
	/*
	 * Function populate the status columns' HTML
	 * @param - $related_list return value from GetRelatedList
	 * @param - $status_column index of the status column in the list.
	 * returns true on success
	 */

	public function add_status_popup($related_list, $status_column = 7, $related_module)
	{
		$adb = PearDatabase::getInstance();

		if (!$this->campaignrelstatus) {
			$result = $adb->query('SELECT * FROM vtiger_campaignrelstatus;');
			while ($row = $adb->fetchByAssoc($result)) {
				$this->campaignrelstatus[$row['campaignrelstatus']] = $row;
			}
		}
		foreach ($related_list['entries'] as $key => &$entry) {
			$popupitemshtml = '';
			foreach ($this->campaignrelstatus as $campaingrelstatus) {
				$camprelstatus = \includes\Language::translate($campaingrelstatus[campaignrelstatus], 'Campaigns');
				$popupitemshtml .= "<a onmouseover=\"javascript: showBlock('campaignstatus_popup_$key')\" href=\"javascript:updateCampaignRelationStatus('$related_module', '" . $this->id . "', '$key', '$campaingrelstatus[campaignrelstatusid]', '" . addslashes($camprelstatus) . "');\">$camprelstatus</a><br />";
			}
			$popuphtml = '<div onmouseover="javascript:clearTimeout(statusPopupTimer);" onmouseout="javascript:closeStatusPopup(\'campaignstatus_popup_' . $key . '\');" style="margin-top: -14px; width: 200px;" id="campaignstatus_popup_' . $key . '" class="calAction"><div style="background-color: #FFFFFF; padding: 8px;">' . $popupitemshtml . '</div></div>';

			$entry[$status_column] = "<a href=\"javascript: showBlock('campaignstatus_popup_$key');\">[+]</a> <span id='campaignstatus_$key'>" . $entry[$status_column] . "</span>" . $popuphtml;
		}

		return $related_list;
	}
	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */

	public function generateReportsSecQuery($module, $secmodule, $queryplanner)
	{
		$matrix = $queryplanner->newDependencyMatrix();
		$matrix->setDependency('vtiger_crmentityCampaigns', array('vtiger_groupsCampaigns', 'vtiger_usersCampaignss', 'vtiger_lastModifiedByCampaigns', 'vtiger_campaignscf'));
		$matrix->setDependency('vtiger_campaign', array('vtiger_crmentityCampaigns', 'vtiger_productsCampaigns'));

		if (!$queryplanner->requireTable("vtiger_campaign", $matrix)) {
			return '';
		}

		$query = $this->getRelationQuery($module, $secmodule, "vtiger_campaign", "campaignid", $queryplanner);

		if ($queryplanner->requireTable("vtiger_crmentityCampaigns", $matrix)) {
			$query .=" left join vtiger_crmentity as vtiger_crmentityCampaigns on vtiger_crmentityCampaigns.crmid=vtiger_campaign.campaignid and vtiger_crmentityCampaigns.deleted=0";
		}
		if ($queryplanner->requireTable("vtiger_productsCampaigns")) {
			$query .=" 	left join vtiger_products as vtiger_productsCampaigns on vtiger_campaign.product_id = vtiger_productsCampaigns.productid";
		}
		if ($queryplanner->requireTable("vtiger_campaignscf")) {
			$query .=" 	left join vtiger_campaignscf on vtiger_campaignscf.campaignid = vtiger_crmentityCampaigns.crmid";
		}
		if ($queryplanner->requireTable("vtiger_groupsCampaigns")) {
			$query .=" left join vtiger_groups as vtiger_groupsCampaigns on vtiger_groupsCampaigns.groupid = vtiger_crmentityCampaigns.smownerid";
		}
		if ($queryplanner->requireTable("vtiger_usersCampaigns")) {
			$query .=" left join vtiger_users as vtiger_usersCampaigns on vtiger_usersCampaigns.id = vtiger_crmentityCampaigns.smownerid";
		}
		if ($queryplanner->requireTable("vtiger_lastModifiedByCampaigns")) {
			$query .=" left join vtiger_users as vtiger_lastModifiedByCampaigns on vtiger_lastModifiedByCampaigns.id = vtiger_crmentityCampaigns.modifiedby ";
		}
		if ($queryplanner->requireTable("vtiger_createdbyCampaigns")) {
			$query .= " left join vtiger_users as vtiger_createdbyCampaigns on vtiger_createdbyCampaigns.id = vtiger_crmentityCampaigns.smcreatorid ";
		}
		return $query;
	}
	/*
	 * Function to get the relation tables for related modules
	 * @param - $secmodule secondary module name
	 * returns the array with table names and fieldnames storing relations between module and this module
	 */

	public function setRelationTables($secmodule = false)
	{
		$relTables = array(
			'Contacts' => array('vtiger_campaign_records' => array('campaignid', 'crmid'), 'vtiger_campaign' => 'campaignid'),
			'Leads' => array('vtiger_campaign_records' => array('campaignid', 'crmid'), 'vtiger_campaign' => 'campaignid'),
			'Accounts' => array('vtiger_campaign_records' => array('campaignid', 'crmid'), 'vtiger_campaign' => 'campaignid'),
			'Vendors' => array('vtiger_campaign_records' => array('campaignid', 'crmid'), 'vtiger_campaign' => 'campaignid'),
			'Partners' => array('vtiger_campaign_records' => array('campaignid', 'crmid'), 'vtiger_campaign' => 'campaignid'),
			'Competition' => array('vtiger_campaign_records' => array('campaignid', 'crmid'), 'vtiger_campaign' => 'campaignid'),
			'Products' => array('vtiger_campaign' => array('campaignid', 'product_id')),
		);
		if ($secmodule === false) {
			return $relTables;
		}
		return $relTables[$secmodule];
	}

	// Function to unlink an entity with given Id from another entity
	public function unlinkRelationship($id, $returnModule, $returnId, $relatedName = false)
	{
		$log = vglobal('log');
		if (empty($returnModule) || empty($returnId))
			return;

		if (in_array($returnModule, ['Leads', 'Vendors', 'Contacts', 'Partners', 'Competition'])) {
			$this->db->delete('vtiger_campaign_records', 'campaignid=? && crmid=?', [$id, $returnId]);
		} elseif ($returnModule == 'Accounts') {
			$this->db->delete('vtiger_campaign_records', 'campaignid=? && crmid=?', [$id, $returnId]);
			$sql = 'DELETE FROM vtiger_campaign_records WHERE campaignid=? && crmid IN (SELECT contactid FROM vtiger_contactdetails WHERE accountid=?)';
			$this->db->pquery($sql, array($id, $returnId));
		} else {
			parent::unlinkRelationship($id, $returnModule, $returnId, $relatedName);
		}
	}

	public function save_related_module($module, $crmid, $withModule, $withCrmids, $relatedName = false)
	{
		$adb = PearDatabase::getInstance();

		if (!is_array($withCrmids))
			$withCrmids = [$withCrmids];
		if (!in_array($withModule, ['Accounts', 'Leads', 'Vendors', 'Contacts', 'Partners', 'Competition'])) {
			parent::save_related_module($module, $crmid, $withModule, $withCrmids, $relatedName);
		} else {
			foreach ($withCrmids as $withCrmid) {
				$checkResult = $adb->pquery('SELECT 1 FROM vtiger_campaign_records WHERE campaignid = ? && crmid = ?', array($crmid, $withCrmid));
				if ($checkResult && $adb->num_rows($checkResult) > 0) {
					continue;
				}
				$adb->insert('vtiger_campaign_records', [
					'campaignid' => $crmid,
					'crmid' => $withCrmid,
					'campaignrelstatusid' => 0
				]);
			}
		}
	}
}
