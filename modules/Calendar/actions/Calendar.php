<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
class Calendar_Calendar_Action extends Vtiger_BasicAjax_Action {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('getEvents');
		$this->exposeMethod('updateEvent');
	}
	
	public function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
		}
	}

	public function getEvents(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$id = $request->get( 'id' ); 

		$record = Calendar_Calendar_Model::getInstance();
		$record->set('user', $request->get('user'));
		$record->set('types', $request->get('types'));
		if ($request->get('start') && $request->get('end')) {
			$record->set('start', $request->get('start'));
			$record->set('end', $request->get('end'));
		}
		$entity = $record->getEntity();
   
		$response = new Vtiger_Response();
		$response->setResult($entity);
		$response->emit();
	}
	
	public function updateEvent(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('id');
		$actionname = 'EditView';
        if(isPermitted($moduleName, $actionname, $recordId) === 'no'){
            $succes = false;
        }else{
			$date_start = date('Y-m-d', strtotime($request->get('start')) );
			$time_start = date('H:i:s', strtotime($request->get('start')) );
			$due_date = date('Y-m-d', strtotime($request->get('end')) );
			$time_end = date('H:i:s', strtotime($request->get('end')) );
			$succes = false;
			if (!empty($recordId)) {
				try {
					$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
					$recordModel->set('id', $recordId);
					$recordModel->set('mode', 'edit');
					$recordModel->set('date_start', $date_start);
					$recordModel->set('time_start', $time_start);
					$recordModel->set('due_date', $due_date);
					if($recordModel->get('activitytype') != 'Task'){
						$recordModel->set('time_end', $time_end);
					}
					$recordModel->save();
					$succes = true;
				} catch (Exception $e) {
					$succes = false;
				}
			}
		}
		$response = new Vtiger_Response();
		$response->setResult($succes);
		$response->emit();
	}

}