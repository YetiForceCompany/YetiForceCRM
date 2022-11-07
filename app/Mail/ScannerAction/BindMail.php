<?php
/**
 * Bind mail scanner action file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Mail\ScannerAction;

/**
 * Bind mail scanner action class.
 */
class BindMail extends Base
{
	/** {@inheritdoc} */
	public static $priority = 3;

	/** {@inheritdoc} */
	public function process(): void
	{
		$owner = $this->account->getSource()->get('assigned_user_id');
		if (empty($this->message->processData['CreatedMail']) || !($mailCrmId = $this->message->getMailCrmId($owner))) {
			return;
		}
		$returnIds = [];
		if ($ids = $this->findRelatedRecords(true)) {
			$relationModel = new \OSSMailView_Relation_Model();
			foreach ($ids as $id) {
				if ($relationModel->addRelation($mailCrmId, $id, $this->message->getDate())) {
					$returnIds[] = $id;
				}
			}
		}
		$this->message->setProcessData($this->getName(), $returnIds);
	}
}
