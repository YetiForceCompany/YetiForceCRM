<?php
/**
 * Main module file.
 *
 * @package CRMEntity
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
include_once 'modules/Vtiger/CRMEntity.php';
/**
 * SMS templates class.
 */
class SMSTemplates extends Vtiger_CRMEntity
{
	/** @var string Table name */
	public $table_name = 'u_yf_smstemplates';
	/** @var string Table index */
	public $table_index = 'smstemplatesid';

	/** @var array Mandatory table for supporting custom fields. */
	public $customFieldTable = ['u_yf_smstemplatescf', 'smstemplatesid'];

	/** @var array Mandatory for Saving, Include tables related to this module. */
	public $tab_name = ['vtiger_crmentity', 'u_yf_smstemplates', 'u_yf_smstemplatescf'];

	/** @var array Mandatory for Saving, Include tablename and tablekey columnname here. */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'u_yf_smstemplates' => 'smstemplatesid',
		'u_yf_smstemplatescf' => 'smstemplatesid',
	];

	/** @var array Default fields on the list */
	public $list_fields_name = [
		'FL_SUBJECT' => 'subject',
		'Assigned To' => 'assigned_user_id',
	];

	/** @var array For Popup listview and UI type support */
	public $search_fields = [
		'FL_SUBJECT' => ['smstemplates', 'subject'],
		'Assigned To' => ['vtiger_crmentity', 'assigned_user_id'],
	];
	/** @var array */
	public $search_fields_name = [];
	/** @var array For Popup window record selection */
	public $popup_fields = ['subject'];
	/** @var string For Alphabetical search */
	public $def_basicsearch_col = 'subject';
	/** @var string Column value to use on detail view record text display */
	public $def_detailview_recname = 'subject';
	/**
	 * Used when enabling/disabling the mandatory fields for the module.
	 * Refers to vtiger_field.fieldname values.
	 *
	 * @var array
	 */
	public $mandatory_fields = ['subject', 'assigned_user_id'];
	/** @var string Default field for sorting */
	public $default_order_by = '';
	/** @var string Default sort order */
	public $default_sort_order = 'ASC';

	/**
	 * Invoked when special actions are performed on the module.
	 *
	 * @param string $moduleName Module name
	 * @param string $eventType  Event Type
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		if ('module.postinstall' === $eventType) {
		} elseif ('module.disabled' === $eventType) {
		} elseif ('module.preuninstall' === $eventType) {
		} elseif ('module.preupdate' === $eventType) {
		} elseif ('module.postupdate' === $eventType) {
		}
	}
}
