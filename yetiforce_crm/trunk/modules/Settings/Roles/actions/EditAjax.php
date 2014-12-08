<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Class Settings_Roles_EditAjax_Action extends Settings_Vtiger_IndexAjax_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('checkDuplicate');
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	public function checkDuplicate(Vtiger_Request $request) {
		$roleName = $request->get('rolename');
		$recordId = $request->get('record');
		
		$recordModel = Settings_Roles_Record_Model::getInstanceByName($roleName, array($recordId));

		$response = new Vtiger_Response();
		if(!empty($recordModel)) {
			$response->setResult(array('success' => true,'message'=>  vtranslate('LBL_DUPLICATES_EXIST',$request->getModule(false))));
			
		}else{
			$response->setResult(array('success' => false));
		}
		$response->emit();
	}

}