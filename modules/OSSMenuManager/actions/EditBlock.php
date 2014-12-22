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
class OSSMenuManager_EditBlock_Action extends Vtiger_Action_Controller {

	function checkPermission(Vtiger_Request $request) {
		return;
	}

	public function process(Vtiger_Request $request) {
		$adb = PearDatabase::getInstance();
		$moduleName = $request->getModule();
        $recordModel = Vtiger_Record_Model::getCleanInstance( $moduleName );
        
        $id         = $request->get( 'id' );
        $name       = $request->get( 'name' );
        $visible    = intval( $request->get( 'visible' ) );
        $permission = $request->get( 'permission' );
		$locationicon = $request->get( 'locationicon' );
		$sizeicon = $request->get( 'sizeicon' );
		$langfield = $request->get( 'langfield' );
        
		
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
		$ifExist = $adb->query( "SELECT * FROM vtiger_ossmenumanager WHERE parent_id = 0", true );
		$num = $adb->num_rows( $ifExist );
		for($i=0; $i<$num; $i++){
			$tab[] = $adb->raw_query_result_rowdata( $ifExist, $i);
			if($name == $tab[$i]['label'] && $id != $adb->query_result( $ifExist, $i, 'id' )){
				$num=-1;
				break;
			}elseif($name == vtranslate($adb->query_result( $ifExist, $i, 'label' ),$moduleName) && $id != $adb->query_result( $ifExist, $i, 'id' )){
				$num=-1;
				break;
			}
		}
		if ($status==true && !($num==-1))
			{
				$params = array(
				'id'            => $id,
				'name'          => $name,
				'visible'       => $visible,
				'permission'    => $permission,
				'locationicon'	=> $locationicon,
				'sizeicon'		=> $sizeicon,
				'langfield'		=> $langfield			
				);
			
			$edited = $recordModel->editBlock( $params );
		
			}
		
		
		
        
        if ($num==-1)
            $result = array('success'=>false, 'return'=>vtranslate('MSG_NAME_ALREADY_EXIST', $moduleName) );
        elseif ( $edited )
            $result = array('success'=>true, 'return'=>vtranslate('MSG_EDITBLOCK_OK', $moduleName) );
        else
            $result = array('success'=>false, 'return'=>vtranslate('MSG_EDITBLOCK_ERROR', $moduleName) );
       // echo '<pre>';print_r($params['langfield']);echo '</pre>';exit
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
?>