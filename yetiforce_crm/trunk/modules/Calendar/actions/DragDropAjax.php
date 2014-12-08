<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_DragDropAjax_Action extends Calendar_SaveAjax_Action {
    
    function __construct() {
		$this->exposeMethod('updateDeltaOnResize');
        $this->exposeMethod('updateDeltaOnDrop');
	}
    
    public function process(Vtiger_Request $request) {  
		$mode = $request->getMode();
		if(!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}

	}
    
    public function updateDeltaOnResize(Vtiger_Request $request){
        $moduleName = $request->getModule();
        $activityType = $request->get('activitytype');
        $recordId = $request->get('id');
        $dayDelta = $request->get('dayDelta');
        $minuteDelta = $request->get('minuteDelta');
        $actionname = 'EditView';
        
        $response = new Vtiger_Response();
        if(isPermitted($moduleName, $actionname, $recordId) === 'no'){
            $result = array('ispermitted'=>false,'error'=>false);
            $response->setResult($result);
            $response->emit();
        }
        else{
            $result = array('ispermitted'=>true,'error'=>false);
            $record = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
            $record->set('mode','edit');
            
            $oldDateTime[] = $record->get('due_date');
            $oldDateTime[] = $record->get('time_end');
            $oldDateTime = implode(' ',$oldDateTime);
            $resultDateTime = $this->changeDateTime($oldDateTime,$dayDelta,$minuteDelta);
            $parts = explode(' ',$resultDateTime);
            $record->set('due_date',$parts[0]);
            if(activitytype != 'Task')
                $record->set('time_end',$parts[1]);

            $startDateTime[] = $record->get('date_start');
            $startDateTime[] = $record->get('time_start');
            $startDateTime = implode(' ',$startDateTime);
            $startDateTime = new DateTime($startDateTime);
            
            $endDateTime[] = $record->get('due_date');
            $endDateTime[] = $record->get('time_end');
            $endDateTime = implode(' ',$endDateTime);
            $endDateTime = new DateTime($endDateTime);
            //Checking if startDateTime is less than or equal to endDateTime
            if($startDateTime <= $endDateTime)
                $record->save();
            else
                $result['error'] = true;

            $response->setResult($result);
            $response->emit();
        }
    }
    
    public function updateDeltaOnDrop(Vtiger_Request $request){
        $moduleName = $request->getModule();
        $activityType = $request->get('activitytype');
        $recordId = $request->get('id');
        $dayDelta = $request->get('dayDelta');
        $minuteDelta = $request->get('minuteDelta');
        $actionname = 'EditView';
        
        $response = new Vtiger_Response();
        if(isPermitted($moduleName, $actionname, $recordId) === 'no'){
            $result = array('ispermitted'=>false);          
            $response->setResult($result);
            $response->emit();
        }
        else{
            $result = array('ispermitted'=>true);
            $record = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
            $record->set('mode','edit');

            $oldStartDateTime[] = $record->get('date_start');
            $oldStartDateTime[] = $record->get('time_start');
            $oldStartDateTime = implode(' ',$oldStartDateTime);
            $resultDateTime = $this->changeDateTime($oldStartDateTime,$dayDelta,$minuteDelta);
            $parts = explode(' ',$resultDateTime);
            $record->set('date_start',$parts[0]);
            $record->set('time_start',$parts[1]);

            $oldEndDateTime[] = $record->get('due_date');
            $oldEndDateTime[] = $record->get('time_end');
            $oldEndDateTime = implode(' ',$oldEndDateTime);
            $resultDateTime = $this->changeDateTime($oldEndDateTime,$dayDelta,$minuteDelta);
            $parts = explode(' ',$resultDateTime);
            $record->set('due_date',$parts[0]);
            if(activitytype != 'Task')
                $record->set('time_end',$parts[1]);  
            $record->save();
            
            $response->setResult($result);
            $response->emit();
        }
    }
    /* *
     * Function adds days and minutes to datetime string
     */
    public function changeDateTime($datetime,$daysToAdd,$minutesToAdd){
        $datetime = strtotime($datetime);
        $futureDate = $datetime+(60*$minutesToAdd)+(24*60*60*$daysToAdd);
        $formatDate = date("Y-m-d H:i:s", $futureDate);
        return $formatDate;
    }
    
}
?>
