<?php
/**
 * Main file that includes basic operations on relations.
 *
 * @package   Relation
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
use App\Relation\RelationInterface;

/**
 * Vtiger_GetManyToMany_Relation class.
 */
class Vtiger_GetManyToMany_Relation implements RelationInterface
{
	/** {@inheritdoc} */
	public function getRelationType(): int
	{
		return Vtiger_Relation_Model::RELATION_M2M;
	}

	/** {@inheritdoc} */
	public function getQuery()
	{
		$recordId = $this->relationModel->get('parentRecord')->getId();
		$relatedModuleName = $this->relationModel->getRelationModuleName();
		$parentModuleName = $this->relationModel->getParentModuleModel()->getName();
		$queryGenerator = $this->relationModel->getQueryGenerator()->setDistinct('id');
		$referenceInfo = \Vtiger_Relation_Model::getReferenceTableInfo($relatedModuleName, $parentModuleName);

		if ($relatedModuleName === $parentModuleName) {
			$queryGenerator->addJoin([
				'INNER JOIN',
				$referenceInfo['table'], "({$referenceInfo['table']}.{$referenceInfo['base']} = vtiger_crmentity.crmid OR {$referenceInfo['table']}.{$referenceInfo['rel']} = vtiger_crmentity.crmid)"
			])->addNativeCondition([
				'or',
				[$referenceInfo['table'] . '.' . $referenceInfo['base'] => $recordId],
				[$referenceInfo['table'] . '.' . $referenceInfo['rel'] => $recordId]
			])->addCondition('id', $recordId, 'n');
		} else {
			$queryGenerator->addJoin(['INNER JOIN', $referenceInfo['table'], "{$referenceInfo['table']}.{$referenceInfo['rel']} = vtiger_crmentity.crmid"])
				->addNativeCondition([$referenceInfo['table'] . '.' . $referenceInfo['base'] => $recordId]);
		}
	}

	/** {@inheritdoc} */
	public function delete(int $sourceRecordId, int $destinationRecordId): bool
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$relatedModuleName = $this->relationModel->getRelationModuleName();
		$parentModuleName = $this->relationModel->getParentModuleModel()->getName();
		$referenceInfo = \Vtiger_Relation_Model::getReferenceTableInfo($relatedModuleName, $parentModuleName);

		$result = $dbCommand->delete($referenceInfo['table'], [$referenceInfo['rel'] => $destinationRecordId, $referenceInfo['base'] => $sourceRecordId])->execute();
		if ($relatedModuleName === $parentModuleName) {
			$result += $dbCommand->delete($referenceInfo['table'], [$referenceInfo['rel'] => $sourceRecordId, $referenceInfo['base'] => $destinationRecordId])->execute();
		}
		return (bool) $result;
	}

	/** {@inheritdoc} */
	public function create(int $sourceRecordId, int $destinationRecordId): bool
	{
		$relatedModuleName = $this->relationModel->getRelationModuleName();
		$parentModuleName = $this->relationModel->getParentModuleModel()->getName();
		$referenceInfo = \Vtiger_Relation_Model::getReferenceTableInfo($relatedModuleName, $parentModuleName);
		$result = false;
		$where = [$referenceInfo['rel'] => $destinationRecordId, $referenceInfo['base'] => $sourceRecordId];
		if ($relatedModuleName === $parentModuleName) {
			$where = ['or',	$where,		[$referenceInfo['rel'] => $sourceRecordId, $referenceInfo['base'] => $destinationRecordId]];
		}
		if (!(new App\Db\Query())->from($referenceInfo['table'])->where($where)->exists()) {
			$result = \App\Db::getInstance()->createCommand()->insert($referenceInfo['table'], [
				$referenceInfo['rel'] => $destinationRecordId,
				$referenceInfo['base'] => $sourceRecordId,
			])->execute();
		}
		return (bool) $result;
	}

	/** {@inheritdoc} */
	public function transfer(int $relatedRecordId, int $fromRecordId, int $toRecordId): bool
	{
		$relatedModuleName = $this->relationModel->getRelationModuleName();
		$parentModuleName = $this->relationModel->getParentModuleModel()->getName();
		$referenceInfo = \Vtiger_Relation_Model::getReferenceTableInfo($relatedModuleName, $parentModuleName);
		return (bool) \App\Db::getInstance()->createCommand()->update($referenceInfo['table'],
		[$referenceInfo['base'] => $toRecordId], [$referenceInfo['base'] => $fromRecordId, $referenceInfo['rel'] => $relatedRecordId])->execute();
	}
}
