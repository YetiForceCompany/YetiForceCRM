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

function getContactsMailsFromTicket($id)
{
	if (empty($id)) {
		return [];
	}

	$db = PearDatabase::getInstance();
	$mails = [];
	$sql = 'SELECT `relcrmid` as contactid FROM `vtiger_crmentityrel` WHERE `module` = ? && `relmodule` = ? && `crmid` = ?;';
	$result = $db->pquery($sql, ['HelpDesk', 'Contacts', $id]);
	$num = $db->num_rows($result);

	while ($contactId = $db->getSingleValue($result)) {
		if (isRecordExists($contactId)) {
			$contactRecord = Vtiger_Record_Model::getInstanceById($contactId, 'Contacts');
			$primaryEmail = $contactRecord->get('email');

			if ($contactRecord->get('emailoptout') == 1 && !empty($primaryEmail)) {
				$mails[] = $primaryEmail;
			}
		}
	}
	return $mails;
}

function HeldDeskChangeNotifyContacts($entityData)
{
	$log = LoggerManager::getInstance();
	$log->debug('Entering HeldDeskChangeNotifyContacts');
	$wsId = $entityData->getId();
	$parts = explode('x', $wsId);
	$entityId = $parts[1];

	$mails = getContactsMailsFromTicket($entityId);
	if (count($mails) > 0) {
		$mails = implode(',', $mails);
		$data = [
			'sysname' => 'NotifyContactOnTicketChange',
			'to_email' => $mails,
			'module' => 'HelpDesk',
			'record' => $entityId
		];
		$recordModel = Vtiger_Record_Model::getCleanInstance('OSSMailTemplates');
		if ($recordModel->sendMailFromTemplate($data)) {
			$log->debug('HeldDeskChangeNotifyContacts');
			return true;
		}
	}

	$log->debug('HeldDeskChangeNotifyContacts');
	return false;
}

function HeldDeskClosedNotifyContacts($entityData)
{
	$log = LoggerManager::getInstance();
	$log->debug('Entering HeldDeskClosedNotifyContacts');
	$wsId = $entityData->getId();
	$parts = explode('x', $wsId);
	$entityId = $parts[1];

	$mails = getContactsMailsFromTicket($entityId);
	if (count($mails) > 0) {
		$mails = implode(',', $mails);
		$data = [
			'sysname' => 'NotifyContactOnTicketClosed',
			'to_email' => $mails,
			'module' => 'HelpDesk',
			'record' => $entityId
		];
		$recordModel = Vtiger_Record_Model::getCleanInstance('OSSMailTemplates');
		if ($recordModel->sendMailFromTemplate($data)) {
			$log->debug('HeldDeskClosedNotifyContacts');
			return true;
		}
	}

	$log->debug('HeldDeskClosedNotifyContacts');
	return false;
}

function HeldDeskNewCommentAccount($entityData)
{
	$log = LoggerManager::getInstance();
	$db = PearDatabase::getInstance();
	$log->debug('Entering HeldDeskNewCommentAccount');

	$wsId = $entityData->getId();
	$parts = explode('x', $wsId);
	$entityId = $parts[1];

	$data = $entityData->getData();
	$relatedToWSId = $data['related_to'];
	$relatedToId = explode('x', $relatedToWSId);
	$moduleName = vtlib\Functions::getCRMRecordType($relatedToId[1]);
	$mail = false;
	if (!empty($relatedToWSId) && $moduleName == 'HelpDesk') {
		if ($moduleName == 'HelpDesk') {
			$sql = 'SELECT vtiger_account.email1 FROM vtiger_account
INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_account.accountid
INNER JOIN vtiger_troubletickets ON vtiger_troubletickets.parent_id = vtiger_account.accountid
WHERE vtiger_crmentity.deleted = 0 && vtiger_troubletickets.ticketid = ? && vtiger_account.emailoptout = 1';
			$result = $db->pquery($sql, [$relatedToId[1]]);
			if ($result->rowCount() > 0) {
				$mail = $db->getSingleValue($result);
			}
		}
	}
	if ($mail) {
		$data = [
			'sysname' => 'NewCommentAddedToTicketAccount',
			'to_email' => $mail,
			'module' => 'ModComments',
			'record' => $entityId
		];
		$recordModel = Vtiger_Record_Model::getCleanInstance('OSSMailTemplates');
		if ($recordModel->sendMailFromTemplate($data)) {
			$log->debug('HeldDeskNewCommentAccount');
			return true;
		}
	}

	$log->debug('HeldDeskNewCommentAccount');
	return false;
}

function HeldDeskNewCommentContacts($entityData)
{
	$log = LoggerManager::getInstance();
	$log->debug('Entering HeldDeskNewCommentAccount');

	$wsId = $entityData->getId();
	$parts = explode('x', $wsId);
	$entityId = $parts[1];
	$data = $entityData->getData();
	$relatedToWSId = $data['related_to'];
	$relatedToId = explode('x', $relatedToWSId);

	$mails = getContactsMailsFromTicket($relatedToId[1]);
	if (count($mails) > 0) {
		$mails = implode(',', $mails);
		$data = [
			'sysname' => 'NewCommentAddedToTicketContact',
			'to_email' => $mails,
			'module' => 'ModComments',
			'record' => $entityId
		];
		$recordModel = Vtiger_Record_Model::getCleanInstance('OSSMailTemplates');
		if ($recordModel->sendMailFromTemplate($data)) {
			$log->debug('HeldDeskNewCommentAccount');
			return true;
		}
	}

	$log->debug('HeldDeskNewCommentAccount');
	return false;
}

function HeldDeskNewCommentOwner($entityData)
{
	$log = LoggerManager::getInstance();
	$log->debug('Entering HeldDeskNewCommentAccount');
	$db = PearDatabase::getInstance();

	$wsId = $entityData->getId();
	$parts = explode('x', $wsId);
	$entityId = $parts[1];
	$data = $entityData->getData();
	$relatedToWSId = $data['related_to'];
	$relatedToId = explode('x', $relatedToWSId);
	$mails = [];

	$sql = 'SELECT smownerid FROM vtiger_crmentity WHERE deleted = 0 && crmid = ? ';
	$result = $db->pquery($sql, [$relatedToId[1]]);
	if ($result->rowCount() > 0) {
		$smownerid = $db->getSingleValue($result);
		$ownerType = vtws_getOwnerType($smownerid);
		if ($ownerType == 'Users') {
			$user = new Users();
			$currentUser = $user->retrieveCurrentUserInfoFromFile($smownerid);
			if ($currentUser->column_fields['emailoptout'] == '1') {
				$mails[] = $currentUser->column_fields['email1'];
			}
		} else {
			require_once('include/utils/GetGroupUsers.php');
			$ggu = new GetGroupUsers();
			$ggu->getAllUsersInGroup($smownerid);
			foreach ($ggu->group_users as $userId) {
				$user = new Users();
				$currentUser = $user->retrieveCurrentUserInfoFromFile($userId);
				if ($currentUser->column_fields['emailoptout'] == '1') {
					$mails[] = $currentUser->column_fields['email1'];
				}
			}
		}
	}

	if (count($mails) > 0) {
		$mails = implode(',', $mails);
		$data = [
			'sysname' => 'NewCommentAddedToTicketOwner',
			'to_email' => $mails,
			'module' => 'ModComments',
			'record' => $entityId
		];
		$recordModel = Vtiger_Record_Model::getCleanInstance('OSSMailTemplates');
		if ($recordModel->sendMailFromTemplate($data)) {
			$log->debug('HeldDeskNewCommentAccount');
			return true;
		}
	}

	$log->debug('HeldDeskNewCommentAccount');
	return false;
}
