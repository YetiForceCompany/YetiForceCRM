<?php
/**
 * OSSMailScanner CRMEntity class
 * @package YetiForce.CRMEntity
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
include_once 'modules/Vtiger/CRMEntity.php';

class PaymentsOut extends Vtiger_CRMEntity
{

	public $table_name = 'vtiger_paymentsout';
	public $table_index = 'paymentsoutid';
	public $column_fields = [];

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = Array('vtiger_paymentsoutcf', 'paymentsoutid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = Array('vtiger_crmentity', 'vtiger_paymentsout', 'vtiger_paymentsoutcf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_paymentsout' => 'paymentsoutid',
		'vtiger_paymentsoutcf' => 'paymentsoutid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	public $list_fields = array(
		'LBL_PAYMENTSNO' => array('vtiger_paymentsout' => 'paymentsno'),
		'LBL_PAYMENTSNAME' => array('vtiger_paymentsout' => 'paymentsname'),
		'LBL_PAYMENTSVALUE' => array('vtiger_paymentsout' => 'paymentsvalue'),
		'LBL_PAYMENTSCURRENCY' => array('vtiger_paymentsout' => 'paymentscurrency'),
		'LBL_PAYMENTSSTATUS' => array('vtiger_paymentsout' => 'paymentsout_status'),
	);
	public $list_fields_name = array(
		'LBL_PAYMENTSNO' => 'paymentsno',
		'LBL_PAYMENTSNAME' => 'paymentsname',
		'LBL_PAYMENTSVALUE' => 'paymentsvalue',
		'LBL_PAYMENTSCURRENCY' => 'paymentscurrency',
		'LBL_PAYMENTSSTATUS' => 'paymentsout_status',
	);

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['paymentsno', 'paymentsname', 'paymentsvalue', 'paymentscurrency', 'paymentsout_status'];
	// Make the field link to detail view from list view (Fieldname)
	public $list_link_field = 'paymentsname';
	// For Popup listview and UI type support
	public $search_fields = array(
		'LBL_PAYMENTSVALUE' => array('paymentsout', 'paymentsvalue'),
		'LBL_PAYMENTSNO' => array('paymentsout', 'paymentsno'),
		'LBL_PAYMENTSNAME' => array('paymentsout', 'paymentsname'),
	);
	public $search_fields_name = array(
		'LBL_PAYMENTSVALUE' => 'paymentsvalue',
		'LBL_PAYMENTSNO' => 'paymentsno',
		'LBL_PAYMENTSNAME' => 'paymentsname',
	);
	// For Popup window record selection
	public $popup_fields = array('paymentsname');
	// For Alphabetical search
	public $def_basicsearch_col = 'paymentsname';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'paymentsname';
	// Required Information for enabling Import feature
	public $required_fields = array('paymentsname' => 1);
	// Callback function list during Importing
	public $special_functions = Array('set_import_assigned_user');
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = array('createdtime', 'modifiedtime', 'paymentsname');

	/**
	 * Invoked when special actions are performed on the module.
	 * @param String Module name
	 * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	public function vtlib_handler($modulename, $event_type)
	{
		$adb = PearDatabase::getInstance();
		if ($event_type == 'module.postinstall') {
			$ModuleInstance = CRMEntity::getInstance($modulename);
			\App\Fields\RecordNumber::setNumber($modulename, '', '1');
			$modcommentsModuleInstance = vtlib\Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments'))
					ModComments::addWidgetTo(array('Payments'));
			}
			$adb->pquery('UPDATE vtiger_tab SET customized=0 WHERE name=?', array($modulename));
			CRMEntity::getInstance('ModTracker')->enableTrackingForModule(vtlib\Functions::getModuleId($modulename));
			$this->addWorkflow($modulename);
		} else if ($event_type == 'module.disabled') {

		} else if ($event_type == 'module.enabled') {

		} else if ($event_type == 'module.preuninstall') {

		} else if ($event_type == 'module.preupdate') {

		} else if ($event_type == 'module.postupdate') {

		}
	}

	private function addWorkflow($moduleName)
	{
		Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/include.php');
		Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/tasks/VTEntityMethodTask.php');
		Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/VTEntityMethodManager.php');
		$functionName = 'UpdateBalance';
		$emm = new VTEntityMethodManager();
		$emm->addEntityMethod($moduleName, $functionName, "modules/PaymentsIn/workflow/UpdateBalance.php", $functionName);

		$workflowManager = new VTWorkflowManager();
		$taskManager = new VTTaskManager();

		$newWorkflow = $workflowManager->newWorkFlow($moduleName);
		$newWorkflow->test = '[]';
		$newWorkflow->defaultworkflow = 0;
		$newWorkflow->description = "$moduleName - UpdateBalance";
		$newWorkflow->executionCondition = 3;
		$workflowManager->save($newWorkflow);

		$task = $taskManager->createTask('VTEntityMethodTask', $newWorkflow->id);
		$task->active = true;
		$task->summary = 'UpdateBalance';
		$task->methodName = $functionName;
		$taskManager->saveTask($task);
	}
}
