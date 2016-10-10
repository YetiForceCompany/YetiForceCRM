<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com.
 * All Rights Reserved.
 * *********************************************************************************************************************************** */
include_once 'modules/Vtiger/CRMEntity.php';

class PaymentsIn extends Vtiger_CRMEntity
{

	var $db, $log; // Used in class functions of CRMEntity
	var $table_name = 'vtiger_paymentsin';
	var $table_index = 'paymentsinid';
	var $column_fields = Array();

	/** Indicator if this is a custom module or standard module */
	var $IsCustomModule = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_paymentsincf', 'paymentsinid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_paymentsin', 'vtiger_paymentsincf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_paymentsin' => 'paymentsinid',
		'vtiger_paymentsincf' => 'paymentsinid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = array(
		'LBL_PAYMENTSNO' => array('vtiger_paymentsin' => 'paymentsno'),
		'LBL_PAYMENTSNAME' => array('vtiger_paymentsin' => 'paymentsname'),
		'LBL_PAYMENTSVALUE' => array('vtiger_paymentsin' => 'paymentsvalue'),
		'LBL_PAYMENTSCURRENCY' => array('vtiger_paymentsin' => 'paymentscurrency'),
		'LBL_PAYMENTSSTATUS' => array('vtiger_paymentsin' => 'paymentsin_status')
	);
	var $list_fields_name = array(
		'LBL_PAYMENTSNO' => 'paymentsno',
		'LBL_PAYMENTSNAME' => 'paymentsname',
		'LBL_PAYMENTSVALUE' => 'paymentsvalue',
		'LBL_PAYMENTSCURRENCY' => 'paymentscurrency',
		'LBL_PAYMENTSSTATUS' => 'paymentsin_status'
	);
	// Make the field link to detail view from list view (Fieldname)
	var $list_link_field = 'paymentsname';
	// For Popup listview and UI type support
	var $search_fields = array(
		'LBL_PAYMENTSNO' => array('paymentsin', 'paymentsno'),
		'LBL_PAYMENTSNAME' => array('paymentsin', 'paymentsname'),
		'LBL_PAYMENTSVALUE' => array('paymentsin', 'paymentsvalue'),
		'LBL_PAYMENTSCURRENCY' => array('paymentsin', 'paymentscurrency'),
		'LBL_PAYMENTSSTATUS' => array('paymentsin', 'paymentsin_status'),
	);
	var $search_fields_name = array(
		'LBL_PAYMENTSNO' => 'paymentsno',
		'LBL_PAYMENTSNAME' => 'paymentsname',
		'LBL_PAYMENTSVALUE' => 'paymentsvalue',
		'LBL_PAYMENTSCURRENCY' => 'paymentscurrency',
		'LBL_PAYMENTSSTATUS' => 'paymentsin_status',
	);
	// For Popup window record selection
	var $popup_fields = array('paymentsname');
	// Placeholder for sort fields - All the fields will be initialized for Sorting through initSortFields
	var $sortby_fields = Array();
	// For Alphabetical search
	var $def_basicsearch_col = 'paymentsname';
	// Column value to use on detail view record text display
	var $def_detailview_recname = 'paymentsname';
	// Required Information for enabling Import feature
	var $required_fields = array('paymentsname' => 1);
	// Callback function list during Importing
	var $special_functions = Array('set_import_assigned_user');
	var $default_order_by = '';
	var $default_sort_order = 'ASC';
	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = array('createdtime', 'modifiedtime', 'paymentsname');

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
			\includes\fields\RecordNumber::setNumber($modulename, '', '1');
			$modcommentsModuleInstance = vtlib\Module::getInstance('ModComments');
			if ($modcommentsModuleInstance && file_exists('modules/ModComments/ModComments.php')) {
				include_once 'modules/ModComments/ModComments.php';
				if (class_exists('ModComments'))
					ModComments::addWidgetTo(array('Payments'));
			}
			$adb->pquery('UPDATE vtiger_tab SET customized=0 WHERE name=?', array($modulename));
			$tabid = vtlib\Functions::getModuleId($modulename);
			include_once('modules/ModTracker/ModTracker.php');
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

			$moduleInstance = vtlib\Module::getInstance('Accounts');
			$blockInstance = vtlib\Block::getInstance('LBL_ACCOUNT_INFORMATION', $moduleInstance);
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
		vimport('~~modules/com_vtiger_workflow/include.inc');
		vimport('~~modules/com_vtiger_workflow/tasks/VTEntityMethodTask.inc');
		vimport('~~modules/com_vtiger_workflow/VTEntityMethodManager.inc');
		$db = PearDatabase::getInstance();
		$functionName = 'UpdateBalance';
		$emm = new VTEntityMethodManager($db);
		$emm->addEntityMethod($moduleName, $functionName, "modules/PaymentsIn/workflow/UpdateBalance.php", $functionName);

		$workflowManager = new VTWorkflowManager($db);
		$taskManager = new VTTaskManager($db);

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
