<?php
/**
 * Main file that includes basic operations on relations.
 *
 * @package   Relation
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Vtiger_GetManyToMany_Relation class.
 */
class Vtiger_GetManyToMany_Relation extends \App\Relation\RelationAbstraction
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
				$referenceInfo['table'], "({$referenceInfo['table']}.{$referenceInfo['base']} = vtiger_crmentity.crmid OR {$referenceInfo['table']}.{$referenceInfo['rel']} = vtiger_crmentity.crmid)",
			])->addNativeCondition([
				'or',
				[$referenceInfo['table'] . '.' . $referenceInfo['base'] => $recordId],
				[$referenceInfo['table'] . '.' . $referenceInfo['rel'] => $recordId],
			])->addCondition('id', $recordId, 'n');
		} else {
			$queryGenerator->addJoin(['INNER JOIN', $referenceInfo['table'], "{$referenceInfo['table']}.{$referenceInfo['rel']} = vtiger_crmentity.crmid"])
				->addNativeCondition([$referenceInfo['table'] . '.' . $referenceInfo['base'] => $recordId]);
		}
	}

	/**
	 * Load advanced conditions for filtering related records.
	 *
	 * @param App\QueryGenerator $queryGenerator QueryGenerator for the list of records to be tapered based on the relationship
	 *
	 * @return void
	 */
	public function loadAdvancedConditionsByRelationId(App\QueryGenerator $queryGenerator): void
	{
		$advancedConditions = $queryGenerator->getAdvancedConditions();
		$relationQueryGenerator = $this->relationModel->getQueryGenerator();
		$relationQueryGenerator->setStateCondition($queryGenerator->getState());
		$relationQueryGenerator->setFields(['id']);
		if (!empty($advancedConditions['relationConditions'])) {
			$relationQueryGenerator->setConditions(\App\Condition::getConditionsFromRequest($advancedConditions['relationConditions']));
		}
		$query = $relationQueryGenerator->createQuery();

		$relatedModuleName = $this->relationModel->getRelationModuleName();
		$parentModuleName = $this->relationModel->getParentModuleModel()->getName();
		$referenceInfo = \Vtiger_Relation_Model::getReferenceTableInfo($relatedModuleName, $parentModuleName);
		if ($relatedModuleName === $parentModuleName) {
			$queryGenerator->addJoin([
				'INNER JOIN',
				$referenceInfo['table'], "({$referenceInfo['table']}.{$referenceInfo['base']} = vtiger_crmentity.crmid OR {$referenceInfo['table']}.{$referenceInfo['rel']} = vtiger_crmentity.crmid)",
			])->addNativeCondition([
				'or',
				[$referenceInfo['table'] . '.' . $referenceInfo['base'] => $query],
				[$referenceInfo['table'] . '.' . $referenceInfo['rel'] => $query],
			]);
		} else {
			$queryGenerator->addJoin(['INNER JOIN', $referenceInfo['table'], "{$referenceInfo['table']}.{$referenceInfo['base']} = vtiger_crmentity.crmid"])
				->addNativeCondition([$referenceInfo['table'] . '.' . $referenceInfo['rel'] => $query]);
		}
	}

	/**
	 * Load advanced conditions relationship by custom column.
	 *
	 * @param App\QueryGenerator $queryGenerator QueryGenerator for the list of records to be tapered based on the relationship
	 * @param array              $searchParam    Related record for which we are filtering the list of records
	 *
	 * @return void
	 */
	public function loadAdvancedConditionsByColumns(App\QueryGenerator $queryGenerator, array $searchParam): void
	{
		$recordId = $searchParam['value'];
		$relatedModuleName = $this->relationModel->getRelationModuleName();
		$parentModuleName = $this->relationModel->getParentModuleModel()->getName();
		$referenceInfo = \Vtiger_Relation_Model::getReferenceTableInfo($relatedModuleName, $parentModuleName);
		if ($relatedModuleName === $parentModuleName) {
			$queryGenerator->addJoin([
				'INNER JOIN',
				$referenceInfo['table'], "({$referenceInfo['table']}.{$referenceInfo['base']} = vtiger_crmentity.crmid OR {$referenceInfo['table']}.{$referenceInfo['rel']} = vtiger_crmentity.crmid)",
			])->addNativeCondition([
				'or',
				[$referenceInfo['table'] . '.' . $referenceInfo['base'] => $recordId],
				[$referenceInfo['table'] . '.' . $referenceInfo['rel'] => $recordId],
			])->addCondition('id', $recordId, 'n');
		} else {
			$queryGenerator->addJoin(['INNER JOIN', $referenceInfo['table'], "{$referenceInfo['table']}.{$referenceInfo['base']} = vtiger_crmentity.crmid"])
				->addNativeCondition([$referenceInfo['table'] . '.' . $referenceInfo['rel'] => $recordId]);
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
