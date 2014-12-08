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
class OSSMenuManager_saveFields_Action extends Vtiger_Action_Controller {
	function checkPermission(Vtiger_Request $request) {
		return;
	}
	public function process(Vtiger_Request $request) {
		$moduleName     = $request->getModule();
		$group_id       = $request->get( 'group_id' );
        $newSequence    = $request->get( 'newSequence' );
        $recordModel    = Vtiger_Record_Model::getCleanInstance($moduleName);

        if ( intval($group_id) > 0 ) {
            $temp = array();
            $tempOther = array();
            foreach( $newSequence as $new ) {
                if ( $new['block'] == $group_id )
                    $temp[] = $new;
                else
                    $tempOther[] = $new;
            }
            
            $sorter = array();
            $ret = array();
            reset($temp);
            foreach ($temp as $k => $val) {
                $sorter[$k] = $val['sequence'];
            }
            if ($highToLow) {
                arsort($sorter);
            } else {
                asort($sorter);
            }
            foreach ($sorter as $k => $val) {
                $ret[$k] = $temp[$k];
            }
            $temp = $ret;
            
            $newSequence = array_merge( $temp, $tempOther );
        }
        else {
            $index=1;
            $indexes = array();
            for ( $i=0; $i<count($newSequence); $i++ ) {
                if ( $newSequence[$i]['block'] == $group_id ) {
                    $newSequence[$i]['sequence'] = $index;
                    echo $newSequence[$i]['block'].' '.$newSequence[$i]['fieldId'].' '.$newSequence[$i]['sequence'].'<br>';
                    $index++;
                }
            }
        }
        
		$Result = $recordModel->updateFields($newSequence );
		$response = new Vtiger_Response();
		$response->setResult($Result);
		$response->emit();
	}
}