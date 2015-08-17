<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class OSSMailView_Relation_Model extends Vtiger_Relation_Model
{

	public function addRelation($sourcerecordId, $destinationRecordId)
	{
		$adb = PearDatabase::getInstance();
		$recordModel = Vtiger_Record_Model::getInstanceById($sourcerecordId, 'OSSMailView');
		$date = $recordModel->get('date');
		$adb->pquery("INSERT INTO vtiger_ossmailview_relation SET ossmailviewid=?, crmid=?, date=?;", [$sourcerecordId, $destinationRecordId, $date]);
	}
}
