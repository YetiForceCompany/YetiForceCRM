<?php
/**
 * Relation Model Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Class Documents_Relation_Model.
 */
class Documents_Relation_Model extends Vtiger_Relation_Model
{
	/**
	 * Set exceptional data.
	 */
	public function setExceptionData()
	{
		$data = [
			'tabid' => $this->getParentModuleModel()->getId(),
			'related_tabid' => $this->getRelationModuleModel()->getId(),
			'name' => 'getRelatedRecord',
			'actions' => 'ADD, SELECT',
			'modulename' => $this->getParentModuleModel()->getName(),
		];
		$this->setData($data);
	}

	/**
	 * Delete relation.
	 *
	 * @param int $relatedRecordId
	 * @param int $sourceRecordId
	 *
	 * @return bool
	 */
	public function deleteRelation($relatedRecordId, $sourceRecordId)
	{
		$destinationModuleName = $this->getParentModuleModel()->get('name');
		$sourceModuleName = $this->getRelationModuleModel()->get('name');
		if ('OSSMailView' == $destinationModuleName || 'OSSMailView' == $sourceModuleName) {
			return $this->deleteRelationOSSMailView($relatedRecordId, $sourceRecordId);
		}
		if ('ModComments' == $destinationModuleName) {
			include_once 'modules/ModTracker/ModTracker.php';
			ModTracker::unLinkRelation($destinationModuleName, $relatedRecordId, $sourceModuleName, $sourceRecordId);
			return true;
		}
		$relationFieldModel = $this->getRelationField();
		if ($relationFieldModel && $relationFieldModel->isMandatory()) {
			return false;
		}
		$return = true;
		if (!empty($sourceModuleName) && !empty($sourceRecordId)) {
			$destinationModuleFocus = $this->getParentModuleModel()->getEntityInstance();
			$eventHandler = new \App\EventHandler();
			$eventHandler->setModuleName($sourceModuleName);
			$eventHandler->setParams([
				'CRMEntity' => $destinationModuleFocus,
				'sourceModule' => $sourceModuleName,
				'sourceRecordId' => $sourceRecordId,
				'destinationModule' => $destinationModuleName,
				'destinationRecordId' => $relatedRecordId,
			]);
			$eventHandler->trigger('EntityBeforeUnLink');

			$destinationModuleFocus->unlinkRelationship($relatedRecordId, $sourceModuleName, $sourceRecordId, $this->get('name'));
			$destinationModuleFocus->trackUnLinkedInfo($sourceRecordId);

			$eventHandler->trigger('EntityAfterUnLink');
		} elseif ($relationFieldModel) {
			$relationRecordModel = \Vtiger_Record_Model::getInstanceById($relatedRecordId, $destinationModuleName);
			if ($relationRecordModel->isEditable()) {
				$relationRecordModel->set($relationFieldModel->getName(), 0);
				$relationRecordModel->ext['modificationType'] = \ModTracker_Record_Model::UNLINK;
				$relationRecordModel->save();
			} else {
				$return = false;
			}
		} else {
			\App\Log::warning("No link has been removed, improper relationship ($relatedRecordId, $sourceRecordId, $destinationModuleName, $sourceModuleName)");
		}
		return $return;
	}

	/**
	 * Delete relation for OSSMailView module.
	 *
	 * @param int $sourceRecordId
	 * @param int $relatedRecordId
	 *
	 * @return bool
	 */
	private function deleteRelationOSSMailView($relatedRecordId, $sourceRecordId)
	{
		$destinationModuleName = $this->getParentModuleModel()->get('name');
		if ('OSSMailView' == $destinationModuleName) {
			$mailId = $relatedRecordId;
			$crmId = $sourceRecordId;
		} else {
			$mailId = $sourceRecordId;
			$crmId = $relatedRecordId;
		}
		if (\App\Db::getInstance()->createCommand()->delete('vtiger_ossmailview_relation', ['crmid' => $crmId, 'ossmailviewid' => $mailId])->execute()) {
			return true;
		}
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function transferDb(array $params)
	{
		return \App\Db::getInstance()->createCommand()->update('vtiger_senotesrel', ['crmid' => $params['sourceRecordId'], 'notesid' => $params['destinationRecordId']], ['crmid' => $params['fromRecordId'], 'notesid' => $params['destinationRecordId']])->execute();
	}

	/**
	 * Function to get related record with document.
	 */
	public function getRelatedRecord()
	{
		$queryGenerator = $this->getQueryGenerator();
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_senotesrel', 'vtiger_senotesrel.crmid = vtiger_crmentity.crmid']);
		$queryGenerator->addNativeCondition(['vtiger_senotesrel.notesid' => $this->get('parentRecord')->getId()]);
	}
}
