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
function _0_created_Email($user_id, $mail_detail, $folder, $return) {
	$folder_group = OSSMailScanner_Record_Model::getConfigFolderList($folder);
	$adb = PearDatabase::getInstance();
	$result_user_id = $adb->pquery("SELECT crm_user_id FROM roundcube_users where user_id = ? ", [$user_id]);
	$assigned_user_id = $adb->query_result($result_user_id, 0, 'crm_user_id');
	$result = $adb->pquery("SELECT ossmailviewid FROM vtiger_ossmailview where uid = ? AND rc_user = ? ", [$mail_detail['message_id'], $user_id]);

	if ($adb->num_rows($result) == 0 && $mail_detail['message_id'] != '') {
		$OSSMailViewInstance = CRMEntity::getInstance('OSSMailView');
		$OSSMailViewInstance->column_fields['assigned_user_id'] = $assigned_user_id;
		$OSSMailViewInstance->column_fields['subject'] = $mail_detail['subject'];
		$OSSMailViewInstance->column_fields['to_email'] = $mail_detail['toaddress'];
		$OSSMailViewInstance->column_fields['from_email'] = $mail_detail['fromaddress'];
		$OSSMailViewInstance->column_fields['reply_to_email'] = $mail_detail['reply_toaddress'];
		$OSSMailViewInstance->column_fields['ossmailview_sendtype'] = OSSMailScanner_Record_Model::getTypeEmail($mail_detail, true);
		$OSSMailViewInstance->column_fields['content'] = $mail_detail['body'];
		$OSSMailViewInstance->column_fields['orginal_mail'] = $mail_detail['clean'];
		$OSSMailViewInstance->column_fields['cc_email'] = $mail_detail['ccaddress'];
		$OSSMailViewInstance->column_fields['bcc_email'] = $mail_detail['bccaddress'];
		$OSSMailViewInstance->column_fields['fromaddress'] = $mail_detail['from'];
		$OSSMailViewInstance->column_fields['uid'] = $mail_detail['message_id'];
		$OSSMailViewInstance->column_fields['id'] = $mail_detail['id'];
		$OSSMailViewInstance->column_fields['mbox'] = $folder;
		$OSSMailViewInstance->column_fields['type'] = OSSMailScanner_Record_Model::getTypeEmail($mail_detail);
		$OSSMailViewInstance->column_fields['rc_user'] = $user_id;
		$OSSMailViewInstance->column_fields['from_id'] = OSSMailScanner_Record_Model::findEmail($mail_detail['fromaddress'], false, false);
		if ($mail_detail['toaddress']) {
			$adress = $mail_detail['toaddress'];
		}
		if ($mail_detail['ccaddress']) {
			if ($adress != '') {
				$adress .= ',';
			}
			$adress .= $mail_detail['ccaddress'];
		}
		if ($mail_detail['bccaddress']) {
			if ($adress != '') {
				$adress .= ',';
			}
			$adress .= $mail_detail['bccaddress'];
		}
		$OSSMailViewInstance->column_fields['to_id'] = OSSMailScanner_Record_Model::findEmail($adress, false, false);

		if (count($mail_detail['attachments']) > 0) {
			$OSSMailViewInstance->column_fields['attachments_exist'] = 1;
		}
		$OSSMailViewInstance->save('OSSMailView');
		$id = $OSSMailViewInstance->id;

		$DocumentsIDs = OSSMail_Record_Model::_SaveAttachements($mail_detail['attachments'], $assigned_user_id, $mail_detail['udate_formated'], $id);
		$adb->pquery('UPDATE vtiger_crmentity SET smcreatorid = ?,modifiedby = ? WHERE crmid = ? ', array($assigned_user_id, $assigned_user_id, $id));
		$adb->pquery('UPDATE vtiger_ossmailview SET date = ? WHERE ossmailviewid = ?;', array($mail_detail['udate_formated'], $id));
	}
	return ['created_Email' => $id];
}
