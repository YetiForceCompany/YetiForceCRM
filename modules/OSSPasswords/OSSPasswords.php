<?php

/**
 * OSSPasswords CRMEntity class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSPasswords extends CRMEntity
{
	public $table_name = 'vtiger_osspasswords';
	public $table_index = 'osspasswordsid';
	public $column_fields = [];

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_osspasswordscf', 'osspasswordsid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'vtiger_osspasswords', 'vtiger_osspasswordscf'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'vtiger_osspasswords' => 'osspasswordsid',
		'vtiger_osspasswordscf' => 'osspasswordsid', ];

	/**
	 * Mandatory for Listing (Related listview).
	 */
	public $list_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'OSSPassword No' => ['osspasswords' => 'osspassword_no'],
		'Key name' => ['osspasswords' => 'passwordname'],
		'Username' => ['osspasswords' => 'username'],
		'Password' => ['osspasswords' => 'password'],
		'WWW page' => ['osspasswords' => 'link_adres'],
	];
	public $list_fields_name = [
		// Format: Field Label => fieldname
		'OSSPassword No' => 'osspassword_no',
		'Key name' => 'passwordname',
		'Username' => 'username',
		'Password' => 'password',
		'WWW page' => 'link_adres',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['osspassword_no', 'passwordname', 'username', 'password', 'link_adres'];
	// Make the field link to detail view
	public $list_link_field = 'passwordname';
	// For Popup listview and UI type support
	public $search_fields = [
		// Format: Field Label => Array(tablename, columnname)
		// tablename should not have prefix 'vtiger_'
		'OSSPassword No' => ['osspasswords' => 'osspassword_no'],
		'Key name' => ['osspasswords' => 'passwordname'],
		'Username' => ['osspasswords' => 'username'],
		'WWW page' => ['osspasswords' => 'link_adres'],
	];
	public $search_fields_name = [
		// Format: Field Label => fieldname
		'OSSPassword No' => 'osspassword_no',
		'Key name' => 'passwordname',
		'Username' => 'username',
		'WWW page' => 'link_adres',
	];
	// For Popup window record selection
	public $popup_fields = ['username'];
	// For Alphabetical search
	public $def_basicsearch_col = 'passwordname';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['passwordname'];
	// Callback function list during Importing
	public $special_functions = ['set_import_assigned_user'];
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
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
	 * @param string Module name
	 * @param string Event Type
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		$db = App\Db::getInstance();
		$registerLink = false;
		$addModTracker = false;

		if ($eventType === 'module.disabled') {
			$registerLink = false;
			App\EventHandler::setInActive('OSSPasswords_Secure_Handler');
		} elseif ($eventType === 'module.enabled') {
			$registerLink = true;
			App\EventHandler::setActive('OSSPasswords_Secure_Handler');
		} elseif ($eventType === 'module.preuninstall') {
			\App\Log::trace('Before starting uninstall script...');
			require_once 'modules/Settings/' . $moduleName . '/views/uninstall.php';
			\App\Log::trace('After uninstall script.');

			header('location: index.php?module=Vtiger&parent=Settings&view=Index');
		}

		$displayLabel = 'OSSPassword Configuration';

		if ($registerLink) {
			Settings_Vtiger_Module_Model::addSettingsField('LBL_SECURITY_MANAGEMENT', [
				'name' => $displayLabel,
				'iconpath' => 'adminIcon-passwords-encryption',
				'description' => 'LBL_OSSPASSWORD_CONFIGURATION_DESCRIPTION',
				'linkto' => 'index.php?module=OSSPasswords&view=ConfigurePass&parent=Settings',
			]);
		} else {
			$db->createCommand()->delete('vtiger_settings_field', ['name' => $displayLabel])->execute();
		}

		// register modtracker history updates
		if ($addModTracker) {
			CRMEntity::getInstance('ModTracker')->enableTrackingForModule(\App\Module::getModuleId($moduleName));
		}
	}
}
