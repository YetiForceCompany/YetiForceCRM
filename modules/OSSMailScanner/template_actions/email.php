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
function bind_email($user_id,$mail_detail,$folder,$ModuleName,$ossmailviewTab) {
	$adb = PearDatabase::getInstance();
	if($mail_detail['ossmailviewid'] == ''){
		$result_ossmailview = $adb->pquery( "SELECT ossmailviewid FROM vtiger_ossmailview where uid = ? AND rc_user = ? ", array($mail_detail['message_id'], $user_id) ,true);
		if( $adb->num_rows($result_ossmailview) == 0){return false;}
		$ossmailviewid = $adb->query_result($result_ossmailview, 0, 'ossmailviewid');
	}else{
		$ossmailviewid = $mail_detail['ossmailviewid'];
	}
	$result_ossmailview_contacts = $adb->pquery("SELECT * FROM vtiger_crmentityrel WHERE (crmid = ? AND relmodule = ?) OR ( relcrmid = ? AND module = ? )", 
            array($ossmailviewid, $ModuleName, $ossmailviewid, $ModuleName) ,true);
	if( $adb->num_rows($result_ossmailview_contacts) > 0){return false;}
	
	//$folder_group = OSSMailScanner_Record_Model::getConfigFolderList($folder);
	//$folder = OSSMailScanner_Record_Model::getTypeFolder($folder_group);
	$crmids_fromaddress = OSSMailScanner_Record_Model::findEmail($mail_detail['fromaddress'], $ModuleName,true);
	$crmids_toaddress = OSSMailScanner_Record_Model::findEmail($mail_detail['toaddress'], $ModuleName,true);
	$crmids_ccaddress = OSSMailScanner_Record_Model::findEmail($mail_detail['ccaddress'], $ModuleName,true);
	$crmids_bccaddress = OSSMailScanner_Record_Model::findEmail($mail_detail['bccaddress'], $ModuleName,true);
	$crmids_to = OSSMailScanner_Record_Model::_merge_array($crmids_toaddress,$crmids_ccaddress);
	$crmids_to = OSSMailScanner_Record_Model::_merge_array($crmids_to,$crmids_bccaddress);
	$return_ids = Array();
	if( count( $crmids_fromaddress ) != 0 ){
		foreach($crmids_fromaddress as $crmids_row){
			$adb->pquery("INSERT INTO vtiger_crmentityrel SET crmid=?, module=?, relcrmid=?, relmodule=?",
			Array($ossmailviewid,'OSSMailView', $crmids_row[0], $ModuleName));
			$return_ids[]=$crmids_row[0];
		}
	}
	if( count( $crmids_to ) != 0 ){
		foreach($crmids_to as $crmids_row){
			$adb->pquery("INSERT INTO vtiger_crmentityrel SET crmid=?, module=?, relcrmid=?, relmodule=?",
			Array($ossmailviewid,'OSSMailView', $crmids_row[0], $ModuleName));
			$return_ids[]=$crmids_row[0];
		}
	}
	return $return_ids;
}