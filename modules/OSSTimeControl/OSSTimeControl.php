<?php
/**
 * OSSTimeControl CRMEntity class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
include_once 'modules/Vtiger/CRMEntity.php';

class OSSTimeControl extends Vtiger_CRMEntity
{
	public $table_name = 'vtiger_osstimecontrol';
	public $table_index = 'osstimecontrolid';
	public $column_fields = [];

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_osstimecontrolcf', 'osstimecontrolid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'vtiger_osstimecontrol', 'vtiger_osstimecontrolcf'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'vtiger_osstimecontrol' => 'osstimecontrolid',
		'vtiger_osstimecontrolcf' => 'osstimecontrolid', ];

	public $list_fields_name = [
		// Format: Field Label => fieldname
		'No.' => 'osstimecontrol_no',
		'Assigned To' => 'assigned_user_id',
		'Created Time' => 'createdtime',
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = [];

	// For Popup listview and UI type support
	public $search_fields = [
		'No.' => ['osstimecontrol', 'osstimecontrol_no'],
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
	 * @param string Module name
	 * @param string Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 * @param mixed $modulename
	 * @param mixed $eventType
	 */
	public function moduleHandler($modulename, $eventType)
	{
		if ('module.postinstall' === $eventType) {
			\App\Db::getInstance()->createCommand()->update('vtiger_field', ['summaryfield' => 1], ['tabid' => \App\Module::getModuleId($modulename), 'columnname' => ['name', 'osstimecontrol_no', 'osstimecontrol_status', 'smownerid', 'date_start', 'time_start', 'time_end', 'due_date', 'sum_time']])->execute();
			$modcommentsModuleInstance = vtlib\Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments')) {
					ModComments::addWidgetTo(['OSSTimeControl']);
				}
			}
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
