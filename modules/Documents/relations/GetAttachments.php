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
 * Documents_GetAttachments_Relation class.
 */
class Documents_GetAttachments_Relation extends \App\Relation\RelationAbstraction
{
	/**
	 * @var string Name of the table that stores relations.
	 */
	public const TABLE_NAME = 'vtiger_senotesrel';

	/** {@inheritdoc} */
	public function getRelationType(): int
	{
		return Vtiger_Relation_Model::RELATION_M2M;
	}

	/** {@inheritdoc} */
	public function getQuery()
	{
		$queryGenerator = $this->relationModel->getQueryGenerator();
		$queryGenerator->setCustomColumn('vtiger_notes.filetype');
		$tableName = self::TABLE_NAME;
		$queryGenerator->addJoin(['INNER JOIN', $tableName, "{$tableName}.notesid= vtiger_notes.notesid"]);
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_crmentity crm2', "crm2.crmid = {$tableName}.crmid"]);
		$queryGenerator->addNativeCondition(['crm2.crmid' => $this->relationModel->get('parentRecord')->getId()]);
		$queryGenerator->setOrder('id', 'DESC');
	}

	/**
	 * Load advanced conditions for filtering related records.
	 *
	 * @param App\QueryGenerator $queryGenerator QueryGenerator for the list of records to be tapered based on the relationship
	 * @param array              $searchParam
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
		$queryGenerator->addJoin(['INNER JOIN', $tableName, "{$tableName}.crmid = vtiger_crmentity.crmid"])
			->addNativeCondition(["{$tableName}.notesid" => $query]);
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
		$queryGenerator->addJoin(['INNER JOIN', $tableName, "{$tableName}.crmid = vtiger_crmentity.crmid"])
			->addNativeCondition(["{$tableName}.notesid" => $searchParam['value']]);
	}

	/** {@inheritdoc} */
	public function delete(int $sourceRecordId, int $destinationRecordId): bool
	{
		$data = ['notesid' => $destinationRecordId, 'crmid' => $sourceRecordId];
		if ($this->relationModel && 'Accounts' === $this->relationModel->getParentModuleModel()->getName()) {
			$subQuery = (new \App\Db\Query())->select(['contactid'])->from('vtiger_contactdetails')->where(['parentid' => $sourceRecordId]);
			$data = ['or', $data, ['crmid' => $subQuery] + $data];
		}
		return (bool) App\Db::getInstance()->createCommand()->delete(self::TABLE_NAME, $data)->execute();
	}

	/** {@inheritdoc} */
	public function create(int $sourceRecordId, int $destinationRecordId): bool
	{
		$result = false;
		$data = ['notesid' => $destinationRecordId, 'crmid' => $sourceRecordId];
		if (!(new \App\Db\Query())->from(self::TABLE_NAME)->where($data)->exists()) {
			$result = \App\Db::getInstance()->createCommand()->insert(self::TABLE_NAME, $data)->execute();
		}
		return (bool) $result;
	}

	/** {@inheritdoc} */
	public function transfer(int $relatedRecordId, int $fromRecordId, int $toRecordId): bool
	{
		return (bool) \App\Db::getInstance()->createCommand()->update(self::TABLE_NAME,
		['crmid' => $toRecordId], ['crmid' => $fromRecordId, 'notesid' => $relatedRecordId])->execute();
	}
}
