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
	  
class Settings_Leads_ConvertToAccount_Model extends Vtiger_Base_Model{
	
	const tableName = 'vtiger_converttoaccount_settings';	

	/**
	 * Saves state data for convert to account
	 * @param <String> $state 
	 * @return array 
	 */
	public static function save($state) {		
		global $log;
		global $adb;
		$log->debug('Settings_Leads_ConvertToAccount_Model::save');			
		if('true' == $state){
			$state = TRUE;
		}else{
			$state = FALSE;
		}
		$query = 'SELECT * FROM '.self::tableName;
		$result = $adb->query($query);
				
		if($adb->num_rows($result) > 0) {
			$query = 'UPDATE '.self::tableName.' SET state = ?';
			$params = array($state);
		}else{
			$query = 'INSERT INTO '.self::tableName.' (state) VALUES(?)';
			$params = array($state);
		}
		
		$result = $adb->pquery($query, $params);
		
		return $result;
	}

	/**
	 * Gets state of convert to account state
	 * @return - string if data exist or FALSE
	 */
	public static function getState(){
		global $adb;
		$query = 'SELECT * FROM '.self::tableName;
		$result = $adb->query($query);	
		$return = FALSE;	

		if($adb->num_rows($result) > 0){
			$row = $adb->query_result_rowdata($result, 0);
			$return = $row['state'];
		}
		
		return $return;
	}
}