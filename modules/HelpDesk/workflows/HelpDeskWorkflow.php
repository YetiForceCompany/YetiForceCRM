<?php
/**
 * 
 * @package YetiForce.Workflows
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */

/**
 * Function to get addresses email to contacts
 * @param int $id
 * @return array
 */
function getContactsMailsFromTicket($id)
{
	if (empty($id)) {
		return [];
	}
	$db = PearDatabase::getInstance();
	$mails = [];
	$sql = 'SELECT `relcrmid` as contactid FROM `vtiger_crmentityrel` WHERE `module` = ? && `relmodule` = ? && `crmid` = ?;';
	$result = $db->pquery($sql, ['HelpDesk', 'Contacts', $id]);
	while ($contactId = $db->getSingleValue($result)) {
		if (App\Record::isExists($contactId)) {
			$contactRecord = Vtiger_Record_Model::getInstanceById($contactId, 'Contacts');
			$primaryEmail = $contactRecord->get('email');
			if (($contactRecord->get('emailoptout') == 1 || !AppConfig::module('HelpDesk', 'CONTACTS_CHECK_EMAIL_OPTOUT')) && !empty($primaryEmail)) {
				$mails[] = $primaryEmail;
			}
		}
	}
	return $mails;
}

/**
 * Function to send mail to contacts. Function invoke by workflow
 * @param Vtiger_Record_Model $recordModel
 */
function HelpDeskChangeNotifyContacts(Vtiger_Record_Model $recordModel)
{
	\App\Log::trace('Entering HelpDeskChangeNotifyContacts');
	$recordId = $recordModel->getId();
	$mails = getContactsMailsFromTicket($recordId);
	if (count($mails) > 0) {
		\App\Mailer::sendFromTemplate([
			'template' => 'NotifyContactOnTicketChange',
			'moduleName' => 'HelpDesk',
			'recordId' => $recordId,
			'to' => $mails,
		]);
	}
	\App\Log::trace('HelpDeskChangeNotifyContacts');
}

/**
 * Function to send mail to contacts. Function invoke by workflow
 * @param Vtiger_Record_Model $recordModel
 */
function HelpDeskClosedNotifyContacts(Vtiger_Record_Model $recordModel)
{
	\App\Log::trace('Entering HelpDeskClosedNotifyContacts');
	$recordId = $recordModel->getId();
	$mails = getContactsMailsFromTicket($recordId);
	if (count($mails) > 0) {
		\App\Mailer::sendFromTemplate([
			'template' => 'NotifyContactOnTicketClosed',
			'moduleName' => 'HelpDesk',
			'recordId' => $recordId,
			'to' => $mails,
		]);
	}
	\App\Log::trace('HelpDeskClosedNotifyContacts');
}

/**
 * Function to send mail to accounts. Function invoke by workflow
 * @param Vtiger_Record_Model $recordModel
 */
function HelpDeskNewCommentAccount(Vtiger_Record_Model $recordModel)
{
	$db = PearDatabase::getInstance();
	\App\Log::trace('Entering HelpDeskNewCommentAccount');
	$relatedToId = $recordModel->get('related_to');
	$moduleName = vtlib\Functions::getCRMRecordType($relatedToId);
	$mail = false;
	if (!empty($relatedToId) && $moduleName == 'HelpDesk') {
		if ($moduleName == 'HelpDesk') {
			$sql = 'SELECT vtiger_account.email1 FROM vtiger_account
INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_account.accountid
INNER JOIN vtiger_troubletickets ON vtiger_troubletickets.parent_id = vtiger_account.accountid
WHERE vtiger_crmentity.deleted = 0 && vtiger_troubletickets.ticketid = ? && vtiger_account.emailoptout = 1';
			$result = $db->pquery($sql, [$relatedToId]);
			if ($result->rowCount() > 0) {
				$mail = $db->getSingleValue($result);
			}
		}
	}
	if ($mail) {
		\App\Mailer::sendFromTemplate([
			'template' => 'NewCommentAddedToTicketAccount',
			'moduleName' => 'ModComments',
			'recordId' => $recordModel->getId(),
			'to' => $mail,
		]);
	}
	\App\Log::trace('HelpDeskNewCommentAccount');
}

/**
 * Function to send mail to contacts. Function invoke by workflow
 * @param Vtiger_Record_Model $recordModel
 */
function HelpDeskNewCommentContacts(Vtiger_Record_Model $recordModel)
{
	\App\Log::trace('Entering HelpDeskNewCommentAccount');
	$mails = getContactsMailsFromTicket($recordModel->get('related_to'));
	if (count($mails) > 0) {
		\App\Mailer::sendFromTemplate([
			'template' => 'NewCommentAddedToTicketContact',
			'moduleName' => 'ModComments',
			'recordId' => $recordModel->getId(),
			'to' => $mails,
		]);
	}
	\App\Log::trace('HelpDeskNewCommentAccount');
}

/**
 * Function to send mail to users. Function invoke by workflow
 * @param Vtiger_Record_Model $recordModel
 */
function HelpDeskNewCommentOwner(Vtiger_Record_Model $recordModel)
{
	\App\Log::trace('Entering HelpDeskNewCommentAccount');
	$db = PearDatabase::getInstance();
	$relatedToId = $recordModel->get('related_to');
	$mails = [];
	$sql = 'SELECT smownerid FROM vtiger_crmentity WHERE deleted = 0 && crmid = ? ';
	$result = $db->pquery($sql, [$relatedToId]);
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
		\App\Mailer::sendFromTemplate([
			'template' => 'NewCommentAddedToTicketOwner',
			'moduleName' => 'ModComments',
			'recordId' => $recordModel->getId(),
			'to' => $mails,
		]);
	}
	\App\Log::trace('HelpDeskNewCommentAccount');
}
