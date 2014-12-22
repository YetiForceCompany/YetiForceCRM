<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_NoteBook_Action extends Vtiger_Action_Controller {
	
	function __construct() {
		$this->exposeMethod('NoteBookCreate');
	}
	
	function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		
		if($mode){
			$this->invokeExposedMethod($mode,$request);
		}
	}
	
	function NoteBookCreate(Vtiger_Request $request){
		$adb = PearDatabase::getInstance();
		
		$userModel = Users_Record_Model::getCurrentUserModel();
		$linkId = $request->get('linkId');
		$noteBookName = $request->get('notePadName');
		$noteBookContent = $request->get('notePadContent');
		
		$date_var = date("Y-m-d H:i:s");
		$date = $adb->formatDate($date_var, true);
		
		$dataValue = array();
		$dataValue['contents'] = $noteBookContent;
		$dataValue['lastSavedOn'] = $date;
		
		$data = Zend_Json::encode((object) $dataValue);

		$query="INSERT INTO vtiger_module_dashboard_widgets(linkid, userid, filterid, title, data) VALUES(?,?,?,?,?)";
		$params= array($linkId,$userModel->getId(),0,$noteBookName,$data);
		$adb->pquery($query, $params);
		$id = $adb->getLastInsertID();
		
		$result = array();
		$result['success'] = TRUE;
		$result['widgetId'] = $id;
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
		
	}
        
        public function validateRequest(Vtiger_Request $request) { 
            $request->validateWriteAccess(); 
        }
}
