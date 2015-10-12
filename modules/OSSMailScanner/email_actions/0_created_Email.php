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

function _0_created_Email($user_id, $mailDetail, $folder, $return)
{
	$type = OSSMailScanner_Record_Model::getTypeEmail($mailDetail);
	$folder_group = OSSMailScanner_Record_Model::getConfigFolderList($folder);
	$exceptionsAll = OSSMailScanner_Record_Model::getConfig('exceptions');
	$adb = PearDatabase::getInstance();
	$result_user_id = $adb->pquery('SELECT crm_user_id FROM roundcube_users where user_id = ? ', [$user_id]);
	$assigned_user_id = $adb->query_result($result_user_id, 0, 'crm_user_id');
	$result = $adb->pquery('SELECT ossmailviewid FROM vtiger_ossmailview where uid = ? AND rc_user = ? ', [$mailDetail['message_id'], $user_id]);

	if ($type == 0) {
		$mailForExceptions = $mailDetail['toaddress'];
	} else {
		$mailForExceptions = $mailDetail['fromaddress'];
	}

	if (!empty($exceptionsAll['crating_mails'])) {
		$exceptions = explode(',', $exceptionsAll['crating_mails']);
		foreach ($exceptions as $exception) {
			if (strpos($mailForExceptions, $exception) !== FALSE) {
				return ['created_Email' => ''];
			}
		}
	}
	if ($adb->num_rows($result) == 0 && $mailDetail['message_id'] != '') {
		$OSSMailViewInstance = CRMEntity::getInstance('OSSMailView');
		$OSSMailViewInstance->column_fields['assigned_user_id'] = $assigned_user_id;
		$OSSMailViewInstance->column_fields['subject'] = $mailDetail['subject'];
		$OSSMailViewInstance->column_fields['to_email'] = $mailDetail['toaddress'];
		$OSSMailViewInstance->column_fields['from_email'] = $mailDetail['fromaddress'];
		$OSSMailViewInstance->column_fields['reply_to_email'] = $mailDetail['reply_toaddress'];
		$OSSMailViewInstance->column_fields['ossmailview_sendtype'] = OSSMailScanner_Record_Model::getTypeEmail($mailDetail, true);
		$OSSMailViewInstance->column_fields['content'] = $mailDetail['body'];
		$OSSMailViewInstance->column_fields['orginal_mail'] = $mailDetail['clean'];
		$OSSMailViewInstance->column_fields['cc_email'] = $mailDetail['ccaddress'];
		$OSSMailViewInstance->column_fields['bcc_email'] = $mailDetail['bccaddress'];
		$OSSMailViewInstance->column_fields['fromaddress'] = $mailDetail['from'];
		$OSSMailViewInstance->column_fields['uid'] = $mailDetail['message_id'];
		$OSSMailViewInstance->column_fields['id'] = $mailDetail['id'];
		$OSSMailViewInstance->column_fields['mbox'] = $folder;
		$OSSMailViewInstance->column_fields['type'] = $type;
		$OSSMailViewInstance->column_fields['rc_user'] = $user_id;
		$adress = [];
		if ($mailDetail['fromaddress']) {
			$adress[] = $mailDetail['fromaddress'];
		}
		if ($mailDetail['reply_toaddress']) {
			$adress[] = $mailDetail['reply_toaddress'];
		}
		$OSSMailViewInstance->column_fields['from_id'] = OSSMailScanner_Record_Model::findEmail(implode(',', $adress), false, false);
		$adress = [];
		if ($mailDetail['toaddress']) {
			$adress[] = $mailDetail['toaddress'];
		}
		if ($mailDetail['ccaddress']) {
			$adress[] = $mailDetail['ccaddress'];
		}
		if ($mailDetail['bccaddress']) {
			$adress[] = $mailDetail['bccaddress'];
		}
		$OSSMailViewInstance->column_fields['to_id'] = OSSMailScanner_Record_Model::findEmail(implode(',', $adress), false, false);

		if (count($mailDetail['attachments']) > 0) {
			$OSSMailViewInstance->column_fields['attachments_exist'] = 1;
		}
		$OSSMailViewInstance->save('OSSMailView');
		$id = $OSSMailViewInstance->id;

		$DocumentsIDs = OSSMail_Record_Model::_SaveAttachements($mailDetail['attachments'], $assigned_user_id, $mailDetail['udate_formated'], $id);
		$adb->pquery('UPDATE vtiger_crmentity SET smcreatorid = ?,modifiedby = ? WHERE crmid = ? ', [$assigned_user_id, $assigned_user_id, $id]);
		$adb->pquery('UPDATE vtiger_ossmailview SET date = ? WHERE ossmailviewid = ?;', [$mailDetail['udate_formated'], $id]);
	}
	return ['created_Email' => $id];
}
