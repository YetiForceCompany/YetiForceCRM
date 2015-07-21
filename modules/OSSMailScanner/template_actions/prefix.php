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
function bind_prefix($user_id, $mail_detail, $folder, $moduleName, $table_name, $table_col, $ossmailviewTab) {
	$adb = PearDatabase::getInstance();
	if ($mail_detail['ossmailviewid'] == '') {
		$result_ossmailview = $adb->pquery("SELECT ossmailviewid FROM vtiger_ossmailview where uid = ? AND rc_user = ? ", [$mail_detail['message_id'], $user_id]);
		if ($adb->num_rows($result_ossmailview) == 0) {
			return FALSE;
		}
		$ossmailviewid = $adb->query_result($result_ossmailview, 0, 'ossmailviewid');
	} else {
		$ossmailviewid = $mail_detail['ossmailviewid'];
	}
	$relationExist = FALSE;
	$relationExistResult = $adb->pquery("SELECT crmid FROM vtiger_ossmailview_relation WHERE ossmailviewid = ?;", [$ossmailviewid]);
	for($i = 0; $i < $adb->num_rows($relationExistResult); $i++){
		$crmid = $adb->query_result_raw($relationExistResult, $i, 'crmid');
		$type = Vtiger_Functions::getCRMRecordType($crmid);
		if($type == $moduleName){
			$relationExist = TRUE;
		}
	}
	if ($relationExist) {
		return FALSE;
	}
	
	$emailNumPrefix = OSSMailScanner_Record_Model::findEmailNumPrefix($moduleName, $mail_detail['subject']);
	if (!$emailNumPrefix) {
		return FALSE;
	}
	
	require_once("modules/$moduleName/$moduleName.php");
	$moduleObject = new $moduleName();
	$table_index = $moduleObject->table_index;

	$return_id = [];
	$result = $adb->pquery("SELECT $table_index FROM " . $table_name . " INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = " . $table_name . "." . $table_index . " WHERE vtiger_crmentity.deleted = 0  AND " . $table_col . " = ? ", [$emailNumPrefix]);

	if ($adb->num_rows($result) > 0) {
		$crmid = $adb->query_result($result, 0, 0);
		$adb->pquery("INSERT INTO vtiger_ossmailview_relation SET ossmailviewid=?, crmid=?, date=?;", [$ossmailviewid, $crmid, $mail_detail['udate_formated']]);
		$return_id[] = $crmid;
	}
	return $return_id;
}
