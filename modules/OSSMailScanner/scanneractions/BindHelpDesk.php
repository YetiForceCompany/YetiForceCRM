<?php

/**
 * Mail scanner action bind HelpDesk.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailScanner_BindHelpDesk_ScannerAction extends OSSMailScanner_PrefixScannerAction_Model
{
	public $moduleName = 'HelpDesk';
	public $tableName = 'vtiger_troubletickets';
	public $tableColumn = 'ticket_no';

	/** {@inheritdoc} */
	public function process(OSSMail_Mail_Model $mail)
	{
		$this->mail = $mail;
		$ids = $this->findAndBind();
		if ($ids) {
			$id = current($ids);
			if (!\App\Record::isExists($id, $this->moduleName) || 1 !== $mail->getTypeEmail()) {
				return false;
			}
			$conf = OSSMailScanner_Record_Model::getConfig('emailsearch');
			$recordModel = Vtiger_Record_Model::getInstanceById($id, $this->moduleName);
			if ('Wait For Response' === $recordModel->get('ticketstatus') && !empty(\Config\Modules\OSSMailScanner::$helpdeskBindNextWaitForResponseStatus)) {
				$recordModel->set('ticketstatus', \Config\Modules\OSSMailScanner::$helpdeskBindNextWaitForResponseStatus);
				$recordModel->save();
			}
			$ticketStatus = array_flip(Settings_SupportProcesses_Module_Model::getTicketStatusNotModify());
			if (isset($ticketStatus[$recordModel->get('ticketstatus')])) {
				if ('openTicket' === $conf['changeTicketStatus']) {
					$recordModel->set('ticketstatus', \Config\Modules\OSSMailScanner::$helpdeskBindOpenStatus);
					$recordModel->save();
				} elseif ('createTicket' === $conf['changeTicketStatus']) {
					$mailAccount = $mail->getAccount();
					if (\is_array($mailAccount['actions']) ? \in_array('CreatedHelpDesk', $mailAccount['actions']) : strstr($mailAccount['actions'], 'CreatedHelpDesk')) {
						$handler = new OSSMailScanner_CreatedHelpDesk_ScannerAction();
						$handler->mail = $mail;
						$handler->add();
					}
				}
			}
		}
		return $ids;
	}
}
