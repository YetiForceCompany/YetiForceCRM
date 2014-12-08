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
class OSSMenuManager_SaveIcon_Action extends Vtiger_Action_Controller {
	function checkPermission(Vtiger_Request $request) {
		return;
	}
	public function process(Vtiger_Request $request) {
		$moduleName     = $request->getModule();
		$paintedicon       = $request->get( 'paintedicon' );
        $recordModel    = Vtiger_Record_Model::getCleanInstance($moduleName);
        
		$save = $recordModel->updateIcon($paintedicon );
		if (!$save)
            $result = array('success'=>false, 'return'=>vtranslate('MSG_CHANGE_ICON_ERROR', $moduleName) );
        else 
            $result = array('success'=>true, 'return'=>vtranslate('MSG_CHANGE_ICON', $moduleName) );
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}