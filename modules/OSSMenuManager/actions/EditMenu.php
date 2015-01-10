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
class OSSMenuManager_EditMenu_Action extends Vtiger_Action_Controller {

	function checkPermission(Vtiger_Request $request) {
		return;
	}

	public function process(Vtiger_Request $request) {
		$adb = PearDatabase::getInstance();
		$moduleName = $request->getModule();
        $recordModel = Vtiger_Record_Model::getCleanInstance( $moduleName );
                
        $id             = $request->get( 'id' );
        $tabId          = $request->get( 'tabId' );
        $label          = $request->get( 'label' );
        $visible        = $request->get( 'visible' );
        $url            = $request->get( 'url' );
        $newWindow      = $request->get( 'newWindow' );
        $permissions    = $request->get( 'permission' );
		$locationicon   = $request->get( 'locationicon');
		$sizeicon		= $request->get( 'sizeicon');
		$parentId 		= $request->get( 'parent_id');
		
        $tab=array();
        $ifExist = $adb->query( "SELECT label, id FROM vtiger_ossmenumanager WHERE parent_id = $parentId", true );
		$num = $adb->num_rows( $ifExist );
		for($i=0; $i<$num; $i++){
			$tab[] = $adb->raw_query_result_rowdata( $ifExist, $i);
			if($label == vtranslate($adb->query_result( $ifExist, $i, 'label' ),$moduleName) && $id!=$adb->query_result( $ifExist, $i, 'id' )){
				$num=0;
				break;
			} elseif ($label == $tab[$i]['label'] && $id!=$adb->query_result( $ifExist, $i, 'id' )){
				$num=0;
				break;
			}
		}
		if(!($num==0)){
			$param = array(
				'id'            => $id,
				'tabid'         => $tabId,
				'label'         => $label,
				'sequence'      => $sequence,
				'visible'       => $visible,
				'url'           => $url,
				'new_window'    => $newWindow,
				'permission'    => $permissions,
				'locationicon'	=> $locationicon,
				'sizeicon'		=> $sizeicon,
			);
        
			$updated = $recordModel->editMenu( $param );
        }
		
		if ($num==0)
            $result = array('success'=>false, 'return'=>vtranslate('MSG_NAME_ALREADY_EXIST', $moduleName) );
        elseif ( $updated == true )
            $result = array('success'=>true, 'return'=>vtranslate('MSG_EDITMENUITEM_OK', $moduleName) );
        else
            $result = array('success'=>false, 'return'=>vtranslate('MSG_EDITMENUITEM_ERROR', $moduleName) );
        
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}