<?php

/**
 * Mail scanner action bind HelpDesk
 * @package YetiForce.MailScanner
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailScanner_BindHelpDesk_ScannerAction extends OSSMailScanner_PrefixScannerAction_Model
{

	public $moduleName = 'HelpDesk';
	public $tableName = 'vtiger_troubletickets';
	public $tableColumn = 'ticket_no';

	public function process(OSSMail_Mail_Model $mail)
	{
		$this->mail = $mail;
		$ids = $this->findAndBind();
		if ($ids) {
			$id = array_shift($ids);
			if (!\App\Record::isExists($id, $this->moduleName)) {
				return false;
			}
			$conf = OSSMailScanner_Record_Model::getConfig('emailsearch');
			$recordModel = Vtiger_Record_Model::getInstanceById($id, $this->moduleName);
			if ($recordModel->get('ticketstatus') === 'Wait For Response' && !empty(AppConfig::module('Mail', 'HELPDESK_NEXT_WAIT_FOR_RESPONSE_STATUS'))) {
				$recordModel->set('ticketstatus', AppConfig::module('Mail', 'HELPDESK_NEXT_WAIT_FOR_RESPONSE_STATUS'));
				$recordModel->save();
			}
			$ticketStatus = array_flip(Settings_SupportProcesses_Module_Model::getTicketStatusNotModify());
			if ($mail->getTypeEmail() == 1 && isset($ticketStatus[$recordModel->get('ticketstatus')])) {
				if ($conf['changeTicketStatus'] === 'openTicket') {
					$recordModel->set('ticketstatus', AppConfig::module('Mail', 'HELPDESK_OPENTICKET_STATUS'));
					$recordModel->save();
				} elseif ($conf['changeTicketStatus'] === 'createTicket') {
					$mailAccount = $mail->getAccount();
					if (strstr($mailAccount['actions'], 'CreatedHelpDesk')) {
						$handler = new OSSMailScanner_CreatedHelpDesk_ScannerAction();
						$handler->add($mail);
					}
				}
			}
		}
		return $ids;
	}
}
