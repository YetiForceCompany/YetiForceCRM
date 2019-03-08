<?php

/**
 * OSSMailView CRMEntity class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMailView extends CRMEntity
{
	public $table_name = 'vtiger_ossmailview';
	public $table_index = 'ossmailviewid';
	public $column_fields = [];

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_ossmailviewcf', 'ossmailviewid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'vtiger_ossmailview', 'vtiger_ossmailviewcf'];
	public $related_tables = ['vtiger_ossmailviewcf' => ['ossmailviewid', 'vtiger_ossmailview', 'ossmailviewid']];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'vtiger_ossmailview' => 'ossmailviewid',
		'vtiger_ossmailviewcf' => 'ossmailviewid', ];

	/**
	 * Mandatory for Listing (Related listview).
	 */
	public $list_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'number' => ['ossmailview' => 'ossmailview_no'],
		'From' => ['ossmailview' => 'from_email'],
		'Subject' => ['ossmailview' => 'subject'],
		'To' => ['ossmailview' => 'to_email'],
		'SendType' => ['ossmailview' => 'ossmailview_sendtype'],
		'Assigned To' => ['ossmailview' => 'assigned_user_id'],
	];
	public $list_fields_name = [
		// Format: Field Label => fieldname
		'number' => 'ossmailview_no',
		'From' => 'from_email',
		'Subject' => 'subject',
		'To' => 'to_email',
		'SendType' => 'ossmailview_sendtype',
		'Assigned To' => 'assigned_user_id',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['ossmailview_no', 'from_email', 'subject', 'to_email', 'ossmailview_sendtype', 'assigned_user_id'];
	// Make the field link to detail view
	public $list_link_field = 'subject';
	// For Popup listview and UI type support
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'number' => ['ossmailview' => 'ossmailview_no'],
		'From' => ['ossmailview' => 'from_email'],
		'Subject' => ['ossmailview' => 'subject'],
		'To' => ['ossmailview' => 'to_email'],
		'SendType' => ['ossmailview' => 'ossmailview_sendtype'],
		'Assigned To' => ['ossmailview' => 'assigned_user_id'],
	];
	public $search_fields_name = [
		// Format: Field Label => fieldname
		'number' => 'ossmailview_no',
		'From' => 'from_email',
		'Subject' => 'subject',
		'To' => 'to_email',
		'SendType' => 'ossmailview_sendtype',
		'Assigned To' => 'assigned_user_id',
	];
	// For Popup window record selection
	public $popup_fields = ['from', 'subject', 'ossmailview_sendtype'];
	// For Alphabetical search
	public $def_basicsearch_col = 'subject';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['subject', 'from'];
	// Callback function list during Importing
	public $special_functions = ['set_import_assigned_user'];
	public $default_order_by = 'date';
	public $default_sort_order = 'DESC';
	public $unit_price;

	/**
	 * Transform the value while exporting.
	 */
	public function transformExportValue($key, $value)
	{
		if ($key == 'owner') {
			return \App\Fields\Owner::getLabel($value);
		}
		return parent::transformExportValue($key, $value);
	}

	/**
	 * Invoked when special actions are performed on the module.
	 *
	 * @param string $moduleName Module name
	 * @param string $eventType  Event Type
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		$dbCommand = App\Db::getInstance()->createCommand();
		if ($eventType === 'module.postinstall') {
			$displayLabel = 'OSSMailView';
			$dbCommand->update('vtiger_tab', ['customized' => 0], ['name' => $displayLabel])->execute();
			$dbCommand->insert('vtiger_ossmailscanner_config', ['conf_type' => 'email_list', 'parameter' => 'widget_limit', 'value' => '10'])->execute();
			$dbCommand->insert('vtiger_ossmailscanner_config', ['conf_type' => 'email_list', 'parameter' => 'target', 'value' => '_blank'])->execute();
			$dbCommand->insert('vtiger_ossmailscanner_config', ['conf_type' => 'email_list', 'parameter' => 'permissions', 'value' => 'vtiger'])->execute();
			CRMEntity::getInstance('ModTracker')->enableTrackingForModule(\App\Module::getModuleId($moduleName));
			$registerLink = true;
			$module = vtlib\Module::getInstance($moduleName);
			$userName = \App\User::getCurrentUserModel()->getDetail('user_name');
			$dbCommand->insert('vtiger_ossmails_logs', ['action' => 'Action_InstallModule', 'info' => $moduleName . ' ' . $module->version, 'user' => $userName])->execute();
		} elseif ($eventType === 'module.disabled') {
			$registerLink = false;
		} elseif ($eventType === 'module.enabled') {
			$registerLink = true;
		} elseif ($eventType === 'module.postupdate') {
			$module = vtlib\Module::getInstance($moduleName);
			$userName = \App\User::getCurrentUserModel()->getDetail('user_name');
			$dbCommand->insert('vtiger_ossmails_logs', ['action' => 'Action_UpdateModule', 'info' => $moduleName . ' ' . $module->version, 'user' => $userName, 'start_time' => date('Y-m-d H:i:s')])->execute();
		}
		$displayLabel = 'Mail View';
		if ($registerLink) {
			Settings_Vtiger_Module_Model::addSettingsField('LBL_MAIL_TOOLS', [
				'name' => $displayLabel,
				'iconpath' => 'adminIcon-oss_mailview',
				'description' => 'LBL_MAIL_VIEW_DESCRIPTION',
				'linkto' => 'index.php?module=OSSMailView&parent=Settings&view=index',
			]);
		} else {
			$dbCommand->delete('vtiger_settings_field', ['name' => $displayLabel])->execute();
		}
	}
}
