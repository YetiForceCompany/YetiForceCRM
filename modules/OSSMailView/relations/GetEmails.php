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
use App\RelationInterface;

/**
 * OSSMailView_GetEmails_Relation class.
 */
class OSSMailView_GetEmails_Relation implements RelationInterface
{
	/**
	 * Name of the table that stores relations.
	 */
	public const TABLE_NAME = 'vtiger_ossmailview_relation';

	/**
	 * {@inheritdoc}
	 */
	public function getQuery()
	{
		$this->relationModel->getQueryGenerator()
			->addJoin(['INNER JOIN', self::TABLE_NAME, self::TABLE_NAME . '.ossmailviewid = vtiger_ossmailview.ossmailviewid'])
			->addNativeCondition([self::TABLE_NAME . '.crmid' => $this->relationModel->get('parentRecord')->getId()]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(int $sourceRecordId, int $destinationRecordId): bool
	{
		return (bool) \App\Db::getInstance()->createCommand()->delete(self::TABLE_NAME, ['crmid' => $sourceRecordId, 'ossmailviewid' => $destinationRecordId])->execute();
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
	public function transfer()
	{
	}
}
