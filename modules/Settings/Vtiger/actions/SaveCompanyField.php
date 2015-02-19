<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 ************************************************************************************/
class Settings_Vtiger_SaveCompanyField_Action extends Settings_Vtiger_Basic_Action {

	public function process(Vtiger_Request $request) {
		global $log;
		global $adb;
		$newField = mysql_escape_string($request->get('field_name'));
		$newField = str_replace(" ","_", $newField);
		$query = "SELECT * 
				FROM information_schema.COLUMNS 
				WHERE 
					TABLE_SCHEMA = ? 
				AND TABLE_NAME = 'vtiger_organizationdetails' 
				AND COLUMN_NAME = ?";
	
		$params = array($adb->dbName, $newField);
		$result = $adb->pquery($query, $params);	
		$rowsNum = $adb->getRowCount($result);			
				
		if($rowsNum > 0){
			$log->info("Settings_Vtiger_SaveCompanyField_Action::process - column $newField exist in table vtiger_organizationdetails");
			$reloadUrl = 'index.php?parent=Settings&module=Vtiger&view=CompanyDetails&AddField=0';
		}else{
			$alterFieldQuery = "ALTER TABLE `vtiger_organizationdetails` ADD $newField VARCHAR(60)";
			$alterFieldResult = $adb->query($alterFieldQuery, $alterFieldParams);
			$rowsNum = $adb->getRowCount($alterFieldResult);
			$this->addFieldToModule($newField);	
			$reloadUrl = 'index.php?parent=Settings&module=Vtiger&view=CompanyDetails&AddField=1';
			$log->info("Settings_Vtiger_SaveCompanyField_Action::process - add column $newField in table vtiger_organizationdetails");
		}		
		header('Location: ' . $reloadUrl);
	}

	
	public function addFieldToModule($field){
		global $log;
		$fileName = 'modules/Settings/Vtiger/models/CompanyDetails.php';
		$fileExists = file_exists($fileName);
		if($fileExists) {
			require_once($fileName);
			$fileContent = file_get_contents($fileName);
			$placeToAdd = "'website' => 'text',";
			$newField = "'$field' => 'text',";
			if(self::parse_data($placeToAdd,$fileContent)){
				$fileContent = str_replace($placeToAdd,$placeToAdd.PHP_EOL.'	'.$newField,$fileContent);
			}else{
				if(self::parse_data('?>',$fileContent)){
					$fileContent = str_replace('?>','',$fileContent);
				}
				$fileContent = $fileContent.PHP_EOL.$placeToAdd.PHP_EOL.'	'.$newField.PHP_EOL.');';
			}
			$log->info('Settings_Vtiger_SaveCompanyField_Action::addFieldToModule - add line to modules/Settings/Vtiger/models/CompanyDetails.php ');
		}else{
			$log->info('Settings_Vtiger_SaveCompanyField_Action::addFieldToModule - File does not exist');
			return FALSE;
		}
		
		$filePointer = fopen($fileName, 'w');
		fwrite($filePointer, $fileContent);
		fclose($filePointer);
		
		return TRUE;
	}	
		
	public function parse_data($a,$b) {
		$resp = false;
		if ($b != '' && strstr($b,$a) !== false) {
			$resp = true;
		}
		return $resp;
	}

}