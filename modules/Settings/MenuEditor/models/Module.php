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
	public function getModulesColors($active = false) {
		global $adb;
		$sql_params = Array();
		$sql = '';
		if($active){
			$sql = 'WHERE coloractive = ?';
			$sql_params[] = 1;
		}
		$result = $adb->pquery( "SELECT * FROM vtiger_tab $sql;", $sql_params);
        $rows = $adb->num_rows($result);
		$modules = Array();
        for($i=0; $i<$rows; $i++){
			$row = $adb->query_result_rowdata($result, $i);
			$modules[] = array(
				'id'=> $row['tabid'], 
				'module'=> $row['name'], 
				'color' => $row['color'] != ''?'#'.$row['color']:'',
				'active' => $row['coloractive'],
			);
        }
		return $modules;
	}
	
	public function updateColor($params){
		$adb = PearDatabase::getInstance();
		$color = str_replace("#","",$params['color']);
		$adb->pquery('UPDATE vtiger_tab SET color = ? WHERE tabid = ?;', array( $color, $params['id'] ));
	}

	public function activeColor($params){
		$adb = PearDatabase::getInstance();
		$sql_params = Array();
		$sql = '';
		if( $params['color'] == ''){
			$color = self::getColor();
			$sql = ' color = ?,';
			$sql_params[] = $color;
		}
		$sql_params[] = $params['status'] == 'true'?1:0;
		$sql_params[] = $params['id'];
		$adb->pquery("UPDATE vtiger_tab SET $sql coloractive = ? WHERE tabid = ?;", $sql_params);
		return $color;
	}
	
	public function getColor(){
		$colors = array('990033','ff3366','cc0033','ff0033','ff9999','cc3366','ffccff','cc3399','993366','660033','cc3399','ff99cc','ff66cc','ff99ff','ff6699','cc0066','ff0066','ff3399','ff0099','ff33cc','ff00cc','ff66ff','ff33ff','Fuchsia','cc0099','990066','cc66cc','cc33cc','cc99ff','cc66ff','cc33ff','993399','cc00cc','cc00ff','9900cc','990099','cc99cc','996699','663366','660099','9933cc','660066','9900ff','9966cc','330033','663399','6633cc','6600cc','9966ff','330066','6600ff','6633ff','ccccff','9999ff','9999cc','6666cc','6666ff','666699','333366','333399','330099','3300cc','3300ff','3333ff','3333cc','0066ff','0033ff','3366ff','3366cc','000066','000033','Blue','000099','0033cc','0000cc','336699','0066cc','99ccff','6699ff','003366','6699cc','006699','3399cc','0099cc','66ccff','3399ff','003399','0099ff','33ccff','00ccff','99ffff','66ffff','33ffff','Aqua','00cccc','009999','669999','99cccc','ccffff','33cccc','66cccc','339999','336666','006666','003333','00ffcc','33ffcc','33cc99','00cc99','66ffcc','99ffcc','00ff99','339966','006633','336633','669966','66cc66','99ff99','66ff66','339933','99cc99','66ff99','33ff99','33cc66','00cc66','66cc99','009966','009933','33ff66','00ff66','ccffcc','ccff99','99ff66','00ff33','33ff33','00cc33','33cc33','66ff33','Lime','66cc33','006600','003300','009900','33ff00','66ff00','99ff00','66cc00','00cc00','33cc00','339900','99cc66','669933','99cc33','336600','669900','99cc00','ccff66','ccff33','ccff00','999900','cccc00','cccc33','333300','666600','999933','cccc66','666633','999966','cccc99','ffffcc','ffff99','ffff33','Yellow','ffcc00','ffcc66','ffcc33','cc9933','996600','cc9900','ff9900','cc6600','993300','cc6633','663300','ff9966','ff6633','ff9933','ff6600','cc3300','996633','330000','663333','996666','cc9999','993333','cc6666','ffcccc','ff3333','cc3333','ff6666','660000','990000','cc0000','Red','ff3300','cc9966','ffcc99');
		return $colors[array_rand($colors)];
	}
}