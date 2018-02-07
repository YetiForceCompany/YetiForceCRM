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

class Reports_ExportReport_View extends \App\Controller\View
{

	use \App\Controller\ExposeMethod,
	 App\Controller\ClearProcess;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getPrintReport');
		$this->exposeMethod('getXls');
		$this->exposeMethod('getCsv');
	}

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		if (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModuleActionPermission($request->getModule(), 'Export')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Function exports the report in a Excel sheet
	 * @param \App\Request $request
	 */
	public function getXls(\App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$reportModel = Reports_Record_Model::getInstanceById($recordId);
		$reportModel->set('advancedFilter', $request->get('advanced_filter'));
		$reportModel->getReportXLS();
	}

	/**
	 * Function exports report in a CSV file
	 * @param \App\Request $request
	 */
	public function getCsv(\App\Request $request)
	{
		$recordId = $request->getInteger('record');
		$reportModel = Reports_Record_Model::getInstanceById($recordId);
		$reportModel->set('advancedFilter', $request->get('advanced_filter'));
		$reportModel->getReportCSV();
	}

	/**
	 * Function displays the report in printable format
	 * @param \App\Request $request
	 */
	public function getPrintReport(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$recordId = $request->getInteger('record');
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
