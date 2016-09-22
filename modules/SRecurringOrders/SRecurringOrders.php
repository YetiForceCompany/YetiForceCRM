<?php
/**
 * @package YetiForce.CRMEntity
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

class SRecurringOrders extends Vtiger_CRMEntity
{

	var $table_name = 'u_yf_srecurringorders';
	var $table_index = 'srecurringordersid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('u_yf_srecurringorderscf', 'srecurringordersid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'u_yf_srecurringorders', 'u_yf_srecurringorderscf', 'u_yf_recurring_info', 'u_yf_srecurringorders_address', 'vtiger_entity_stats');
	var $related_tables = ['u_yf_recurring_info' => ['srecurringordersid', 'u_yf_srecurringorders', 'srecurringordersid']];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'u_yf_srecurringorders' => 'srecurringordersid',
		'u_yf_srecurringorderscf' => 'srecurringordersid',
		'u_yf_recurring_info' => 'srecurringordersid',
		'u_yf_srecurringorders_address' => 'srecurringordersaddressid',
		'vtiger_entity_stats' => 'crmid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'LBL_SUBJECT' => Array('srecurringorders', 'subject'),
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
		'LBL_SUBJECT' => Array('srecurringorders', 'subject'),
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
	var $default_order_by = '';
	var $default_sort_order = 'ASC';

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type
	 */
	public function vtlib_handler($moduleName, $eventType)
	{
		$adb = PearDatabase::getInstance();
		if ($eventType == 'module.postinstall') {
			$moduleInstance = CRMEntity::getInstance('SRecurringOrders');
			\includes\fields\RecordNumber::setNumber($moduleName, 'S-RO', '1');
			$adb->pquery('UPDATE vtiger_tab SET customized=0 WHERE name=?', ['SRecurringOrders']);

			$modcommentsModuleInstance = vtlib\Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments'))
					ModComments::addWidgetTo(array('SRecurringOrders'));
			}
			$modcommentsModuleInstance = vtlib\Module::getInstance('ModTracker');
			if ($modcommentsModuleInstance && file_exists('modules/ModTracker/ModTracker.php')) {
				include_once 'modules/ModTracker/ModTracker.php';
				$tabid = vtlib\Functions::getModuleId('SRecurringOrders');
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

	public function getJoinClause($tableName)
	{
		if ($tableName == 'vtiger_recurring_info') {
			return 'LEFT JOIN';
		}
		return parent::getJoinClause($tableName);
	}
}
