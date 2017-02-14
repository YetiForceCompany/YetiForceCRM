<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Reports_ExportReport_View extends Vtiger_View_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('GetPrintReport');
		$this->exposeMethod('GetXLS');
		$this->exposeMethod('GetCSV');
	}

	public function checkPermission(Vtiger_Request $request)
	{
		$record = $request->get('record');
		$reportModel = Reports_Record_Model::getCleanInstance($record);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($request->getModule())) {
			throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Preprocess
	 * @param Vtiger_Request $request
	 * @param boolean $display
	 * @return boolean
	 */
	public function preProcess(Vtiger_Request $request, $display = true)
	{
		return false;
	}

	public function postProcess(Vtiger_Request $request)
	{
		return false;
	}

	public function process(Vtiger_request $request)
	{
		$mode = $request->getMode();
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
		}
	}

	/**
	 * Function exports the report in a Excel sheet
	 * @param Vtiger_Request $request
	 */
	public function GetXLS(Vtiger_Request $request)
	{
		$recordId = $request->get('record');
		$reportModel = Reports_Record_Model::getInstanceById($recordId);
		$reportModel->set('advancedFilter', $request->get('advanced_filter'));
		$reportModel->getReportXLS();
	}

	/**
	 * Function exports report in a CSV file
	 * @param Vtiger_Request $request
	 */
	public function GetCSV(Vtiger_Request $request)
	{
		$recordId = $request->get('record');
		$reportModel = Reports_Record_Model::getInstanceById($recordId);
		$reportModel->set('advancedFilter', $request->get('advanced_filter'));
		$reportModel->getReportCSV();
	}

	/**
	 * Function displays the report in printable format
	 * @param Vtiger_Request $request
	 */
	public function GetPrintReport(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$recordId = $request->get('record');
		$reportModel = Reports_Record_Model::getInstanceById($recordId);
		$reportModel->set('advancedFilter', $request->get('advanced_filter'));
		$printData = $reportModel->getReportPrint();

		$viewer->assign('REPORT_NAME', $reportModel->getName());
		$viewer->assign('PRINT_DATA', $printData['data'][0]);
		$viewer->assign('TOTAL', $printData['total']);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('ROW', $printData['data'][1]);

		$viewer->view('PrintReport.tpl', $moduleName);
	}
}
