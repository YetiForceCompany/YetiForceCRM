<?php
/**
 * Main file that includes basic operations on relations.
 *
 * @package   Relation
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Accounts_GetCampaigns_Relation class.
 */
class Accounts_GetCampaigns_Relation extends Campaigns_GetCampaigns_Relation
{
	/** {@inheritdoc} */
	public function delete(int $sourceRecordId, int $destinationRecordId): bool
	{
		return (bool) App\Db::getInstance()->createCommand()->delete(self::TABLE_NAME, ['and',
			['campaignid' => $destinationRecordId],
			['or',
				['crmid' => $sourceRecordId],
				['crmid' => (new \App\Db\Query())->select(['contactid'])->from('vtiger_contactdetails')->where(['parentid' => $sourceRecordId])]
			]
		])->execute();
	}

	/** {@inheritdoc} */
	public function create(int $sourceRecordId, int $destinationRecordId): bool
	{
		$result = false;
		$data = ['campaignid' => $destinationRecordId, 'crmid' => $sourceRecordId];
		if (!(new \App\Db\Query())->from(self::TABLE_NAME)->where($data)->exists()) {
			$data['campaignrelstatusid'] = 1;
			$result = (bool) App\Db::getInstance()->createCommand()->insert(self::TABLE_NAME, $data)->execute();
		}

		return $result;
	}
}
