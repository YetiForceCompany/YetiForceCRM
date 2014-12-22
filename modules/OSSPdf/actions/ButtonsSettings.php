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
class OSSPdf_ButtonsSettings_Action extends Vtiger_Action_Controller {

	function checkPermission(Vtiger_Request $request) {
		return;
	}

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$buttonsReturn=false;
		$mode=$request->get('mode');
		$formodule=$request->get('formodule');
		$recordModel = Vtiger_Module_Model::getCleanInstance('OSSPdf');
		if ($mode == 'create_buttons') {
            $recordModel->add_links($formodule);
			  $buttonsReturn=true;
        }
		if ($mode == 'delete_buttons') {
			$modCommentsModule = Vtiger_Module::getInstance($formodule);
            $modCommentsModule->deleteLink('LISTVIEWSIDEBARWIDGET', 'Pdf');
            $modCommentsModule->deleteLink('DETAILVIEWSIDEBARWIDGET', 'Pdf');
            $modCommentsModule->deleteLink('DETAILVIEWBASIC', 'LBL_QUICK_GENERATE_MAIL'); // do ewentualnej poprawy
            $modCommentsModule->deleteLink('DETAILVIEWBASIC', 'Generate default PDF');
			$buttonsReturn=true;
        }

	
		if ( $buttonsReturn === false ) {
			$result = array( 'success' => false, 'message' => vtranslate('Error', $moduleName) );
		}
        else {
			$result = array( 'success' => true, 'message' => 'Ok' );
		}
        
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}