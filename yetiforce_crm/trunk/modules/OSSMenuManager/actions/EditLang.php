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
class OSSMenuManager_EditLang_Action extends Vtiger_Action_Controller {

	function checkPermission(Vtiger_Request $request) {
		return;
	}

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
        $recordModel = Vtiger_Record_Model::getCleanInstance( $moduleName );
        
        $id         = $request->get( 'id' );
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
		
		if ($status==true)
			{
			$params = array(
			'id'            => $id,
			'langfield'		=> $langfield			
			);
        
			$edited = $recordModel->editLang( $params );
		}	
		
        if ( $edited )
            $result = array('success'=>true, 'return'=>vtranslate('MSG_EDITMENUITEM_OK', $moduleName) );
        else
            $result = array('success'=>false, 'return'=>vtranslate('MSG_EDITMENUITEM_ERROR', $moduleName) );
        
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
?>