<?php
/**
 * Includes GetRelatedContacts relation.
 *
 * @package   Relation
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
/**
 * Class GetRelatedContacts.
 */
class Vtiger_GetRelatedContacts_Relation extends Vtiger_GetRelatedList_Relation
{
	/**
	 * {@inheritdoc}
	 */
	public const TABLE_NAME = 'u_yf_relations_contacts_entity';

	/**
	 * {@inheritdoc}
	 */
	public function getQuery()
	{
		$record = $this->relationModel->get('parentRecord')->getId();
		$tableName = static::TABLE_NAME;
		$this->relationModel->getQueryGenerator()
			->addJoin(['INNER JOIN', $tableName, "({$tableName}.relcrmid = vtiger_crmentity.crmid OR {$tableName}.crmid = vtiger_crmentity.crmid)"])
			->addNativeCondition(['or', ["{$tableName}.crmid" => $record], ["{$tableName}.relcrmid" => $record]])
			->setCustomColumn(["{$tableName}.role_rel"])
			->setDistinct('id');
		return $this->relationModel->getQueryGenerator();
	}

	/**
	 * {@inheritdoc}
	 */
	public function create(int $sourceRecordId, int $destinationRecordId): bool
	{
		$result = false;
		if (!$this->getRelationData($sourceRecordId, $destinationRecordId)) {
			$result = \App\Db::getInstance()->createCommand()->insert(static::TABLE_NAME, ['crmid' => $sourceRecordId, 'relcrmid' => $destinationRecordId])->execute();
		}

		return $result;
	}

	/**
	 * updateRelationData function.
	 *
	 * @param int   $sourceRecordId
	 * @param int   $destinationRecordId
	 * @param array $updateData
	 *
	 * @return bool
	 */
	public function updateRelationData(int $sourceRecordId, int $destinationRecordId, array $updateData): bool
	{
		$conditions = [
			'or',
			['crmid' => $sourceRecordId, 'relcrmid' => $destinationRecordId],
			['crmid' => $destinationRecordId, 'relcrmid' => $sourceRecordId]
		];
		$result = (bool) $this->getRelationData($sourceRecordId, $destinationRecordId);
		if ($result) {
			$result = (bool) \App\Db::getInstance()->createCommand()->update(static::TABLE_NAME, $updateData, $conditions)->execute();
		}
		return $result;
	}

	/**
	 * Get relation data.
	 *
	 * @param int $sourceRecordId
	 * @param int $destinationRecordId
	 *
	 * @return array
	 */
	public function getRelationData(int $sourceRecordId, int $destinationRecordId)
	{
		return (new \App\Db\Query())->from(static::TABLE_NAME)->where([
			'or',
			['crmid' => $sourceRecordId, 'relcrmid' => $destinationRecordId],
			['crmid' => $destinationRecordId, 'relcrmid' => $sourceRecordId]
		])->one();
	}
}
