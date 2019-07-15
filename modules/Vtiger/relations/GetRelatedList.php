<?php
/**
 * Main file that includes basic operations on relations.
 *
 * @package   Relation
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
use App\Relation\RelationInterface;

/**
 * Vtiger_GetRelatedList_Relation class.
 */
class Vtiger_GetRelatedList_Relation implements RelationInterface
{
	/**
	 * Name of the table that stores relations.
	 */
	public const TABLE_NAME = 'vtiger_crmentityrel';

	/**
	 * {@inheritdoc}
	 */
	public function getQuery()
	{
		$record = $this->relationModel->get('parentRecord')->getId();
		$tableName = self::TABLE_NAME;
		$this->relationModel->getQueryGenerator()
			->addJoin(['INNER JOIN', $tableName, "({$tableName}.relcrmid = vtiger_crmentity.crmid OR {$tableName}.crmid = vtiger_crmentity.crmid)"])
			->addNativeCondition(['or', ["{$tableName}.crmid" => $record], ["{$tableName}.relcrmid" => $record]])
			->setDistinct('id');
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(int $sourceRecordId, int $destinationRecordId): bool
	{
		return (bool) App\Db::getInstance()->createCommand()->delete(self::TABLE_NAME, [
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

	/**
	 * {@inheritdoc}
	 */
	public function create(int $sourceRecordId, int $destinationRecordId): bool
	{
		$sourceModuleName = $this->relationModel->getParentModuleModel()->getName();
		$relModuleName = $this->relationModel->getRelationModuleName();
		$result = false;
		$data = ['crmid' => $sourceRecordId, 'module' => $sourceModuleName, 'relcrmid' => $destinationRecordId, 'relmodule' => $relModuleName];
		if (!(new \App\Db\Query())->from(self::TABLE_NAME)->where($data)->exists()) {
			$data['rel_created_user'] = \App\User::getCurrentUserRealId();
			$data['rel_created_time'] = date('Y-m-d H:i:s');
			$result = \App\Db::getInstance()->createCommand()->insert(self::TABLE_NAME, $data)->execute();
		}

		return $result;
	}

	/**
	 * {@inheritdoc}
	 */
	public function transfer(int $relatedRecordId, int $fromRecordId, int $toRecordId): bool
	{
		$dbCommand = \App\Db::getInstance()->createCommand();
		$count = $dbCommand->update(self::TABLE_NAME, ['crmid' => $toRecordId],
		['crmid' => $fromRecordId, 'relcrmid' => $relatedRecordId])->execute();
		return (bool) ($count + $dbCommand->update(self::TABLE_NAME, ['relcrmid' => $toRecordId],
				['relcrmid' => $fromRecordId, 'crmid' => $relatedRecordId])->execute());
	}
}
