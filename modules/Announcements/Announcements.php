<?php
/**
 * Announcements CRMEntity Class
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';

class Announcements extends Vtiger_CRMEntity
{

	public var $table_name = 'u_yf_announcement';
	public var $table_index = 'announcementid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public var $customFieldTable = ['u_yf_announcementcf', 'announcementid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public var $tab_name = ['vtiger_crmentity', 'u_yf_announcement', 'u_yf_announcementcf', 'u_yf_announcement_mark'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public var $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'u_yf_announcement' => 'announcementid',
		'u_yf_announcementcf' => 'announcementid',
		'u_yf_announcement_mark' => 'announcementid'
	];

	/**
	 * Mandatory for Listing (Related listview)
	 */
	public var $list_fields = [
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'LBL_SUBJECT' => ['announcement', 'subject'],
		'Assigned To' => ['crmentity', 'smownerid']
	];
	public var $list_fields_name = [
		/* Format: Field Label => fieldname */
		'LBL_SUBJECT' => 'subject',
		'Assigned To' => 'assigned_user_id',
	];
	// Make the field link to detail view
	public var $list_link_field = 'subject';
	// For Popup listview and UI type support
	public var $search_fields = [
		/* Format: Field Label => Array(tablename, columnname) */
		// tablename should not have prefix 'vtiger_'
		'LBL_SUBJECT' => ['announcement', 'subject'],
		'Assigned To' => ['vtiger_crmentity', 'assigned_user_id'],
	];
	public var $search_fields_name = [
		/* Format: Field Label => fieldname */
		'LBL_SUBJECT' => 'subject',
		'Assigned To' => 'assigned_user_id',
	];
	// For Popup window record selection
	public var $popup_fields = ['subject'];
	// For Alphabetical search
	public var $def_basicsearch_col = 'subject';
	// Column value to use on detail view record text display
	public var $def_detailview_recname = 'subject';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public var $mandatory_fields = ['subject', 'assigned_user_id'];
	public var $default_order_by = '';
	public var $default_sort_order = 'ASC';
	protected $lockFields = ['announcementstatus' => ['PLL_PUBLISHED', 'PLL_ARCHIVES']];

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type
	 */
	public function vtlib_handler($moduleName, $eventType)
	{
		$adb = PearDatabase::getInstance();
		if ($eventType == 'module.postinstall') {
			\includes\fields\RecordNumber::setNumber($moduleName, 'NO', '1');
			$adb->pquery('UPDATE vtiger_tab SET customized=0 WHERE name=?', array('Announcements'));

		} else if ($eventType == 'module.disabled') {

		} else if ($eventType == 'module.preuninstall') {

		} else if ($eventType == 'module.preupdate') {

		} else if ($eventType == 'module.postupdate') {

		}
	}
}
