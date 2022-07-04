<?php
/**
 * Reservations CRMEntity class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
include_once 'modules/Vtiger/CRMEntity.php';

class Reservations extends Vtiger_CRMEntity
{
	public $table_name = 'vtiger_reservations';
	public $table_index = 'reservationsid';
	public $column_fields = [];

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_reservationscf', 'reservationsid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'vtiger_reservations', 'vtiger_reservationscf'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'vtiger_reservations' => 'reservationsid',
		'vtiger_reservationscf' => 'reservationsid', ];

	public $list_fields_name = [
		// Format: Field Label => fieldname
		'No.' => 'reservations_no',
		'Assigned To' => 'assigned_user_id',
		'Created Time' => 'createdtime',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = [];

	// For Popup listview and UI type support
	public $search_fields = [
		'No.' => ['reservations', 'reservations_no'],
		'Assigned To' => ['crmentity', 'smownerid'],
		'Created Time' => ['crmentity', 'createdtime'],
	];
	public $search_fields_name = [];
	// For Popup window record selection
	public $popup_fields = ['name'];
	// For Alphabetical search
	public $def_basicsearch_col = 'name';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'name';
	// Callback function list during Importing
	public $special_functions = ['set_import_assigned_user'];
	public $default_order_by = '';
	public $default_sort_order = 'DESC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['createdtime', 'modifiedtime', 'assigned_user_id'];

	/**
	 * Invoked when special actions are performed on the module.
	 *
	 * @param string $moduleName Module name
	 * @param string $eventType  Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		if ('module.postinstall' === $eventType) {
			\App\Db::getInstance()->createCommand()->update('vtiger_tab', ['customized' => 0], ['name' => 'Reservations'])->execute();
			$moduleInstance = vtlib\Module::getInstance($moduleName);
			$targetModule = vtlib\Module::getInstance('Accounts');
			$targetModule->setRelatedList($moduleInstance, 'Reservations', ['ADD'], 'getDependentsList');
			$targetModule = vtlib\Module::getInstance('HelpDesk');
			$targetModule->setRelatedList($moduleInstance, 'Reservations', ['ADD'], 'getDependentsList');
			$targetModule = vtlib\Module::getInstance('Leads');
			$targetModule->setRelatedList($moduleInstance, 'Reservations', ['ADD'], 'getDependentsList');
			$targetModule = vtlib\Module::getInstance('Project');
			$targetModule->setRelatedList($moduleInstance, 'Reservations', ['ADD'], 'getDependentsList');
			$targetModule = vtlib\Module::getInstance('Vendors');
			$targetModule->setRelatedList($moduleInstance, 'Reservations', ['ADD'], 'getDependentsList');
		}
	}

	/** {@inheritdoc} */
	public function retrieveEntityInfo(int $record, string $module)
	{
		parent::retrieveEntityInfo($record, $module);
		$start = DateTimeField::convertToUserTimeZone($this->column_fields['date_start'] . ' ' . $this->column_fields['time_start']);
		$this->column_fields['date_start'] = $start->format('Y-m-d');
		$end = DateTimeField::convertToUserTimeZone($this->column_fields['due_date'] . ' ' . $this->column_fields['time_end']);
		$this->column_fields['due_date'] = $end->format('Y-m-d');
	}
}
