<?php
/**
 * FInvoice CRMEntity Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

class FInvoice extends Vtiger_CRMEntity
{

	var $table_name = 'u_yf_finvoice';
	var $table_index = 'finvoiceid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('u_yf_finvoicecf', 'finvoiceid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'u_yf_finvoice', 'u_yf_finvoicecf', 'u_yf_finvoice_address');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'u_yf_finvoice' => 'finvoiceid',
		'u_yf_finvoicecf' => 'finvoiceid',
		'u_yf_finvoice_address' => 'finvoiceaddressid'
	);

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
// tablename should not have prefix 'vtiger_'
		'FL_SUBJECT' => Array('finvoice', 'subject'),
		'FL_SALE_DATE' => Array('finvoice', 'saledate'),
		'Assigned To' => Array('crmentity', 'smownerid')
	);
	var $list_fields_name = Array(
		/* Format: Field Label => fieldname */
		'FL_SUBJECT' => 'subject',
		'FL_SALE_DATE' => 'saledate',
		'Assigned To' => 'assigned_user_id',
	);
// Make the field link to detail view
	var $list_link_field = 'subject';
// For Popup listview and UI type support
	var $search_fields = Array(
		/* Format: Field Label => Array(tablename, columnname) */
// tablename should not have prefix 'vtiger_'
		'FL_SUBJECT' => Array('finvoice', 'subject'),
		'FL_SALE_DATE' => Array('finvoice', 'saledate'),
		'Assigned To' => Array('vtiger_crmentity', 'assigned_user_id'),
	);
	var $search_fields_name = Array(
		/* Format: Field Label => fieldname */
		'FL_SUBJECT' => 'subject',
		'FL_SALE_DATE' => 'saledate',
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
			$moduleInstance = CRMEntity::getInstance($moduleName);
			$moduleInstance->setModuleSeqNumber('configure', 'SQuotes', 'F-I', '1');
			$adb->pquery('UPDATE vtiger_tab SET customized=0 WHERE name=?', [$moduleName]);

			$modcommentsModuleInstance = Vtiger_Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments'))
					ModComments::addWidgetTo(array($moduleName));
			}
			$modTrackerInstance = Vtiger_Module::getInstance('ModTracker');
			if ($modTrackerInstance && file_exists('modules/ModTracker/ModTracker.php')) {
				include_once('vtlib/Vtiger/Module.php');
				include_once 'modules/ModTracker/ModTracker.php';
				$tabid = Vtiger_Functions::getModuleId($moduleName);
				ModTracker::enableTrackingForModule($tabid);
			}
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
}
