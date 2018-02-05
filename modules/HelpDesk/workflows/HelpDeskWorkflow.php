<?php

/**
 * HelpDeskWorkflow
 * @package YetiForce.Workflows
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class HelpDeskWorkflow
{

	/**
	 * Function to get addresses email to contacts
	 * @param int $id
	 * @return array
	 */
	private static function getContactsMailsFromTicket($id)
	{
		if (empty($id)) {
			return [];
		}
		$mails = [];
		$query = (new \App\Db\Query())->select(['relcrmid as contactid'])->from('vtiger_crmentityrel')->where(['module' => 'HelpDesk', 'relmodule' => 'Contacts', 'crmid' => $id])->createCommand()->query();
		while ($contactId = $query->readColumn(0)) {
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
	public static function HelpDeskChangeNotifyContacts(Vtiger_Record_Model $recordModel)
	{
		\App\Log::trace('Entering HelpDeskChangeNotifyContacts');
		$recordId = $recordModel->getId();
		$mails = static::getContactsMailsFromTicket($recordId);
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
	public static function HelpDeskClosedNotifyContacts(Vtiger_Record_Model $recordModel)
	{
		\App\Log::trace('Entering HelpDeskClosedNotifyContacts');
		$recordId = $recordModel->getId();
		$mails = static::getContactsMailsFromTicket($recordId);
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
	public static function HelpDeskNewCommentAccount(Vtiger_Record_Model $recordModel)
	{
		\App\Log::trace('Entering HelpDeskNewCommentAccount');
		$relatedToId = $recordModel->get('related_to');
		$moduleName = \App\Record::getType($relatedToId);
		$mail = false;
		if (!empty($relatedToId) && $moduleName === 'HelpDesk') {
			$mail = (new \App\Db\Query())->select(['vtiger_account.email1'])->from('vtiger_account')->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_account.accountid')->innerJoin('vtiger_troubletickets', 'vtiger_troubletickets.parent_id = vtiger_account.accountid')->where(['vtiger_crmentity.deleted' => 0, 'vtiger_troubletickets.ticketid' => $relatedToId, 'vtiger_account.emailoptout' => 1])->scalar();
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
	public static function HelpDeskNewCommentContacts(Vtiger_Record_Model $recordModel)
	{
		\App\Log::trace('Entering HelpDeskNewCommentContacts');
		$mails = static::getContactsMailsFromTicket($recordModel->get('related_to'));
		if (count($mails) > 0) {
			\App\Mailer::sendFromTemplate([
				'template' => 'NewCommentAddedToTicketContact',
				'moduleName' => 'ModComments',
				'recordId' => $recordModel->getId(),
				'to' => $mails,
			]);
		}
		\App\Log::trace('HelpDeskNewCommentContacts');
	}

	/**
	 * Function to send mail to users. Function invoke by workflow
	 * @param Vtiger_Record_Model $recordModel
	 */
	public static function HelpDeskNewCommentOwner(Vtiger_Record_Model $recordModel)
	{
		\App\Log::trace('Entering HelpDeskNewCommentOwner');
		$relatedToId = $recordModel->get('related_to');
		$mails = [];
		$result = (new \App\Db\Query())->select(['smownerid'])->from('vtiger_crmentity')->where(['deleted' => 0, 'crmid' => $relatedToId])->scalar();
		if ($result) {
			$smownerId = $result;
			$ownerType = \App\Fields\Owner::getType($smownerId);
			if ($ownerType === 'Users') {
				$user = App\User::getUserModel($smownerId);
				if ($user->getDetail('emailoptout') == 1) {
					$mails[] = $user->getDetail('email1');
				}
			} else {
				$groupUsers = \App\PrivilegeUtil::getUsersByGroup($smownerId);
				foreach ($groupUsers as $userId) {
					$user = App\User::getUserModel($userId);
					if ($user->getDetail('emailoptout') == 1) {
						$mails[] = $user->getDetail('email1');
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
		\App\Log::trace('HelpDeskNewCommentOwner');
	}
}
