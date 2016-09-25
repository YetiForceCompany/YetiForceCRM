<?php
/**
 * @package YetiForce.CRMEntity
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

class SQuoteEnquiries extends Vtiger_CRMEntity
{

	public var $table_name = 'u_yf_squoteenquiries';
	public var $table_index = 'squoteenquiriesid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public var $customFieldTable = Array('u_yf_squoteenquiriescf', 'squoteenquiriesid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public var $tab_name = Array('vtiger_crmentity', 'u_yf_squoteenquiries', 'u_yf_squoteenquiriescf', 'vtiger_entity_stats');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'u_yf_squoteenquiries' => 'squoteenquiriesid',
		'u_yf_squoteenquiriescf' => 'squoteenquiriesid',
		'vtiger_entity_stats' => 'crmid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	public var $list_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'LBL_SUBJECT' => Array('squoteenquiries', 'subject'),
		'Assigned To' => Array('crmentity', 'smownerid')
	);
	public var $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'LBL_SUBJECT' => 'subject',
		'Assigned To' => 'assigned_user_id',
	);
	// Make the field link to detail view
	public var $list_link_field = 'subject';
	// For Popup listview and UI type support
	public var $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'LBL_SUBJECT' => Array('squoteenquiries', 'subject'),
		'Assigned To' => Array('vtiger_crmentity', 'assigned_user_id'),
	);
	public var $search_fields_name = Array(
		/* Format: Field Label => fieldname */
		'LBL_SUBJECT' => 'subject',
		'Assigned To' => 'assigned_user_id',
	);
	// For Popup window record selection
	public var $popup_fields = Array('subject');
	// For Alphabetical search
	public var $def_basicsearch_col = 'subject';
	// Column value to use on detail view record text display
	public var $def_detailview_recname = 'subject';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public var $mandatory_fields = Array('subject', 'assigned_user_id');
	public var $default_order_by = '';
	public var $default_sort_order = 'ASC';

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type
	 */
	public function vtlib_handler($moduleName, $eventType)
	{
		$adb = PearDatabase::getInstance();
		if ($eventType == 'module.postinstall') {
			\includes\fields\RecordNumber::setNumber($moduleName, 'S-QE', '1');
			$adb->pquery('UPDATE vtiger_tab SET customized=0 WHERE name=?', ['SQuoteEnquiries']);

			$modcommentsModuleInstance = vtlib\Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments'))
					ModComments::addWidgetTo(array('SQuoteEnquiries'));
			}
			$modcommentsModuleInstance = vtlib\Module::getInstance('ModTracker');
			if ($modcommentsModuleInstance && file_exists('modules/ModTracker/ModTracker.php')) {
				include_once 'modules/ModTracker/ModTracker.php';
				$tabid = vtlib\Functions::getModuleId('SQuoteEnquiries');
				$moduleModTrackerInstance = new ModTracker();
				if (!$moduleModTrackerInstance->isModulePresent($tabid)) {
					$res = $adb->pquery("INSERT INTO vtiger_modtracker_tabs VALUES(?,?)", array($tabid, 1));
					$moduleModTrackerInstance->updateCache($tabid, 1);
				} else {
					$updatevisibility = $adb->pquery("UPDATE vtiger_modtracker_tabs SET visible = 1 WHERE tabid = ?", array($tabid));
					$moduleModTrackerInstance->updateCache($tabid, 1);
				}
				if (!$moduleModTrackerInstance->isModTrackerLinkPresent($tabid)) {
					$moduleInstance = vtlib\Module::getInstance($tabid);
					$moduleInstance->addLink('DETAILVIEWBASIC', 'View History', "javascript:ModTrackerCommon.showhistory('\$RECORD\$')", '', '', array('path' => 'modules/ModTracker/ModTracker.php', 'class' => 'ModTracker', 'method' => 'isViewPermitted'));
				}
			}

		} else if ($eventType == 'module.disabled') {

		} else if ($eventType == 'module.preuninstall') {
			
		} else if ($eventType == 'module.preupdate') {

		} else if ($eventType == 'module.postupdate') {

		}
	}
}
