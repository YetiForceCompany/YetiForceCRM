<?php

/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_PickListDependency_Index_Action extends Settings_Vtiger_Basic_Action {
    
    function __construct() {
        parent::__construct();
        $this->exposeMethod('checkCyclicDependency');
    }
   
    public function checkCyclicDependency(Vtiger_Request $request) {
        $module = $request->get('sourceModule');
        $sourceField = $request->get('sourcefield');
        $targetField = $request->get('targetfield');
        $result = Vtiger_DependencyPicklist::checkCyclicDependency($module, $sourceField, $targetField);
        $response = new Vtiger_Response();
        $response->setResult(array('result'=>$result));
        $response->emit();
    }
}