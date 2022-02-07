<?php
/**
 * Relation Model Class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Class ModComments_Relation_Model.
 */
class ModComments_Relation_Model extends Vtiger_Relation_Model
{
	/** {@inheritdoc} */
	public function addRelation($sourceRecordId, $destinationRecordIds, $params = false)
	{
		$result = false;
		$sourceModuleName = $this->getParentModuleModel()->getName();
		if (!\is_array($destinationRecordIds)) {
			$destinationRecordIds = [$destinationRecordIds];
		}
		$data = [
			'CRMEntity' => $this->getParentModuleModel()->getEntityInstance(),
			'sourceModule' => $sourceModuleName,
			'sourceRecordId' => $sourceRecordId,
			'destinationModule' => $this->getRelationModuleModel()->getName(),
		];
		$eventHandler = new \App\EventHandler();
		$eventHandler->setModuleName($sourceModuleName);
		foreach ($destinationRecordIds as $destinationRecordId) {
			$data['destinationRecordId'] = $destinationRecordId;
			$eventHandler->setParams($data);
			$eventHandler->trigger('EntityBeforeLink');
			\CRMEntity::trackLinkedInfo($sourceRecordId);
			$eventHandler->trigger('EntityAfterLink');
		}
		return $result;
	}

	/**
	 * Set exceptional data.
	 */
	public function setExceptionData()
	{
		$data = [
			'tabid' => $this->getParentModuleModel()->getId(),
			'related_tabid' => $this->getRelationModuleModel()->getId(),
			'name' => 'getRelatedRecord',
			'actions' => '',
			'modulename' => $this->getParentModuleModel()->getName(),
		];
		$this->setData($data);
	}
}
