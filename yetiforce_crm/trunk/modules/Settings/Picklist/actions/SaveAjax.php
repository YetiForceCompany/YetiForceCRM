<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Picklist_SaveAjax_Action extends Settings_Vtiger_Basic_Action {
    
    function __construct() {
        $this->exposeMethod('add');
        $this->exposeMethod('rename');
        $this->exposeMethod('remove');
        $this->exposeMethod('assignValueToRole');
        $this->exposeMethod('saveOrder');
        $this->exposeMethod('enableOrDisable');
    }

    public function process(Vtiger_Request $request) {
        $mode = $request->get('mode');
        $this->invokeExposedMethod($mode, $request);
    }
    
    /*
     * @function updates user tables with new picklist value for default event and status fields
     */
    public function updateDefaultPicklistValues($pickListFieldName,$oldValue,$newValue) {
        $db = PearDatabase::getInstance();            
            if($pickListFieldName == 'activitytype')
                $defaultFieldName = 'defaultactivitytype';
            else
                $defaultFieldName = 'defaulteventstatus';
            $queryToGetId = 'SELECT id FROM vtiger_users WHERE '.$defaultFieldName.' IN (';
             if(is_array($oldValue)) {
                 for($i=0;$i<count($oldValue);$i++) {
                     $queryToGetId .= '"'.$oldValue[$i].'"';
                     if($i<(count($oldValue)-1)) {
                         $queryToGetId .= ',';
                     }
                 }
                 $queryToGetId .= ')';
             }
             else {
                 $queryToGetId .= '"'.$oldValue.'")';
             }
            $result = $db->pquery($queryToGetId, array());
            $rowCount =  $db->num_rows($result);
            for($i=0; $i<$rowCount; $i++) {
                $recordId = $db->query_result_rowdata($result, $i);
                $recordId = $recordId['id'];
                $record = Vtiger_Record_Model::getInstanceById($recordId, 'Users');
                $record->set('mode','edit');
                $record->set($defaultFieldName,$newValue);
                $record->save();
            }
    }
    
    public function add(Vtiger_Request $request) {
        $newValue = $request->getRaw('newValue');
        $pickListName = $request->get('picklistName');
        $moduleName = $request->get('source_module');
        $moduleModel = Settings_Picklist_Module_Model::getInstance($moduleName);
        $fieldModel = Settings_Picklist_Field_Model::getInstance($pickListName, $moduleModel);
        $rolesSelected = array();
        if($fieldModel->isRoleBased()) {
            $userSelectedRoles = $request->get('rolesSelected',array());
            //selected all roles option
            if(in_array('all',$userSelectedRoles)) {
                $roleRecordList = Settings_Roles_Record_Model::getAll();
                foreach($roleRecordList as $roleRecord) {
                    $rolesSelected[] = $roleRecord->getId();
                }
            }else{
                $rolesSelected = $userSelectedRoles;
            }
        }
        $response = new Vtiger_Response();
        try{
            $id = $moduleModel->addPickListValues($fieldModel, $newValue, $rolesSelected);
            $response->setResult(array('id' => $id['id']));
        }  catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }
    
    public function rename(Vtiger_Request $request) {
        $moduleName = $request->get('source_module');
        
        $newValue = $request->getRaw('newValue');
        $pickListFieldName = $request->get('picklistName');
        $oldValue = $request->getRaw('oldValue');
		$id = $request->getRaw('id');
        
        if($moduleName == 'Events' && ($pickListFieldName == 'activitytype' || $pickListFieldName == 'eventstatus')) {
             $this->updateDefaultPicklistValues($pickListFieldName,$oldValue,$newValue);
        }   
        $moduleModel = new Settings_Picklist_Module_Model();
        $response = new Vtiger_Response();
        try{
            $status = $moduleModel->renamePickListValues($pickListFieldName, $oldValue, $newValue, $moduleName, $id);
            $response->setResult(array('success',$status));
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }
    
    public function remove(Vtiger_Request $request) {
        $moduleName = $request->get('source_module');
        $valueToDelete = $request->getRaw('delete_value');
        $replaceValue = $request->getRaw('replace_value');
        $pickListFieldName = $request->get('picklistName');
        
        if($moduleName == 'Events' && ($pickListFieldName == 'activitytype' || $pickListFieldName == 'eventstatus')) {
             $this->updateDefaultPicklistValues($pickListFieldName,$valueToDelete,$replaceValue);
        } 
        $moduleModel = Settings_Picklist_Module_Model::getInstance($moduleName);
        $response = new Vtiger_Response();
        try{
            $status = $moduleModel->remove($pickListFieldName, $valueToDelete, $replaceValue, $moduleName);
            $response->setResult(array('success',$status));
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }

    /**
     * Function which will assign existing values to the roles
     * @param Vtiger_Request $request
     */
    public function assignValueToRole(Vtiger_Request $request) {
        $pickListFieldName = $request->get('picklistName');
        $valueToAssign = $request->getRaw('assign_values');
        $userSelectedRoles = $request->get('rolesSelected');
        
        $roleIdList = array();
        //selected all roles option
        if(in_array('all',$userSelectedRoles)) {
            $roleRecordList = Settings_Roles_Record_Model::getAll();
            foreach($roleRecordList as $roleRecord) {
                $roleIdList[] = $roleRecord->getId();
            }
        }else{
            $roleIdList = $userSelectedRoles;
        }
        
        $moduleModel = new Settings_Picklist_Module_Model();
        
        $response = new Vtiger_Response();
        try{
            $moduleModel->enableOrDisableValuesForRole($pickListFieldName, $valueToAssign, array(),$roleIdList);
            $response->setResult(array('success',true));
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }
    
    public function saveOrder(Vtiger_Request $request) {
        $pickListFieldName = $request->get('picklistName');
        $picklistValues = $request->getRaw('picklistValues');
        
        $moduleModel = new Settings_Picklist_Module_Model();
        $response = new Vtiger_Response();
        try{
            $moduleModel->updateSequence($pickListFieldName, $picklistValues);
            $response->setResult(array('success',true));
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }
    
    public function enableOrDisable(Vtiger_Request $request) {
        $pickListFieldName = $request->get('picklistName');
        $enabledValues = $request->getRaw('enabled_values',array());
        $disabledValues = $request->getRaw('disabled_values',array());
        $roleSelected = $request->get('rolesSelected');
        
        $moduleModel = new Settings_Picklist_Module_Model();
		$response = new Vtiger_Response();
        try{
            $moduleModel->enableOrDisableValuesForRole($pickListFieldName, $enabledValues, $disabledValues,array($roleSelected));
            $response->setResult(array('success',true));
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }
         
    public function validateRequest(Vtiger_Request $request) { 
        $request->validateWriteAccess(); 
    } 
}
