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

function _1_created_HelpDesk($user_id, $mailDetail, $folder, $return)
{
	$adb = PearDatabase::getInstance();
	$result_user_id = $adb->pquery("SELECT crm_user_id FROM roundcube_users where user_id = ? ", array($user_id), true);
	$assigned_user_id = $adb->query_result($result_user_id, 0, 'crm_user_id');
	$EmailNumPrefix = OSSMailScanner_Record_Model::findEmailNumPrefix('HelpDesk', $mailDetail['subject']);
	$result = $adb->pquery("SELECT ticketid FROM vtiger_troubletickets where ticket_no = ? ", array($EmailNumPrefix), true);
	$exceptionsAll = OSSMailScanner_Record_Model::getConfig('exceptions');

	if (!empty($exceptionsAll['crating_tickets'])) {
		$exceptions = explode(',', $exceptionsAll['crating_tickets']);
		foreach ($exceptions as $exception) {
			if (strpos($mailDetail['fromaddress'], $exception) !== FALSE) {
				return ['created_HelpDesk' => ''];
			}
		}
	}

	if ($adb->num_rows($result) == 0) {
		$contact_id = OSSMailScanner_Record_Model::findEmail($mailDetail['fromaddress'], 'Contacts', false);
		$parent_id = OSSMailScanner_Record_Model::findEmail($mailDetail['fromaddress'], 'Accounts', false);
		$HelpDeskInstance = CRMEntity::getInstance('HelpDesk');
		$HelpDeskInstance->column_fields['assigned_user_id'] = $assigned_user_id;
		$HelpDeskInstance->column_fields['ticket_title'] = $mailDetail['subject'];
		if ($contact_id && $contact_id != '' && $contact_id != '0') {
			$HelpDeskInstance->column_fields['contact_id'] = $contact_id;
		}
		if ($parent_id && $parent_id != '' && $parent_id != '0') {
			$HelpDeskInstance->column_fields['parent_id'] = $parent_id;
			$servicecontracts = $adb->pquery("SELECT servicecontractsid, priority FROM vtiger_servicecontracts where sc_related_to = ? ", array($parent_id));
			if ($adb->num_rows($servicecontracts) == 1) {
				$HelpDeskInstance->column_fields['servicecontractsid'] = $adb->query_result($servicecontracts, 0, 'servicecontractsid');
				$HelpDeskInstance->column_fields['ticketpriorities'] = $adb->query_result($servicecontracts, 0, 'priority');
			}
		}
		$HelpDeskInstance->column_fields['description'] = strip_tags($mailDetail['body']);
		$HelpDeskInstance->column_fields['ticketstatus'] = 'Open';
		$HelpDeskInstance->save('HelpDesk');
		$id = $HelpDeskInstance->id;
		$result_ossmailview = $adb->pquery("SELECT ossmailviewid FROM vtiger_ossmailview where uid = ? ", array($mailDetail['message_id']), true);
		if ($adb->num_rows($result_ossmailview) > 0) {
			$ossmailviewid = $adb->query_result($result_ossmailview, 0, 'ossmailviewid');
			$adb->pquery("INSERT INTO vtiger_crmentityrel SET crmid=?, module=?, relcrmid=?, relmodule=?", Array($ossmailviewid, 'OSSMailView', $id, 'HelpDesk'));
		}
		$adb->pquery("UPDATE vtiger_crmentity SET createdtime = ?,smcreatorid = ?,modifiedby = ?  WHERE crmid = ? ", array($mailDetail['udate_formated'], $assigned_user_id, $assigned_user_id, $id));
	}
	return Array('created_HelpDesk' => $id);
}
