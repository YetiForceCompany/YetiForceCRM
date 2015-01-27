<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 ************************************************************************************/
class Settings_LayoutEditor_Relation_Action extends Settings_Vtiger_Index_Action {
	function __construct() {
		$this->exposeMethod('changeStatusRelation');
		$this->exposeMethod('updateSequenceRelatedModule');
		$this->exposeMethod('updateSelectedFields');
	}
	
	public function changeStatusRelation(Vtiger_Request $request) {
		$relationId = $request->get('relationId');
		$status = $request->get('status');
		$response = new Vtiger_Response();
		try{
			Vtiger_Relation_Model::updateRelationPresence($relationId, $status);
			$response->setResult(array('success'=> true));
		}
		catch(Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}
	
	public function updateSequenceRelatedModule(Vtiger_Request $request) {
		$modules = $request->get('modules');
		$response = new Vtiger_Response();
		try{
			Vtiger_Relation_Model::updateRelationSequence($modules);
			$response->setResult(array('success'=> true));
		}
		catch(Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}
	
	public function updateSelectedFields(Vtiger_Request $request) {
		$fields = $request->get('fields');
		$relationId = $request->get('relationId');
		$response = new Vtiger_Response();
		try{
			Vtiger_Relation_Model::updateModuleRelatedFields($relationId,$fields);
			$response->setResult(array('success'=> true));
		}
		catch(Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
		$response->emit();
	}
	
    public function validateRequest(Vtiger_Request $request) { 
        $request->validateWriteAccess(); 
    } 
}