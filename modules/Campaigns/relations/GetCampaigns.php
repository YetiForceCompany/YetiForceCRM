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
 * Campaigns_GetCampaigns_Relation class.
 */
class Campaigns_GetCampaigns_Relation extends \App\Relation\RelationAbstraction
{
	/**
	 * @var string Name of the table that stores relations.
	 */
	public const TABLE_NAME = 'vtiger_campaign_records';

	/** {@inheritdoc} */
	public function getRelationType(): int
	{
		return Vtiger_Relation_Model::RELATION_M2M;
	}

	/** {@inheritdoc} */
	public function getQuery()
	{
		$this->relationModel->getQueryGenerator()
			->addJoin(['INNER JOIN', self::TABLE_NAME, self::TABLE_NAME . '.campaignid=vtiger_campaign.campaignid'])
			->addNativeCondition([self::TABLE_NAME . '.crmid' => $this->relationModel->get('parentRecord')->getId()]);
	}

	/** {@inheritdoc} */
	public function delete(int $sourceRecordId, int $destinationRecordId): bool
	{
		return (bool) App\Db::getInstance()->createCommand()
			->delete(self::TABLE_NAME, ['crmid' => $sourceRecordId, 'campaignid' => $destinationRecordId])
			->execute();
	}

	/** {@inheritdoc} */
	public function create(int $sourceRecordId, int $destinationRecordId): bool
	{
		$result = false;
		$data = ['campaignid' => $destinationRecordId, 'crmid' => $sourceRecordId];
		if (!(new \App\Db\Query())->from(self::TABLE_NAME)->where($data)->exists()) {
			$data['campaignrelstatusid'] = 0;
			$result = (bool) App\Db::getInstance()->createCommand()->insert(self::TABLE_NAME, $data)->execute();
		}

		return $result;
	}

	/** {@inheritdoc} */
	public function transfer(int $relatedRecordId, int $fromRecordId, int $toRecordId): bool
	{
		return (bool) \App\Db::getInstance()->createCommand()->update(self::TABLE_NAME, ['crmid' => $toRecordId], [
			'crmid' => $fromRecordId, 'campaignid' => $relatedRecordId,
		])->execute();
	}
}
