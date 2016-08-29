<?php

/**
 * Mail scanner action bind HelpDesk
 * @package YetiForce.MailScanner
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailScanner_BindHelpDesk_ScannerAction extends OSSMailScanner_PrefixScannerAction_Model
{

	public function process(OSSMail_Mail_Model $mail)
	{
		$moduleName = 'HelpDesk';
		$answeredStatus = 'Answered';

		$ids = parent::process($mail, $moduleName, 'vtiger_troubletickets', 'ticket_no');
		if ($ids) {
			$conf = OSSMailScanner_Record_Model::getConfig('emailsearch');
			if ($conf['change_ticket_status'] == 'true' && $mail->getTypeEmail() == 1) {
				foreach ($ids as $id) {
					$recordModel = Vtiger_Record_Model::getInstanceById($id, $moduleName);
					if ($recordModel->get('ticketstatus') == 'Wait For Response') {
						$recordModel->set('ticketstatus', $answeredStatus);
						$recordModel->save();
					}
				}
			}
		}
		return $ids;
	}
}
