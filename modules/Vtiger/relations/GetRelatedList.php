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
 * Vtiger_GetRelatedList_Relation class.
 */
class Vtiger_GetRelatedList_Relation extends \App\Relation\RelationAbstraction
{
	/**
	 * @var string Name of the table that stores relations.
	 */
	public const TABLE_NAME = 'vtiger_crmentityrel';

	/** {@inheritdoc} */
	public function getRelationType(): int
	{
		return Vtiger_Relation_Model::RELATION_M2M;
	}

	/** {@inheritdoc} */
	public function getQuery()
	{
		$record = $this->relationModel->get('parentRecord')->getId();
		$tableName = static::TABLE_NAME;
		$this->relationModel->getQueryGenerator()
			->addJoin(['INNER JOIN', $tableName, "({$tableName}.relcrmid = vtiger_crmentity.crmid OR {$tableName}.crmid = vtiger_crmentity.crmid)"])
			->addNativeCondition(['or', ["{$tableName}.crmid" => $record], ["{$tableName}.relcrmid" => $record]])
			->setDistinct('id');
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
		$tableName = static::TABLE_NAME;
		$advancedConditions = $queryGenerator->getAdvancedConditions();
		$relationQueryGenerator = $this->relationModel->getQueryGenerator();
		$relationQueryGenerator->setStateCondition($queryGenerator->getState());
		$relationQueryGenerator->setFields(['id']);
		if (!empty($advancedConditions['relationConditions'])) {
			$relationQueryGenerator->setConditions(\App\Condition::getConditionsFromRequest($advancedConditions['relationConditions']));
		}
		$query = $relationQueryGenerator->createQuery();
		$queryGenerator->addJoin(['INNER JOIN', $tableName, "({$tableName}.relcrmid = vtiger_crmentity.crmid OR {$tableName}.crmid = vtiger_crmentity.crmid)"])
			->addNativeCondition(['or', ["{$tableName}.crmid" => $query], ["{$tableName}.relcrmid" => $query]]);
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
		$tableName = static::TABLE_NAME;
		$queryGenerator->addJoin(['INNER JOIN', $tableName, "({$tableName}.relcrmid = vtiger_crmentity.crmid OR {$tableName}.crmid = vtiger_crmentity.crmid)"])
			->addNativeCondition(['or', ["{$tableName}.crmid" => $searchParam['value']], ["{$tableName}.relcrmid" => $searchParam['value']]]);
	}

	/** {@inheritdoc} */
	public function delete(int $sourceRecordId, int $destinationRecordId): bool
	{
		return (bool) App\Db::getInstance()->createCommand()->delete(static::TABLE_NAME, [
			'or',
			[
				'crmid' => $sourceRecordId,
				'relcrmid' => $destinationRecordId,
			],
			[
				'relcrmid' => $sourceRecordId,
				'crmid' => $destinationRecordId,
			],
		])->execute();
	}

	/** {@inheritdoc} */
	public function create(int $sourceRecordId, int $destinationRecordId): bool
	{
		$sourceModuleName = $this->relationModel->getParentModuleModel()->getName();
		$relModuleName = $this->relationModel->getRelationModuleName();
		$result = false;
		$data = ['crmid' => $sourceRecordId, 'module' => $sourceModuleName, 'relcrmid' => $destinationRecordId, 'relmodule' => $relModuleName];
		if (!(new \App\Db\Query())->from(static::TABLE_NAME)->where($data)->exists()) {
			$data['rel_created_user'] = \App\User::getCurrentUserRealId();
			$data['rel_created_time'] = date('Y-m-d H:i:s');
			$result = \App\Db::getInstance()->createCommand()->insert(static::TABLE_NAME, $data)->execute();
		}

		return $result;
	}

	/** {@inheritdoc} */
	public function transfer(int $relatedRecordId, int $fromRecordId, int $toRecordId): bool
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$count = $dbCommand->update(static::TABLE_NAME, ['crmid' => $toRecordId],
		['crmid' => $fromRecordId, 'relcrmid' => $relatedRecordId])->execute();
		return (bool) ($count + $dbCommand->update(static::TABLE_NAME, ['relcrmid' => $toRecordId],
				['relcrmid' => $fromRecordId, 'crmid' => $relatedRecordId])->execute());
	}
}
