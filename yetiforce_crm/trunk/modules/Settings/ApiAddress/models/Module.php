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

class Settings_ApiAddress_Module_Model extends Settings_Vtiger_Module_Model {
	
	public function getConfig($panel = false){
		global $log;
		$db = PearDatabase::getInstance();
		$log->debug("Entering Settings_ApiAddress_Module_Model::getConfig(".$panel.") method ...");
		
		$sql = "SELECT * FROM `vtiger_apiaddress`;";
		$result = $db->query( $sql, true );
		
		$rawData = array();
		
		if ( $db->num_rows( $result ) > 0 )
			$rawData = $db->query_result_rowdata($result, 0);
			
		$log->debug("Exiting Settings_ApiAddress_Module_Model::getConfig() method ...");
		// check permission
		if(!$panel && $rawData['nominatim'] == 0)
			return false;
		return $rawData;
	}
	
	public function setConfig($elements){
		global $log;
		$db = PearDatabase::getInstance();
		$log->debug("Entering Settings_ApiAddress_Module_Model::setConfig() method ...");
	
        $sql = "UPDATE `vtiger_apiaddress` SET ";
		$i=1;
		$params = array();
		foreach($elements as $name=>$value){
			$sql .= '`'.$name.'` = ?';
			if($i<count($elements))
				$sql .= ', ';
			$i++;
			$params[] = $value ;
		}
        $result = $db->pquery( $sql, $params, true );
		$log->debug("Exiting Settings_ApiAddress_Module_Model::setConfig() method ...");
        return $result;;
	}
}