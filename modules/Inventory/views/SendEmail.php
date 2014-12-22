<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Inventory_SendEmail_View extends Vtiger_ComposeEmail_View {

    /**
     * Function which will construct the compose email
     * This will handle the case of attaching the invoice pdf as attachment
     * @param Vtiger_Request $request 
     */
    public function composeMailData(Vtiger_Request $request) {
        parent::composeMailData($request);
        $viewer = $this->getViewer($request);
        $inventoryRecordId = $request->get('record');
        $recordModel = Vtiger_Record_Model::getInstanceById($inventoryRecordId, $request->getModule());
        $pdfFileName = $recordModel->getPDFFileName();
        
        $fileComponents = explode('/', $pdfFileName);
        
        $fileName = $fileComponents[count($fileComponents)-1];
        //remove the fileName
        array_pop($fileComponents);

        $attachmentDetails = array(array(
            'attachment' =>$fileName,
            'path' => implode('/',$fileComponents),
            'size' => filesize($pdfFileName),
            'type' => 'pdf',
            'nondeletable' => true
        ));

        $this->populateTo($request);
        $viewer->assign('ATTACHMENTS', $attachmentDetails);
        echo $viewer->view('ComposeEmailForm.tpl', 'Emails', true);
    }

    public function populateTo($request){
        $viewer = $this->getViewer($request);
        
        $inventoryRecordId = $request->get('record');
        $recordModel = Vtiger_Record_Model::getInstanceById($inventoryRecordId, $request->getModule());
        $inventoryModule = $recordModel->getModule();
        $inventotyfields = $inventoryModule->getFields();
        
        $toEmailConsiderableFields = array('contact_id','account_id','vendor_id');
        $db = PearDatabase::getInstance();
        $to = array();
        $to_info = array();
        $toMailNamesList = array();
        foreach($toEmailConsiderableFields as $fieldName){
            if(!array_key_exists($fieldName, $inventotyfields)){
                continue;
            }
            $fieldModel = $inventotyfields[$fieldName];
            if(!$fieldModel->isViewable()) {
                continue;
            }
            $fieldValue = $recordModel->get($fieldName);
            if(empty($fieldValue)) {
                continue;
            }
            $referenceList = $fieldModel->getReferenceList();
            $referenceModule = $referenceList[0];
            $fieldLabel = Vtiger_Util_Helper::getLabel($fieldValue);
            $referenceModuleModel = Vtiger_Module_Model::getInstance($referenceModule);
            $emailFields = $referenceModuleModel->getFieldsByType('email');
            if(count($emailFields) <= 0) {
                continue;
            }
            
            $current_user = Users_Record_Model::getCurrentUserModel();
            $queryGenerator = new QueryGenerator($referenceModule, $current_user);
            $queryGenerator->setFields(array_keys($emailFields));
            $query = $queryGenerator->getQuery();
            $query .= ' AND crmid = ' . $fieldValue;
            
            $result = $db->pquery($query, array());
            $num_rows = $db->num_rows($result);
            if($num_rows  <= 0) {
                continue;
            }
            foreach($emailFields as $fieldName => $emailFieldModel) {
                $emailValue = $db->query_result($result,0,$fieldName);
                if(!empty($emailValue)){
                    $to[] = $emailValue;
                    $to_info[$fieldValue][] = $emailValue;
                    $toMailNamesList[$fieldValue][] = array('label' => $fieldLabel, 'value' => $emailValue);
                    break;
                }
            }
            if(!empty($to)) {
                break;
            }
        }
        $viewer->assign('TO', $to);
        $viewer->assign('TOMAIL_NAMES_LIST', $toMailNamesList);
		$viewer->assign('TOMAIL_INFO', $to_info);
    }
    
}
