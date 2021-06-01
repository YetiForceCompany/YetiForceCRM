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
 * Documents_GetRelatedRecord_Relation class.
 */
class Documents_GetRelatedRecord_Relation implements RelationInterface
{
	/**
	 * Name of the table that stores relations.
	 */
	public const TABLE_NAME = 'vtiger_senotesrel';

	/** {@inheritdoc} */
	public function getRelationType(): int
	{
		return Vtiger_Relation_Model::RELATION_M2M;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getQuery()
	{
		$queryGenerator = $this->relationModel->getQueryGenerator();
		$tableName = self::TABLE_NAME;
		$queryGenerator->addJoin(['INNER JOIN', $tableName, "{$tableName}.crmid = vtiger_crmentity.crmid"]);
		$queryGenerator->addNativeCondition(["{$tableName}.notesid" => $this->relationModel->get('parentRecord')->getId()]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(int $sourceRecordId, int $destinationRecordId): bool
	{
		return (bool) App\Db::getInstance()->createCommand()->delete(self::TABLE_NAME, [
			'notesid' => $sourceRecordId,
			'crmid' => $destinationRecordId
		])->execute();
	}

	/**
	 * {@inheritdoc}
	 */
	public function create(int $sourceRecordId, int $destinationRecordId): bool
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function transfer(int $relatedRecordId, int $fromRecordId, int $toRecordId): bool
	{
		return false;
	}
}
