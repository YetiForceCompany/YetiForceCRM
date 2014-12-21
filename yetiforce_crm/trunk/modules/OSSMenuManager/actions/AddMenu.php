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
class OSSMenuManager_AddMenu_Action extends Vtiger_Action_Controller {

	function checkPermission(Vtiger_Request $request) {
		return;
	}

	public function process(Vtiger_Request $request) {
		$adb = PearDatabase::getInstance();
		
	
		$moduleName = $request->getModule();
        $recordModel = Vtiger_Record_Model::getCleanInstance( $moduleName );
        
        $blockId        = $request->get( 'blockId' );
        $type           = $request->get( 'type' );
        $tabId          = $request->get( 'tabId' );
        $label          = $request->get( 'label' );
        $sequence       = $request->get( 'sequence' );
        $visible        = $request->get( 'visible' );
        $url            = $request->get( 'url' );
        $newWindow      = $request->get( 'newWindow' );
        $permissions    = $request->get( 'permissions' );
		$locationicon	= $request->get( 'locationicon' );
		$sizeicon 		= $request->get( 'sizeicon' );
		$langfield 		= $request->get( 'langfield' );
        
        $typeId = false;        
        switch( $type ) {
            case 'module':      $typeId = 0; break;
            case 'shortcut':    $typeId = 1; break;
            case 'label':       $typeId = 2; break;
            case 'separator':   $typeId = 3; break;
            case 'script':      $typeId = 4; break;
        }
        
		
		
		$status=true;
		if(	$langfield ){
				$res = explode('#',$langfield);
				for ($i=0; count($res)>$i; $i++){
					$prefix=substr( $res[$i], 0, strpos($res[$i], "*") );
					$value=substr( $res[$i], 6 );
					if(preg_match('/^([a-z]{2})_([a-z]{2})$/', $prefix) && !preg_match('/[*#]/', $value)){
					}else {
					$status=false;
					break;}
				}
			}
			
		$tab=array();	
		$ifExist = $adb->query( "SELECT label, tabid FROM vtiger_ossmenumanager WHERE parent_id = $blockId", true );
		$num = $adb->num_rows( $ifExist );
		$alert = 'MSG_NAME_ALREADY_EXIST';
		for($i=0; $i<$num; $i++){
			$tab[] = $adb->raw_query_result_rowdata( $ifExist, $i);
			if($type == 'separator' ){
				$num=0;
				break;
			}elseif($label == $tab[$i]['label'] ){
				$num=-1;
				if($tabId == $adb->query_result( $ifExist, $i, 'tabid' ))
					$alert='MSG_BLOCK_ALREADY_EXIST';
				break;
			}elseif($label == vtranslate($adb->query_result( $ifExist, $i, 'label' ),$moduleName)){
				$num=-1;
				if($tabId == $adb->query_result( $ifExist, $i, 'tabid' ))
				break;
			}
			
		}
		if ($status==true && !($num==-1))	{
			$param = array(
				'parent_id'     => $blockId,
				'tabid'         => $tabId,
				'label'         => $label,
				'sequence'      => $sequence,
				'visible'       => $visible,
				'type'          => $typeId,
				'url'           => $url,
				'new_window'    => $newWindow,
				'permission'    => $permissions,
				'locationicon'	=> $locationicon,
				'sizeicon'		=> $sizeicon,
				'langfield'		=> $langfield
			);
        
			$insertId = $recordModel->addMenu( $param );
        }
		
		
		
        if ($num==-1)
            $result = array('success'=>false, 'return'=>vtranslate($alert, $moduleName) );
        else if ( $insertId > 0 )
			$result = array('success'=>true, 'return'=>vtranslate('MSG_ADDMENUITEM_OK', $moduleName) );
		else
            $result = array('success'=>false, 'return'=>vtranslate('MSG_ADDMENUITEM_ERROR', $moduleName) );
        
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
?>