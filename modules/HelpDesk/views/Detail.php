<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class HelpDesk_Detail_View extends Vtiger_Detail_View {
	
	function __construct() {
		parent::__construct();
		$this->exposeMethod('showRelatedRecords');
		$this->exposeMethod('showCharts');
	}

	function showCharts(Vtiger_Request $request) {
	
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		$viewer = $this->getViewer($request);
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$data = $moduleModel->getTimeEmployee($recordId);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		//echo '<pre>' ; print_r($data); echo '</pre>';
		$viewer->view('charts/ShowTimeHelpDesk.tpl', $moduleName);
	}

}
