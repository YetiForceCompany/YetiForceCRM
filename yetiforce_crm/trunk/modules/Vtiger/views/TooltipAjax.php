<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Vtiger_TooltipAjax_View extends Vtiger_PopupAjax_View {

	function preProcess(Vtiger_Request $request) {
		return true;
	}

	function postProcess(Vtiger_Request $request) {
		return true;
	}

	function process (Vtiger_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();

		$this->initializeListViewContents($request, $viewer);

		echo $viewer->view('TooltipContents.tpl', $moduleName, true);
	}
	
	public function initializeListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer) {
		$moduleName = $this->getModule($request);
		
		$recordId = $request->get('record');
		$tooltipViewModel = Vtiger_TooltipView_Model::getInstance($moduleName, $recordId);

		$viewer->assign('MODULE', $moduleName);

		$viewer->assign('MODULE_MODEL', $tooltipViewModel->getRecord()->getModule());
		
		$viewer->assign('TOOLTIP_FIELDS', $tooltipViewModel->getFields());
		$viewer->assign('RECORD', $tooltipViewModel->getRecord());
		$viewer->assign('RECORD_STRUCTURE', $tooltipViewModel->getStructure());

		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
	}

}