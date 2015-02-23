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
class OSSTimeControl_Calendar_Action extends Vtiger_Action_Controller {

	function checkPermission(Vtiger_Request $request) {
		return;
	}

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$id = $request->get( 'id' ); 
		$sourceModule = $request->get( 'sourceModule' ); 
        $sourceData = array();
        
		$record = OSSTimeControl_Calendar_Model::getInstance();
		$record->set('user', $request->get('user'));
		if ($request->get('start') && $request->get('end')) {
			$record->set('start', $request->get('start'));
			$record->set('end', $request->get('end'));
		}
		$entity = $record->getEntity();
   
		$response = new Vtiger_Response();
		$response->setResult($entity);
		$response->emit();
	}
}