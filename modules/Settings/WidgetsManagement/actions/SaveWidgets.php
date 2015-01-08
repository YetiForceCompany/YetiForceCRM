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
 
class Settings_WidgetsManagement_SaveWidgets_Action extends Vtiger_Action_Controller {

	function checkPermission(Vtiger_Request $request) {
		return;
	}

	public function process(Vtiger_Request $request) {
		$adb = PearDatabase::getInstance();
		$moduleName = $request->getModule(false);
		$widgetsToRole = $request->get( 'widgetsToRole' ); 
		$oldWidgetsToRole = $request->get( 'oldWidgetsToRole' ); 
		
		if(!is_array($widgetsToRole))
			$widgetsToRole = array();
		if(!is_array($oldWidgetsToRole))
			$oldWidgetsToRole = array();
		$overlap = $request->get( 'overlap' ); 
		if($overlap == 'mandatory'){
			$change = Settings_WidgetsManagement_Module_Model::setMandatoryWidgets($widgetsToRole, $oldWidgetsToRole);
		} elseif($overlap == 'inactive'){
			$change = Settings_WidgetsManagement_Module_Model::setInactiveWidgets($widgetsToRole, $oldWidgetsToRole);
		}

		if ( !$change ) {
			$result = array( 'success' => false, 'message' => vtranslate('LBL_FAILED_TO_SAVE', $moduleName) );
		}else
			$result = array( 'success' => true,  'message' => vtranslate('LBL_SAVE_CHANGE', $moduleName) );

        
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}