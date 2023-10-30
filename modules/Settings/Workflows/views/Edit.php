<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class Settings_Workflows_Edit_View extends Settings_Vtiger_Index_View
{
	public function process(App\Request $request)
	{
		$mode = $request->getMode();
		if ($mode) {
			$this->{$mode}($request);
		} else {
			$this->step1($request);
		}
	}

	public function preProcess(App\Request $request, $display = true)
	{
		parent::preProcess($request);
		$viewer = $this->getViewer($request);
		$recordId = !$request->isEmpty('record') ? $request->getInteger('record') : '';
		if ($recordId) {
			$workflowModel = Settings_Workflows_Record_Model::getInstance($recordId);
			$viewer->assign('WORKFLOW_MODEL', $workflowModel);
		}
		$viewer->assign('RECORDID', $recordId);
		$viewer->assign('RECORD_MODE', $request->getMode());
		$viewer->view('EditHeader.tpl', $request->getModule(false));
	}

	public function step1(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$weekDays = ['Sunday' => 0, 'Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6];
		if (!$request->isEmpty('record')) {
			$recordId = $request->getInteger('record');
			$workflowModel = Settings_Workflows_Record_Model::getInstance($recordId);
			$viewer->assign('RECORDID', $recordId);
			$viewer->assign('MODULE_MODEL', $workflowModel->getModule());
			$viewer->assign('MODE', 'edit');
		} else {
			$workflowModel = Settings_Workflows_Record_Model::getCleanInstance($moduleName);
			$selectedModule = $request->getByType('source_module', 2);
			if (!empty($selectedModule)) {
				$viewer->assign('SELECTED_MODULE', $selectedModule);
			}
		}
		$viewer->assign('WORKFLOW_MODEL', $workflowModel);
		$viewer->assign('ALL_MODULES', Settings_Workflows_Module_Model::getSupportedModules());
		$viewer->assign('TRIGGER_TYPES', Settings_Workflows_Module_Model::getTriggerTypes());

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$admin = Users::getActiveAdminUser();
		$viewer->assign('ACTIVE_ADMIN', $admin);
		$viewer->assign('WEEK_START_ID', $weekDays[\App\User::getCurrentUserModel()->getDetail('dayoftheweek')]);
		$viewer->view('Step1.tpl', $qualifiedModuleName);
	}

	public function step2(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		if (!$request->isEmpty('record')) {
			$workFlowModel = Settings_Workflows_Record_Model::getInstance($request->getInteger('record'));
			$selectedModule = $workFlowModel->getModule();
			$selectedModuleName = $selectedModule->getName();
		} else {
			$selectedModuleName = $request->getByType('module_name', 2);
			$selectedModule = Vtiger_Module_Model::getInstance($selectedModuleName);
			$workFlowModel = Settings_Workflows_Record_Model::getCleanInstance($selectedModuleName);
		}

		foreach (['summary', 'schdayofweek', 'schdayofmonth', 'execution_condition', 'schtypeid', 'schtime', 'schdate', 'schannualdates', 'params', 'filtersavedinnew', 'record'] as $name) {
			if ($request->has($name)) {
				switch ($name) {
					case 'summary':
						$value = htmlspecialchars($request->getByType($name, 'Text'));
						break;
					case 'schdayofweek':
					case 'schdayofmonth':
						$value = $request->getArray($name, 'Integer');
						$value = empty($value) ? null : $value;
						break;
					case 'record':
					case 'filtersavedinnew':
					case 'schtypeid':
						$value = $request->isEmpty($name) ? null : $request->getInteger($name);
						break;
					case 'execution_condition':
						$value = $request->getInteger($name);
						break;
					case 'schtime':
						$value = $request->isEmpty($name) ? null : $request->getByType($name, 'TimeInUserFormat');
						break;
					case 'schdate':
						$value = $request->isEmpty($name) ? null : $request->getByType($name, 'dateTimeInUserFormat');
						break;
					case 'schannualdates':
						$value = $request->isEmpty($name) ? null : implode(',', $request->getExploded($name, ',', 'DateInUserFormat'));
						break;
					case 'params':
						$value = $request->getMultiDimensionArray($name, [
							'iterationOff' => \App\Purifier::BOOL,
							'showTasks' => \App\Purifier::BOOL,
							'enableTasks' => \App\Purifier::BOOL,
						]
						);
						$value = \App\Json::encode($value);
						break;
					default:
						$value = null;
				}
				$workFlowModel->set($name, $value);
			}
		}

		//Added to support advance filters
		$recordStructureInstance = Settings_Workflows_RecordStructure_Model::getInstanceForWorkFlowModule($workFlowModel, Settings_Workflows_RecordStructure_Model::RECORD_STRUCTURE_MODE_FILTER);
		$recordStructure = $recordStructureInstance->getStructure();
		$viewer->assign('RECORD_STRUCTURE', $recordStructure);
		$viewer->assign('WORKFLOW_MODEL', $workFlowModel);
		$viewer->assign('MODULE_MODEL', $selectedModule);
		$viewer->assign('SELECTED_MODULE_NAME', $selectedModuleName);
		$viewer->assign('DATE_FILTERS', Vtiger_AdvancedFilter_Helper::getDateFilter($qualifiedModuleName));
		$viewer->assign('ADVANCED_FILTER_OPTIONS', Settings_Workflows_Field_Model::getAdvancedFilterOptions());
		$viewer->assign('ADVANCED_FILTER_OPTIONS_BY_TYPE', Settings_Workflows_Field_Model::getAdvancedFilterOpsByFieldType());
		$viewer->assign('COLUMNNAME_API', 'getWorkFlowFilterColumnName');
		$viewer->assign('FIELD_EXPRESSIONS', Settings_Workflows_Module_Model::getExpressions());

		// Added to show filters only when saved from vtiger6
		if ($workFlowModel->isFilterSavedInNew()) {
			$viewer->assign('ADVANCE_CRITERIA', $workFlowModel->transformToAdvancedFilterCondition());
		} else {
			$viewer->assign('ADVANCE_CRITERIA', '');
		}

		$viewer->assign('IS_FILTER_SAVED_NEW', $workFlowModel->isFilterSavedInNew());
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('SKIPPED_FIELD_DATA_TYPES', ['smtp']);

		$viewer->view('Step2.tpl', $qualifiedModuleName);
	}

	public function step3(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$recordId = !$request->isEmpty('record') ? $request->getInteger('record') : '';
		if ($recordId) {
			$workFlowModel = Settings_Workflows_Record_Model::getInstance($recordId);
			$selectedModule = $workFlowModel->getModule();
			$selectedModuleName = $selectedModule->getName();
		} else {
			$selectedModuleName = $request->getByType('module_name', 2);
			$workFlowModel = Settings_Workflows_Record_Model::getCleanInstance($selectedModuleName);
		}
		$viewer->assign('TASK_RECORDS', $workFlowModel->getTaskTypes());
		$viewer->assign('SOURCE_MODULE', $selectedModuleName);
		$viewer->assign('RECORD', $recordId);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('WORKFLOW_MODEL', $workFlowModel);
		$viewer->assign('TASK_LIST', $workFlowModel->getTasks(false));
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('Step3.tpl', $qualifiedModuleName);
	}

	public function getFooterScripts(App\Request $request)
	{
		$moduleName = $request->getModule();
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'libraries.clipboard.dist.clipboard',
			'modules.Settings.Vtiger.resources.Edit',
			"modules.Settings.$moduleName.resources.Edit",
			"modules.Settings.$moduleName.resources.Edit1",
			"modules.Settings.$moduleName.resources.Edit2",
			"modules.Settings.$moduleName.resources.Edit3",
			"modules.Settings.$moduleName.resources.AdvanceFilter",
			'~vendor/ckeditor/ckeditor/ckeditor.js',
			'~vendor/ckeditor/ckeditor/adapters/jquery.js',
		]));
	}
}
