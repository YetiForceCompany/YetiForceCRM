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
 * Documents_GetAttachments_Relation class.
 */
class Documents_GetAttachments_Relation implements RelationInterface
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
		$queryGenerator->setCustomColumn('vtiger_notes.filetype');
		$tableName = self::TABLE_NAME;
		$queryGenerator->addJoin(['INNER JOIN', $tableName, "{$tableName}.notesid= vtiger_notes.notesid"]);
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_crmentity crm2', "crm2.crmid = {$tableName}.crmid"]);
		$queryGenerator->addNativeCondition(['crm2.crmid' => $this->relationModel->get('parentRecord')->getId()]);
		$queryGenerator->setOrder('id', 'DESC');
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(int $sourceRecordId, int $destinationRecordId): bool
	{
		$data = ['notesid' => $destinationRecordId, 'crmid' => $sourceRecordId];
		if ($this->relationModel && 'Accounts' === $this->relationModel->getParentModuleModel()->getName()) {
			$subQuery = (new \App\Db\Query())->select(['contactid'])->from('vtiger_contactdetails')->where(['parentid' => $sourceRecordId]);
			$data = ['or', $data, ['crmid' => $subQuery] + $data];
		}
		return (bool) App\Db::getInstance()->createCommand()->delete(self::TABLE_NAME, $data)->execute();
	}

	/**
	 * {@inheritdoc}
	 */
	public function create(int $sourceRecordId, int $destinationRecordId): bool
	{
		$result = false;
		$data = ['notesid' => $destinationRecordId, 'crmid' => $sourceRecordId];
		if (!(new \App\Db\Query())->from(self::TABLE_NAME)->where($data)->exists()) {
			$result = \App\Db::getInstance()->createCommand()->insert(self::TABLE_NAME, $data)->execute();
		}
		return (bool) $result;
	}

	/**
	 * {@inheritdoc}
	 */
	public function transfer(int $relatedRecordId, int $fromRecordId, int $toRecordId): bool
	{
		return (bool) \App\Db::getInstance()->createCommand()->update(self::TABLE_NAME,
		['crmid' => $toRecordId], ['crmid' => $fromRecordId, 'notesid' => $relatedRecordId])->execute();
	}
}
