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
function bind_prefix($user_id,$mail_detail,$folder,$ModuleName,$table_name,$table_col,$ossmailviewTab) {
	$adb = PearDatabase::getInstance();
	if($mail_detail['ossmailviewid'] == ''){
		$result_ossmailview = $adb->pquery( "SELECT ossmailviewid FROM vtiger_ossmailview where uid = ? AND rc_user = ? ", array($mail_detail['message_id'], $user_id) ,true);
		if( $adb->num_rows($result_ossmailview) == 0){return false;}
		$ossmailviewid = $adb->query_result($result_ossmailview, 0, 'ossmailviewid');
	}else{
		$ossmailviewid = $mail_detail['ossmailviewid'];
	}
	$result_ossmailview_contacts = $adb->pquery("SELECT * FROM vtiger_crmentityrel WHERE (crmid = ? AND relmodule = ?) OR ( relcrmid = ? AND module = ? )", array($ossmailviewid, $ModuleName, $ossmailviewid, $ModuleName) ,true);
	if( $adb->num_rows($result_ossmailview_contacts) > 0){return false;}
	
	require_once("modules/$ModuleName/$ModuleName.php");
	$ModuleObject = new $ModuleName();
	$table_index = $ModuleObject->table_index;
	$ModuleId = Vtiger_Functions::getModuleId($ModuleName);
	//$folder_group = OSSMailScanner_Record_Model::getConfigFolderList($folder);
	//$folder = OSSMailScanner_Record_Model::getTypeFolder($folder_group);
	$EmailNumPrefix = OSSMailScanner_Record_Model::findEmailNumPrefix($ModuleName,$mail_detail['subject']);
	if(!$EmailNumPrefix){return false;}
	$return_id = Array();
	$result = $adb->pquery( "SELECT $table_index FROM ".$table_name." INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = ".$table_name.".".$table_index." WHERE vtiger_crmentity.deleted = 0  AND ".$table_col." = ? ", array($EmailNumPrefix) ,true);
	if( $adb->num_rows($result) > 0){
		$crmid = $adb->query_result($result, 0, 0);
		$adb->pquery("INSERT INTO vtiger_crmentityrel SET crmid=?, module=?, relcrmid=?, relmodule=?",Array($ossmailviewid,'OSSMailView', $crmid, $ModuleName));
		$return_id[]=$crmid;
	}
	return $return_id;
}