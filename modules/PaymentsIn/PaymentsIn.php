<?php
/**
 * PaymentsIn CRMEntity class
 * @package YetiForce.CRMEntity
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
include_once 'modules/Vtiger/CRMEntity.php';

class PaymentsIn extends Vtiger_CRMEntity
{

	public $table_name = 'vtiger_paymentsin';
	public $table_index = 'paymentsinid';
	public $column_fields = [];

	/** Indicator if this is a custom module or standard module */
	public $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	public $customFieldTable = ['vtiger_paymentsincf', 'paymentsinid'];

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	public $tab_name = ['vtiger_crmentity', 'vtiger_paymentsin', 'vtiger_paymentsincf'];

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	public $tab_name_index = [
		'vtiger_crmentity' => 'crmid',
		'vtiger_paymentsin' => 'paymentsinid',
		'vtiger_paymentsincf' => 'paymentsinid'];

	/**
	 * Mandatory for Listing (Related listview)
	 */
	public $list_fields = [
		'LBL_PAYMENTSNO' => ['vtiger_paymentsin' => 'paymentsno'],
		'LBL_PAYMENTSNAME' => ['vtiger_paymentsin' => 'paymentsname'],
		'LBL_PAYMENTSVALUE' => ['vtiger_paymentsin' => 'paymentsvalue'],
		'LBL_PAYMENTSCURRENCY' => ['vtiger_paymentsin' => 'paymentscurrency'],
		'LBL_PAYMENTSSTATUS' => ['vtiger_paymentsin' => 'paymentsin_status']
	];
	public $list_fields_name = [
		'LBL_PAYMENTSNO' => 'paymentsno',
		'LBL_PAYMENTSNAME' => 'paymentsname',
		'LBL_PAYMENTSVALUE' => 'paymentsvalue',
		'LBL_PAYMENTSCURRENCY' => 'paymentscurrency',
		'LBL_PAYMENTSSTATUS' => 'paymentsin_status'
	];

	/**
	 * @var string[] List of fields in the RelationListView
	 */
	public $relationFields = ['paymentsno', 'paymentsname', 'paymentsvalue', 'paymentscurrency', 'paymentsin_status'];
	// Make the field link to detail view from list view (Fieldname)
	public $list_link_field = 'paymentsname';
	// For Popup listview and UI type support
	public $search_fields = [
		'LBL_PAYMENTSNO' => ['paymentsin', 'paymentsno'],
		'LBL_PAYMENTSNAME' => ['paymentsin', 'paymentsname'],
		'LBL_PAYMENTSVALUE' => ['paymentsin', 'paymentsvalue'],
		'LBL_PAYMENTSCURRENCY' => ['paymentsin', 'paymentscurrency'],
		'LBL_PAYMENTSSTATUS' => ['paymentsin', 'paymentsin_status'],
	];
	public $search_fields_name = [
		'LBL_PAYMENTSNO' => 'paymentsno',
		'LBL_PAYMENTSNAME' => 'paymentsname',
		'LBL_PAYMENTSVALUE' => 'paymentsvalue',
		'LBL_PAYMENTSCURRENCY' => 'paymentscurrency',
		'LBL_PAYMENTSSTATUS' => 'paymentsin_status',
	];
	// For Popup window record selection
	public $popup_fields = ['paymentsname'];
	// For Alphabetical search
	public $def_basicsearch_col = 'paymentsname';
	// Column value to use on detail view record text display
	public $def_detailview_recname = 'paymentsname';
	// Required Information for enabling Import feature
	public $required_fields = ['paymentsname' => 1];
	// Callback function list during Importing
	public $special_functions = ['set_import_assigned_user'];
	public $default_order_by = '';
	public $default_sort_order = 'ASC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	public $mandatory_fields = ['createdtime', 'modifiedtime', 'paymentsname'];

	/**
	 * Invoked when special actions are performed on the module.
	 * @param string $moduleName Module name
	 * @param string $eventType Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
	 */
	public function moduleHandler($moduleName, $eventType)
	{
		if ($eventType === 'module.postinstall') {
			\App\Fields\RecordNumber::setNumber($moduleName, '', '1');
			$modcommentsModuleInstance = vtlib\Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments'))
					ModComments::addWidgetTo(['Payments']);
			}
			\App\Db::getInstance()->createCommand()->update('vtiger_tab', ['customized' => 0], ['name' => $moduleName])->execute();
			CRMEntity::getInstance('ModTracker')->enableTrackingForModule(\App\Module::getModuleId($moduleName));

			$blockInstance = vtlib\Block::getInstance('LBL_ACCOUNT_INFORMATION', \App\Module::getModuleId('Accounts'));
			$fieldInstance = new vtlib\Field();
			$fieldInstance->name = 'payment_balance';
			$fieldInstance->table = 'vtiger_account';
			$fieldInstance->label = 'Payment balance';
			$fieldInstance->column = 'payment_balance';
			$fieldInstance->columntype = 'decimal(25,8)';
			$fieldInstance->uitype = 7;
			$fieldInstance->typeofdata = 'NN~O';
			$fieldInstance->displaytype = 2;
			$blockInstance->addField($fieldInstance);

			$this->addWorkflow($moduleName);
		}
	}

	private function addWorkflow($moduleName)
	{
		Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/include.php');
		Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/tasks/VTEntityMethodTask.php');
		Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/VTEntityMethodManager.php');
		$functionName = 'UpdateBalance';
		$emm = new VTEntityMethodManager();
		$emm->addEntityMethod($moduleName, $functionName, 'modules/PaymentsIn/workflow/UpdateBalance.php', $functionName);

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
