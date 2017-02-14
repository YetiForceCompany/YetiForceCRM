<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Reports_Detail_View extends Vtiger_Index_View
{

	protected $reportData;
	protected $calculationFields;
	protected $count;

	public function checkPermission(Vtiger_Request $request)
	{
		$record = $request->get('record');
		$reportModel = Reports_Record_Model::getCleanInstance($record);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($request->getModule()) && !$reportModel->isEditable()) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	const REPORT_LIMIT = 1000;

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		parent::preProcess($request);

		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$page = $request->get('page');

		$detailViewModel = Reports_DetailView_Model::getInstance($moduleName, $recordId);
		$reportModel = $detailViewModel->getRecord();
		$reportModel->setModule('Reports');

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $page);
		$pagingModel->set('limit', self::REPORT_LIMIT);

		$reportData = $reportModel->getReportData($pagingModel);
		$this->reportData = $reportData['data'];
		$this->calculationFields = $reportModel->getReportCalulationData();

		$count = $reportData['count'];
		if ($count < 1000) {
			$this->count = $count;
		} else {
			$query = $reportModel->getReportSQL(false, 'PDF');
			$countQuery = $reportModel->generateCountQuery($query);
			$this->count = $reportModel->getReportsCount($countQuery);
		}

		$primaryModule = $reportModel->getPrimaryModule();
		$secondaryModules = $reportModel->getSecondaryModules();

		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userPrivilegesModel = Users_Privileges_Model::getInstanceById($currentUser->getId());
		$permission = $userPrivilegesModel->hasModulePermission($primaryModule);

		if (!$permission) {
			$viewer->assign('MODULE', $primaryModule);
			$viewer->assign('MESSAGE', 'LBL_PERMISSION_DENIED');
			$viewer->view('OperationNotPermitted.tpl', $primaryModule);
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}

		$detailViewLinks = $detailViewModel->getDetailViewLinks();

		// Advanced filter conditions
		$viewer->assign('SELECTED_ADVANCED_FILTER_FIELDS', $reportModel->transformToNewAdvancedFilter());
		$viewer->assign('PRIMARY_MODULE', $primaryModule);

		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($reportModel);
		$primaryModuleRecordStructure = $recordStructureInstance->getPrimaryModuleRecordStructure();
		$secondaryModuleRecordStructures = $recordStructureInstance->getSecondaryModuleRecordStructure();

		if ($primaryModule == 'HelpDesk') {
			foreach ($primaryModuleRecordStructure as $blockLabel => $blockFields) {
				foreach ($blockFields as $field => $object) {
					if ($field == 'update_log') {
						unset($primaryModuleRecordStructure[$blockLabel][$field]);
					}
				}
			}
		}
		if (!empty($secondaryModuleRecordStructures)) {
			foreach ($secondaryModuleRecordStructures as $module => $structure) {
				if ($module == 'HelpDesk') {
					foreach ($structure as $blockLabel => $blockFields) {
						foreach ($blockFields as $field => $object) {
							if ($field == 'update_log') {
								unset($secondaryModuleRecordStructures[$module][$blockLabel][$field]);
							}
						}
					}
				}
			}
		}
		// End

		$viewer->assign('PRIMARY_MODULE_RECORD_STRUCTURE', $primaryModuleRecordStructure);
		$viewer->assign('SECONDARY_MODULE_RECORD_STRUCTURES', $secondaryModuleRecordStructures);

		$secondaryModuleIsCalendar = strpos($secondaryModules, 'Calendar');
		if (($primaryModule == 'Calendar') || ($secondaryModuleIsCalendar !== false)) {
			$advanceFilterOpsByFieldType = Calendar_Field_Model::getAdvancedFilterOpsByFieldType();
		} else {
			$advanceFilterOpsByFieldType = Vtiger_Field_Model::getAdvancedFilterOpsByFieldType();
		}
		$viewer->assign('ADVANCED_FILTER_OPTIONS', \App\CustomView::ADVANCED_FILTER_OPTIONS);
		$viewer->assign('ADVANCED_FILTER_OPTIONS_BY_TYPE', $advanceFilterOpsByFieldType);
		$viewer->assign('DATE_FILTERS', Vtiger_AdvancedFilter_Helper::getDateFilter($module));
		$viewer->assign('LINEITEM_FIELD_IN_CALCULATION', $reportModel->showLineItemFieldsInFilter(false));
		$viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);
		$viewer->assign('REPORT_MODEL', $reportModel);
		$viewer->assign('RECORD_ID', $recordId);
		$viewer->assign('COUNT', $this->count);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('ReportHeader.tpl', $moduleName);
	}

	public function process(Vtiger_Request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		echo $this->getReport($request);
	}

	public function getReport(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$record = $request->get('record');
		$page = $request->get('page');

		$data = $this->reportData;
		$calculation = $this->calculationFields;

		if (empty($data)) {
			$reportModel = Reports_Record_Model::getInstanceById($record);
			$reportModel->setModule('Reports');
			$reportType = $reportModel->get('reporttype');

			$pagingModel = new Vtiger_Paging_Model();
			$pagingModel->set('page', $page);
			$pagingModel->set('limit', self::REPORT_LIMIT + 1);

			$reportData = $reportModel->getReportData($pagingModel);
			$data = $reportData['data'];
			$calculation = $reportModel->getReportCalulationData();

			$advFilterSql = $reportModel->getAdvancedFilterSQL();
			$query = $reportModel->getReportSQL($advFilterSql, 'PDF');
			$countQuery = $reportModel->generateCountQuery($query);
			$this->count = $reportModel->getReportsCount($countQuery);
		}

		$viewer->assign('CALCULATION_FIELDS', $calculation);
		$viewer->assign('DATA', $data);
		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('COUNT', $this->count);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('REPORT_RUN_INSTANCE', ReportRun::getInstance($record));
		if (count($data) > self::REPORT_LIMIT) {
			$viewer->assign('LIMIT_EXCEEDED', true);
		}

		$viewer->view('ReportContents.tpl', $moduleName);
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(Vtiger_Request $request)
	{
		$headerScriptInstances = parent::getFooterScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			'modules.Vtiger.resources.Detail',
			"modules.$moduleName.resources.Detail"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}
