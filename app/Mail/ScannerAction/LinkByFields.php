<?php
/**
 * Base mail scanner action file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Mail\ScannerAction;

/**
 * Base mail scanner action class.
 */
class LinkByFields extends Base
{
	/** {@inheritdoc} */
	public static $priority = 3;

	/** {@inheritdoc} */
	public function process(): void
	{
		$scanner = $this->scannerEngine;
		if (empty($scanner->processData['CreatedMail']) || false === $scanner->getMailCrmId()) {
			return;
		}
		$returnIds = [];
		if ($ids = $scanner->findRelatedRecords(true)) {
			$relationModel = new \OSSMailView_Relation_Model();
			foreach ($ids as $id) {
				if ($relationModel->addRelation($scanner->getMailCrmId(), $id, $scanner->get('date'))) {
					$returnIds[] = $id;
				}
			}
		}
		$scanner->processData['LinkByFields'] = $returnIds;
	}
}
