<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_GetData_Action extends Vtiger_IndexAjax_View {

	public function process(Vtiger_Request $request) {
		$record = $request->get('record');
		$sourceModule = $request->get('source_module');
		$response = new Vtiger_Response();

		$permitted = Users_Privileges_Model::isPermitted($sourceModule, 'DetailView', $record);
		if($permitted) {
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $sourceModule);
			$data = $recordModel->getData();
			$response->setResult(array('success'=>true, 'data'=>array_map('decode_html',$data)));
		} else {
			$response->setResult(array('success'=>false, 'message'=>vtranslate('LBL_PERMISSION_DENIED')));
		}
		$response->emit();
	}
}
