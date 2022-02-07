<?php

/**
 * OSSMailView Relation model class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OSSMailView_Relation_Model extends Vtiger_Relation_Model
{
	/**
	 * Add relation.
	 *
	 * @param int       $sourceRecordId
	 * @param int|int[] $destinationRecordId
	 * @param mixed     $params
	 *
	 * @return bool
	 */
	public function addRelation($sourceRecordId, $destinationRecordId, $params = false)
	{
		$return = false;
		if (!\is_array($destinationRecordId)) {
			$destinationRecordId = [$destinationRecordId];
		}

		$relationModel = empty($this->getData) ? (new OSSMailView_GetRecordToMails_Relation()) : $this->getTypeRelationModel();
		if ($params) {
			$relationModel->date = $params;
		}
		foreach ($destinationRecordId as $crmId) {
			$destinationModuleName = \App\Record::getType($crmId);
			$data = [
				'CRMEntity' => CRMEntity::getInstance($destinationModuleName),
				'sourceModule' => $destinationModuleName,
				'sourceRecordId' => $crmId,
				'destinationModule' => 'OSSMailView',
				'destinationRecordId' => $sourceRecordId,
			];
			$eventHandler = new App\EventHandler();
			$eventHandler->setModuleName($destinationModuleName);
			$eventHandler->setParams($data);
			$eventHandler->trigger('EntityBeforeLink');
			if ($return = $relationModel->create($sourceRecordId, $crmId)) {
				CRMEntity::trackLinkedInfo($crmId);
				$eventHandler->trigger('EntityAfterLink');
			}
		}
		return $return;
	}
}
