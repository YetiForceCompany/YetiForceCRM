<?php
/**
 * Transfer records.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Transfer records class.
 */
class RecordTransfer
{
	/**
	 * Transfer.
	 *
	 * @param int   $recordId
	 * @param array $migrate
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 * @throws \Throwable
	 */
	public static function transfer(int $recordId, array $migrate)
	{
		if (!Record::isExists($recordId) || (($record = \Vtiger_Record_Model::getInstanceById($recordId)) && !$record->isViewable())) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$transaction = Db::getInstance()->beginTransaction();
		try {
			static::recordData($record, $migrate);
			$record->ext['modificationType'] = \ModTracker_Record_Model::TRANSFER_EDIT;
			$record->save();
			static::relations($recordId, array_keys($migrate));
			$transaction->commit();
		} catch (\Throwable $ex) {
			$transaction->rollBack();
			throw $ex;
		}
	}

	/**
	 * Update record data.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 * @param array                $migrate     [$recordId => [$source => $target, ...], ...]
	 *
	 * @throws Exceptions\NoPermittedToRecord
	 * @throws Exceptions\FieldException
	 */
	public static function recordData(\Vtiger_Record_Model $recordModel, array $migrate)
	{
		foreach ($migrate as $recordId => $fields) {
			if (!Record::isExists($recordId) || (($sourceRecord = \Vtiger_Record_Model::getInstanceById($recordId)) && (!$sourceRecord->isViewable()))) {
				throw new Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
			foreach ($fields as $source => $target) {
				$sourceFieldModel = $sourceRecord->getField($source);
				$targetFieldModel = $recordModel->getField($target);
				if ($sourceFieldModel && $sourceFieldModel->isViewEnabled() && $targetFieldModel && $targetFieldModel->isEditable()) {
					$recordModel->set($target, $sourceFieldModel->getUITypeModel()->getDuplicateValue($sourceRecord));
				} else {
					throw new Exceptions\FieldException('ERR_FIELD_NOT_FOUND');
				}
			}
		}
	}

	/**
	 * Transfer relations.
	 *
	 * @param int   $sourceId
	 * @param array $records
	 *
	 * @throws Exceptions\NoPermittedToRecord
	 */
	public static function relations(int $sourceId, array $records)
	{
		$sourceRecord = \Vtiger_Record_Model::getInstanceById($sourceId);
		foreach ($records as $recordId) {
			$recordModel = \Vtiger_Record_Model::getInstanceById($recordId);
			if (!$recordModel->isViewable()) {
				throw new Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
			if ($recordModel->getModule()->isCommentEnabled() && $sourceRecord->getModule()->isCommentEnabled()) {
				\CRMEntity::getInstance('ModComments');
				\ModComments::transferRecords($recordId, $sourceId);
			}
			$relations = \Vtiger_Relation_Model::getAllRelations($recordModel->getModule(), false, true, false);
			foreach (\Vtiger_Relation_Model::getAllRelations($sourceRecord->getModule(), false, true, false) as $sourceRelation) {
				foreach ($relations as $destRelation) {
					if ($sourceRelation->get('related_tabid') === $destRelation->get('related_tabid') && $sourceRelation->get('name') === $destRelation->get('name') && $sourceRelation->get('field_name') === $destRelation->get('field_name')) {
						$destRelation = clone $destRelation;
						$sourceRelation = clone $sourceRelation;
						$queryGenerator = $sourceRelation->set('parentRecord', $sourceRecord)->getQuery()->setFields(['id']);
						$queryGenerator->permissions = false;
						$queryGenerator->setStateCondition('All');
						$sourceRelationIds = $queryGenerator->createQuery()->column();
						$queryGenerator = $destRelation->set('parentRecord', $recordModel)->getQuery()->setFields(['id']);
						$queryGenerator->permissions = false;
						$queryGenerator->setStateCondition('All');
						$relationIds = $queryGenerator->createQuery()->column();
						foreach (array_diff($relationIds, $sourceRelationIds) as $relId) {
							$sourceRelation->transfer([$relId => $recordModel->getId()]);
							if (!$destRelation->isDirectRelation()) {
								$destRelation->transferDelete($relId);
							}
						}
						foreach (array_intersect($relationIds, $sourceRelationIds) as $relId) {
							$destRelation->transferDelete($relId);
						}
						if ($destRelation->isTreeRelation()) {
							$relTrees = $destRelation->getRelationTreeQuery()->select(['ttd.tree', 'crmid'])->createCommand()->queryAllByGroup();
							$sourceTrees = $sourceRelation->getRelationTreeQuery()->select(['ttd.tree', 'crmid'])->createCommand()->queryAllByGroup();
							foreach ($relTrees as $tree => $crmid) {
								if (!isset($sourceTrees[$tree])) {
									$sourceRelation->transferTree([$tree => $crmid]);
								}
								$destRelation->deleteRelationTree($recordModel->getId(), $tree);
							}
						}
					}
				}
			}
		}
	}
}
