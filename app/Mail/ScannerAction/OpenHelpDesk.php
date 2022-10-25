<?php
/**
 * Base mail scanner action file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Mail\ScannerAction;

use App\Mail\RecordFinder;

/**
 * Base mail scanner action class.
 */
class OpenHelpDesk extends Base
{
	/** {@inheritdoc} */
	public static $priority = 4;

	/** {@inheritdoc} */
	public function process(): void
	{
		$scanner = $this->message;
		if ($this->checkExceptions() || \App\Mail\Message\Base::MAIL_TYPE_RECEIVED !== $scanner->getMailType() || !($prefix = RecordFinder::getRecordNumberFromString($this->message->getHeader('subject'), 'HelpDesk')) || !($id = \App\Record::getIdByRecordNumber($prefix, 'HelpDesk'))) {
			return;
		}
		$recordModel = \Vtiger_Record_Model::getInstanceById($id, 'HelpDesk');
		if ('Wait For Response' === $recordModel->get('ticketstatus') && !empty(\Config\Modules\OSSMailScanner::$helpdeskBindNextWaitForResponseStatus)) {
			$recordModel->set('ticketstatus', \Config\Modules\OSSMailScanner::$helpdeskBindNextWaitForResponseStatus);
		} elseif (($ticketStatus = array_flip(\Settings_SupportProcesses_Module_Model::getTicketStatusNotModify())) && isset($ticketStatus[$recordModel->get('ticketstatus')])) {
			$recordModel->set('ticketstatus', \Config\Modules\OSSMailScanner::$helpdeskBindOpenStatus);
		}

		$recordModel->save();
		$this->message->setProcessData($this->getName(), $recordModel->getId());
	}
}
