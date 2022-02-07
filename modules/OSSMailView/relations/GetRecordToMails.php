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
 * OSSMailView_GetRecordToMails_Relation class.
 */
class OSSMailView_GetRecordToMails_Relation extends \App\Relation\RelationAbstraction
{
	/**
	 * @var string Name of the table that stores relations.
	 */
	public const TABLE_NAME = 'vtiger_ossmailview_relation';

	/** {@inheritdoc} */
	public function getRelationType(): int
	{
		return Vtiger_Relation_Model::RELATION_M2M;
	}

	/** {@inheritdoc} */
	public function getQuery()
	{
		$this->relationModel->getQueryGenerator()
			->addJoin(['INNER JOIN', self::TABLE_NAME, self::TABLE_NAME . '.crmid = vtiger_crmentity.crmid'])
			->addNativeCondition([self::TABLE_NAME . '.ossmailviewid' => $this->relationModel->get('parentRecord')->getId()]);
	}

	/** {@inheritdoc} */
	public function delete(int $sourceRecordId, int $destinationRecordId): bool
	{
		return (bool) \App\Db::getInstance()->createCommand()->delete(self::TABLE_NAME, ['crmid' => $destinationRecordId, 'ossmailviewid' => $sourceRecordId])->execute();
	}

	/** {@inheritdoc} */
	public function create(int $sourceRecordId, int $destinationRecordId): bool
	{
		$return = false;
		$data = ['ossmailviewid' => $sourceRecordId, 'crmid' => $destinationRecordId];
		if (!$this->isExists($data)) {
			$date = $this->date ?? \Vtiger_Record_Model::getInstanceById($sourceRecordId, 'OSSMailView')->get('date');
			$return = $this->addToDB(array_merge($data, ['date' => $date]));
			if ($return && ($parentId = \Users_Privileges_Model::getParentRecord($destinationRecordId))) {
				$data['crmid'] = $parentId;
				if ($this->addRelation($data, $date) && ($parentId = \Users_Privileges_Model::getParentRecord($parentId))) {
					$data['crmid'] = $parentId;
					$this->addRelation($data, $date);
				}
			}
		}
		return (bool) $return;
	}

	/**
	 * Check if relation exists.
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	public function isExists(array $data): bool
	{
		return (bool) (new \App\Db\Query())->from(self::TABLE_NAME)->where($data)->exists();
	}

	/**
	 * Add relation to DB.
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	public function addToDB(array $data): bool
	{
		return (bool) \App\Db::getInstance()->createCommand()->insert(self::TABLE_NAME, $data)->execute();
	}

	/**
	 * Add relation if exists.
	 *
	 * @param array  $data
	 * @param string $date
	 *
	 * @return bool
	 */
	public function addRelation(array $data, string $date): bool
	{
		$result = false;
		if (!$this->isExists($data)) {
			$data['date'] = $date;
			$result = $this->addToDB($data);
		}
		return $result;
	}

	/** {@inheritdoc} */
	public function transfer(int $relatedRecordId, int $fromRecordId, int $toRecordId): bool
	{
		return (bool) \App\Db::getInstance()->createCommand()->update(self::TABLE_NAME,
		['ossmailviewid' => $toRecordId], ['ossmailviewid' => $fromRecordId, 'crmid' => $relatedRecordId])->execute();
	}
}
