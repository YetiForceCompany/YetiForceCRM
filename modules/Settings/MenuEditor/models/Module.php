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

class Settings_MenuEditor_Module_Model extends Settings_Vtiger_Module_Model {
	public function getModulesColors() {
		global $adb;
		$result = $adb->pquery( "SELECT vtiger_ossmenumanager.id, vtiger_ossmenumanager.tabid, vtiger_ossmenumanager.color, vtiger_tab.name FROM vtiger_ossmenumanager LEFT JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_ossmenumanager.tabid WHERE vtiger_ossmenumanager.tabid <> ? AND vtiger_tab.name <> '' ORDER BY vtiger_ossmenumanager.tabid ASC;", array( 0 ), true );
        $rows = $adb->num_rows($result);
		$modules = Array();
        for($i=0; $i<$rows; $i++){
			$row = $adb->query_result_rowdata($result, $i);
			$modules[] = array(
				'id'=>$row['id'], 
				'module'=>$row['name'], 
				'color' => '#'.$row['color']
			);
        }
		return $modules;
	}
	
	public static function updateColor($params){
		$adb = PearDatabase::getInstance();
		$color = str_replace("#","",$params['color']);
		$adb->pquery('UPDATE vtiger_ossmenumanager SET color = ? WHERE id = ?;', array( $color, $params['id'] ));
	}
}
