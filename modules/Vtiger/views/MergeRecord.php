<?php
/**************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 **************************************************************************************/

class Vtiger_MergeRecord_View extends Vtiger_Popup_View {
	function process(Vtiger_Request $request) {
		$records = $request->get('records');
		$records = explode(',', $records);
		$module = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		$fieldModels =  $moduleModel->getFields();

		foreach($records as $record) {
			$recordModels[] = Vtiger_Record_Model::getInstanceById($record);
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORDS', $records);
		$viewer->assign('RECORDMODELS', $recordModels);
		$viewer->assign('FIELDS', $fieldModels);
		$viewer->assign('MODULE', $module);
		$viewer->view('MergeRecords.tpl', $module);
	}
}
