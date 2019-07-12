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
 * Campaigns_GetCampaignsRecords_Relation class.
 */
class Campaigns_GetCampaignsRecords_Relation implements RelationInterface
{
	/**
	 * Name of the table that stores relations.
	 */
	public const TABLE_NAME = 'vtiger_campaign_records';

	/**
	 * {@inheritdoc}
	 */
	public function getQuery()
	{
		$queryGenerator = $this->relationModel->getQueryGenerator();
		$queryGenerator->addJoin(['INNER JOIN', self::TABLE_NAME, self::TABLE_NAME . '.crmid=' . $queryGenerator->getColumnName('id')])
			->addNativeCondition([self::TABLE_NAME . '.campaignid' => $this->relationModel->get('parentRecord')->getId()]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(int $sourceRecordId, int $destinationRecordId): bool
	{
		return (bool) App\Db::getInstance()->createCommand()
			->delete(self::TABLE_NAME, ['crmid' => $destinationRecordId, 'campaignid' => $sourceRecordId])
			->execute();
	}

	/**
	 * {@inheritdoc}
	 */
	public function create(int $sourceRecordId, int $destinationRecordId): bool
	{
		$data = ['campaignid' => $sourceRecordId, 'crmid' => $destinationRecordId];
		if (!(new \App\Db\Query())->from(self::TABLE_NAME)->where($data)->exists()) {
			$data['campaignrelstatusid'] = 0;
			$result = (bool) App\Db::getInstance()->createCommand()->insert(self::TABLE_NAME, $data)->execute();
		}
		return $result;
	}

	/**
	 * {@inheritdoc}
	 */
	public function transfer()
	{
	}
}
