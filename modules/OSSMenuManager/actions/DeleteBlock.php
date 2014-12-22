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
class OSSMenuManager_DeleteBlock_Action extends Vtiger_Action_Controller {

	function checkPermission(Vtiger_Request $request) {
		return;
	}

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
        
        $id = $request->get( 'id' );
        $recordModel = Vtiger_Record_Model::getCleanInstance( $moduleName );
        
        // sprawdzić czy ma jakieś pozycje menu w sobie
        $db = PearDatabase::getInstance();
        $sql = "SELECT COUNT(1) as nr FROM `vtiger_ossmenumanager` WHERE `parent_id` = ?;";
        $params = array( intval($id) );
        $result = $db->pquery( $sql, $params, true );
        $num = $db->query_result( $result, 0, 'nr' );
        
        if (  $num == 0 ) 
            $deleted = $recordModel->deleteBlock( $id );
        else 
            $deleted = false;
        
        if ( $deleted == true )
            $result = array('success'=>true, 'return'=>vtranslate('MSG_DELETEBLOCK_OK', $moduleName) );
        else
            $result = array('success'=>false, 'return'=>vtranslate('MSG_DELETEBLOCK_ERROR', $moduleName) );        
        
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
?>