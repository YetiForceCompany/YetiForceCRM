<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
class VTUpdateClosedTime extends VTTask{
	public $executeImmediately = true; 
	
	public function getFieldNames(){return array();}
	public function doTask($entity){
		$entityIdDetails = vtws_getIdComponents($entity->getId());
		$entityId = $entityIdDetails[1];
		$adb = PearDatabase::getInstance();
		$adb->pquery("UPDATE vtiger_crmentity SET closedtime = ? WHERE crmid = ?",array(date("Y-m-d H:i:s"),$entityId) );
	}
}