<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

function bind_email($user_id, $mail_detail, $folder, $moduleName, $ossmailviewTab)
{
	$adb = PearDatabase::getInstance();

	if ($mail_detail['ossmailviewid'] == '') {
		$result_ossmailview = $adb->pquery('SELECT ossmailviewid FROM vtiger_ossmailview WHERE uid = ? AND rc_user = ?', [$mail_detail['message_id'], $user_id]);
		if ($adb->num_rows($result_ossmailview) == 0) {
			return FALSE;
		}
		$ossmailviewid = $adb->query_result($result_ossmailview, 0, 'ossmailviewid');
	} else {
		$ossmailviewid = $mail_detail['ossmailviewid'];
	}

	$crmids_fromaddress = OSSMailScanner_Record_Model::findEmail($mail_detail['fromaddress'], $moduleName, TRUE);
	$crmids_toaddress = OSSMailScanner_Record_Model::findEmail($mail_detail['toaddress'], $moduleName, TRUE);
	$crmids_ccaddress = OSSMailScanner_Record_Model::findEmail($mail_detail['ccaddress'], $moduleName, TRUE);
	$crmids_bccaddress = OSSMailScanner_Record_Model::findEmail($mail_detail['bccaddress'], $moduleName, TRUE);
	$crmids_to = OSSMailScanner_Record_Model::_merge_array($crmids_toaddress, $crmids_ccaddress);
	$crmids_to = OSSMailScanner_Record_Model::_merge_array($crmids_to, $crmids_bccaddress);
	$return_ids = [];

	if (count($crmids_fromaddress) != 0) {
		foreach ($crmids_fromaddress as $crmidsRow) {
			$adb->pquery('INSERT INTO vtiger_ossmailview_relation SET ossmailviewid=?, crmid=?, date=?;', [$ossmailviewid, $crmidsRow[0], $mail_detail['udate_formated']]);
			$return_ids[] = $crmidsRow[0];
		}
	}
	if (count($crmids_to) != 0) {
		foreach ($crmids_to as $crmidsRow) {
			$adb->pquery('INSERT INTO vtiger_ossmailview_relation SET ossmailviewid=?, crmid=?, date=?;', [$ossmailviewid, $crmidsRow[0], $mail_detail['udate_formated']]);
			$return_ids[] = $crmidsRow[0];
		}
	}
	return $return_ids;
}
