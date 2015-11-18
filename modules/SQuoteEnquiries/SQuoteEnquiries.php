<?php
/**
 * @package YetiForce.CRMEntity
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

class SQuoteEnquiries extends Vtiger_CRMEntity
{

	var $table_name = 'u_yf_squoteenquiries';
	var $table_index = 'squoteenquiriesid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('u_yf_squoteenquiriescf', 'squoteenquiriesid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'u_yf_squoteenquiries', 'u_yf_squoteenquiriescf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'u_yf_squoteenquiries' => 'squoteenquiriesid',
		'u_yf_squoteenquiriescf' => 'squoteenquiriesid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'LBL_SUBJECT' => Array('squoteenquiries', 'subject'),
		'Assigned To' => Array('crmentity', 'smownerid')
	);
	var $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'LBL_SUBJECT' => 'subject',
		'Assigned To' => 'assigned_user_id',
	);
	// Make the field link to detail view
	var $list_link_field = 'subject';
	// For Popup listview and UI type support
	var $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'LBL_SUBJECT' => Array('squoteenquiries', 'subject'),
		'Assigned To' => Array('vtiger_crmentity', 'assigned_user_id'),
	);
	var $search_fields_name = Array(
		/* Format: Field Label => fieldname */
		'LBL_SUBJECT' => 'subject',
		'Assigned To' => 'assigned_user_id',
	);
	// For Popup window record selection
	var $popup_fields = Array('subject');
	// For Alphabetical search
	var $def_basicsearch_col = 'subject';
	// Column value to use on detail view record text display
	var $def_detailview_recname = 'subject';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('subject', 'assigned_user_id');
	var $default_order_by = 'subject';
	var $default_sort_order = 'ASC';

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type
	 */
	function vtlib_handler($moduleName, $eventType)
	{
		$adb = PearDatabase::getInstance();
		if ($eventType == 'module.postinstall') {
			$moduleInstance = CRMEntity::getInstance('SQuoteEnquiries');
			$moduleInstance->setModuleSeqNumber("configure", 'SQuoteEnquiries', 'S-QE', '1'); // co w miejsce id
			$adb->pquery('UPDATE vtiger_tab SET customized=0 WHERE name=?', ['SQuoteEnquiries']);

			$modcommentsModuleInstance = Vtiger_Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments'))
					ModComments::addWidgetTo(array('SQuoteEnquiries'));
			}
			$modcommentsModuleInstance = Vtiger_Module::getInstance('ModTracker');
			if ($modcommentsModuleInstance && file_exists('modules/ModTracker/ModTracker.php')) {
				include_once('vtlib/Vtiger/Module.php');
				include_once 'modules/ModTracker/ModTracker.php';
				$tabid = Vtiger_Functions::getModuleId('SQuoteEnquiries');
				$moduleModTrackerInstance = new ModTracker();
				if (!$moduleModTrackerInstance->isModulePresent($tabid)) {
					$res = $adb->pquery("INSERT INTO vtiger_modtracker_tabs VALUES(?,?)", array($tabid, 1));
					$moduleModTrackerInstance->updateCache($tabid, 1);
				} else {
					$updatevisibility = $adb->pquery("UPDATE vtiger_modtracker_tabs SET visible = 1 WHERE tabid = ?", array($tabid));
					$moduleModTrackerInstance->updateCache($tabid, 1);
				}
				if (!$moduleModTrackerInstance->isModTrackerLinkPresent($tabid)) {
					$moduleInstance = Vtiger_Module::getInstance($tabid);
					$moduleInstance->addLink('DETAILVIEWBASIC', 'View History', "javascript:ModTrackerCommon.showhistory('\$RECORD\$')", '', '', array('path' => 'modules/ModTracker/ModTracker.php', 'class' => 'ModTracker', 'method' => 'isViewPermitted'));
				}
			}
			$this->addActions();
			// TODO Handle actions after this module is installed.
		} else if ($eventType == 'module.disabled') {
			// TODO Handle actions before this module is being uninstalled.
		} else if ($eventType == 'module.preuninstall') {
			// TODO Handle actions when this module is about to be deleted.
		} else if ($eventType == 'module.preupdate') {
			// TODO Handle actions before this module is updated.
		} else if ($eventType == 'module.postupdate') {
			// TODO Handle actions after this module is updated.
		}
	}

	function addActions()
	{
		$adb = PearDatabase::getInstance();
		$actions = ['QEOpen', 'QEClose'];
		foreach ($actions as $key => $action) {
			$result = $adb->pquery('SELECT actionid FROM vtiger_actionmapping WHERE actionname=?;', [$action]);
			if ($adb->getRowCount($result)) {
				continue;
			}
			$result = $adb->query("SELECT MAX(actionid) AS max_seq  FROM vtiger_actionmapping ;");
			$key = (int) $adb->getSingleValue($result) + 1;
			$adb->pquery("INSERT INTO `vtiger_actionmapping` (`actionid`, `actionname`, `securitycheck`) VALUES (?, ?, ?);", [$key, $action, 0]);
			$tabid = Vtiger_Functions::getModuleId('SQuoteEnquiries');
			$resultP = $adb->query("SELECT profileid FROM vtiger_profile;");
			$rowCountP = $adb->getRowCount($resultP);
			for ($i = 0; $i < $rowCountP; $i++) {
				$profileId = $adb->query_result_raw($resultP, $i, 'profileid');
				$resultC = $adb->pquery("SELECT activityid FROM vtiger_profile2utility WHERE profileid=? AND tabid=? AND activityid=? ;", [$profileId, $tabid, $key]);
				if ($adb->getRowCount($resultC) == 0) {
					$adb->pquery("INSERT INTO vtiger_profile2utility (profileid, tabid, activityid, permission) VALUES  (?, ?, ?, ?)", [$profileId, $tabid, $key, 0]);
				}
			}
		}
	}

	function get_emails($id, $cur_tab_id, $rel_tab_id, $actions = false)
	{
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		$singlepane_view = vglobal('singlepane_view');
		$currentModule = vglobal('currentModule');
		$log->debug("Entering get_emails(" . $id . ") method ...");
		$this_module = $currentModule;

		$related_module = vtlib_getModuleNameById($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();
		vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		if ($singlepane_view == 'true')
			$returnset = '&return_module=' . $this_module . '&return_action=DetailView&return_id=' . $id;
		else
			$returnset = '&return_module=' . $this_module . '&return_action=CallRelatedList&return_id=' . $id;

		$button = '';

		$button .= '<input type="hidden" name="email_directing_module"><input type="hidden" name="record">';

		if ($actions) {
			if (is_string($actions))
				$actions = explode(',', strtoupper($actions));
			if (in_array('SELECT', $actions) && isPermitted($related_module, 4, '') == 'yes') {
				$button .= "<input title='" . getTranslatedString('LBL_SELECT') . " " . getTranslatedString($related_module) . "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id','test','width=640,height=602,resizable=0,scrollbars=0');\" value='" . getTranslatedString('LBL_SELECT') . " " . getTranslatedString($related_module) . "'>&nbsp;";
			}
			if (in_array('ADD', $actions) && isPermitted($related_module, 1, '') == 'yes') {
				$button .= "<input title='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "' accessyKey='F' class='crmbutton small create' onclick='fnvshobj(this,\"sendmail_cont\");sendmail(\"$this_module\",$id);' type='button' name='button' value='" . getTranslatedString('LBL_ADD_NEW') . " " . getTranslatedString($singular_modname) . "'></td>";
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name' =>
			'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT vtiger_ossmailview.*, vtiger_crmentity.modifiedtime, vtiger_crmentity.crmid, vtiger_crmentity.smownerid, case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name FROM vtiger_ossmailview 
			INNER JOIN vtiger_ossmailview_relation ON vtiger_ossmailview_relation.ossmailviewid = vtiger_ossmailview.ossmailviewid
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_ossmailview.ossmailviewid 
			LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid 
			LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id
			WHERE vtiger_crmentity.deleted = 0 AND vtiger_ossmailview_relation.crmid = " . $id . " ";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if ($return_value == null)
			$return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_emails method ...");
		return $return_value;
	}
}
