<?php

/**
 * HelpDeskWorkflow.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class HelpDeskWorkflow
{
	/**
	 * Function to get addresses email to contacts.
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	private static function getContactsMailsFromTicket(int $id)
	{
		$queryGenerator = new \App\QueryGenerator('Contacts');
		$queryGenerator->permissions = false;
		$queryGenerator->setFields(['email']);
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_crmentityrel', $queryGenerator->getColumnName('id') . '=vtiger_crmentityrel.relcrmid']);
		$queryGenerator->addNativeCondition(['and', ['vtiger_crmentityrel.crmid' => $id], ['vtiger_crmentityrel.module' => 'HelpDesk']]);
		$queryGenerator->addCondition('email', '', 'ny');
		if (App\Config::module('HelpDesk', 'CONTACTS_CHECK_EMAIL_OPTOUT')) {
			$queryGenerator->addCondition('emailoptout', 1, 'e');
		}
		return $queryGenerator->createQuery()->column();
	}

	/**
	 * Function to send mail to contacts. Function invoke by workflow.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public static function helpDeskChangeNotifyContacts(Vtiger_Record_Model $recordModel)
	{
		\App\Log::trace('Entering helpDeskChangeNotifyContacts');
		$recordId = $recordModel->getId();
		$mails = static::getContactsMailsFromTicket($recordId);
		if (\count($mails) > 0) {
			\App\Mailer::sendFromTemplate([
				'template' => 'NotifyContactOnTicketChange',
				'moduleName' => 'HelpDesk',
				'recordId' => $recordId,
				'to' => $mails,
			]);
		}
		\App\Log::trace('helpDeskChangeNotifyContacts');
	}

	/**
	 * Function to send mail to contacts. Function invoke by workflow.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public static function helpDeskClosedNotifyContacts(Vtiger_Record_Model $recordModel)
	{
		\App\Log::trace('Entering helpDeskClosedNotifyContacts');
		$recordId = $recordModel->getId();
		$mails = static::getContactsMailsFromTicket($recordId);
		if (\count($mails) > 0) {
			\App\Mailer::sendFromTemplate([
				'template' => 'NotifyContactOnTicketClosed',
				'moduleName' => 'HelpDesk',
				'recordId' => $recordId,
				'to' => $mails,
			]);
		}
		\App\Log::trace('helpDeskClosedNotifyContacts');
	}

	/**
	 * Function to send mail to accounts. Function invoke by workflow.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public static function helpDeskNewCommentAccount(Vtiger_Record_Model $recordModel)
	{
		\App\Log::trace('Entering helpDeskNewCommentAccount');
		$relatedToId = $recordModel->get('related_to');
		$moduleName = \App\Record::getType($relatedToId);
		$mail = false;
		if (!empty($relatedToId) && 'HelpDesk' === $moduleName) {
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
		\App\Log::trace('helpDeskNewCommentAccount');
	}

	/**
	 * Function to send mail to contacts. Function invoke by workflow.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public static function helpDeskNewCommentContacts(Vtiger_Record_Model $recordModel)
	{
		\App\Log::trace('Entering helpDeskNewCommentContacts');
		if (($relId = $recordModel->get('related_to')) && 'HelpDesk' === \App\Record::getType($relId) && ($mails = static::getContactsMailsFromTicket($relId))) {
			\App\Mailer::sendFromTemplate([
				'template' => 'NewCommentAddedToTicketContact',
				'moduleName' => 'ModComments',
				'recordId' => $recordModel->getId(),
				'to' => $mails,
			]);
		}
		\App\Log::trace('helpDeskNewCommentContacts');
	}

	/**
	 * Function to send mail to users. Function invoke by workflow.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 */
	public static function helpDeskNewCommentOwner(Vtiger_Record_Model $recordModel)
	{
		\App\Log::trace('Entering helpDeskNewCommentOwner');
		$relatedToId = $recordModel->get('related_to');
		$result = (new \App\Db\Query())->select(['smownerid'])->from('vtiger_crmentity')->where(['deleted' => 0, 'crmid' => $relatedToId])->scalar();
		if ($result) {
			$mails = [];
			$smownerId = $result;
			$ownerType = \App\Fields\Owner::getType($smownerId);
			if ('Users' === $ownerType) {
				$user = App\User::getUserModel($smownerId);
				if (1 === (int) $user->getDetail('emailoptout')) {
					$mails[] = $user->getDetail('email1');
				}
			} else {
				$groupUsers = \App\PrivilegeUtil::getUsersByGroup($smownerId);
				foreach ($groupUsers as $userId) {
					$user = App\User::getUserModel($userId);
					if (1 === (int) $user->getDetail('emailoptout')) {
						$mails[] = $user->getDetail('email1');
					}
				}
			}
			if ($mails) {
				\App\Mailer::sendFromTemplate([
					'template' => 'NewCommentAddedToTicketOwner',
					'moduleName' => 'ModComments',
					'recordId' => $recordModel->getId(),
					'to' => $mails,
				]);
			}
		}
		\App\Log::trace('helpDeskNewCommentOwner');
	}
}
