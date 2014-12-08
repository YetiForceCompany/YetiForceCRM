<?php

/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Currency_SaveAjax_Action extends Settings_Vtiger_Basic_Action {
    
    public function process(Vtiger_Request $request) {
        
        $record = $request->get('record');
        if(empty($record)) {
			//get instance from currency name, Aleady deleted and adding again same currency case 
            $recordModel = Settings_Currency_Record_Model::getInstance($request->get('currency_name'));
            if(empty($recordModel)) {
				$recordModel = new Settings_Currency_Record_Model();
			}
		} else {
            $recordModel = Settings_Currency_Record_Model::getInstance($record);
        }
        
        $fieldList = array('currency_name','conversion_rate','currency_status','currency_code','currency_symbol');
        
        foreach ($fieldList as $fieldName) {
            if($request->has($fieldName)) {
                $recordModel->set($fieldName,$request->get($fieldName));
            }
        }
		//To make sure we are saving record as non deleted. This is useful if we are adding deleted currency
		$recordModel->set('deleted',0);
        $response = new Vtiger_Response();
        try{
            if($request->get('currency_status') == 'Inactive' && !empty($record)) {
                $transforCurrencyToId = $request->get('transform_to_id');
                if(empty($transforCurrencyToId)) {
                    throw new Exception('Transfer currency id cannot be empty');
                }
                Settings_Currency_Module_Model::tranformCurrency($record, $transforCurrencyToId);
            }
            $id = $recordModel->save();
            $recordModel = Settings_Currency_Record_Model::getInstance($id);
            $response->setResult(array_merge($recordModel->getData(),array('record'=> $recordModel->getId())));
        }catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }
    
    public function validateRequest(Vtiger_Request $request) { 
        $request->validateWriteAccess(); 
    } 
}