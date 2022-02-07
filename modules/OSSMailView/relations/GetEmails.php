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
 * OSSMailView_GetEmails_Relation class.
 */
class OSSMailView_GetEmails_Relation extends \App\Relation\RelationAbstraction
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
			->addJoin(['INNER JOIN', self::TABLE_NAME, self::TABLE_NAME . '.ossmailviewid = vtiger_ossmailview.ossmailviewid'])
			->addNativeCondition([self::TABLE_NAME . '.crmid' => $this->relationModel->get('parentRecord')->getId()]);
	}

	/** {@inheritdoc} */
	public function delete(int $sourceRecordId, int $destinationRecordId): bool
	{
		return (bool) \App\Db::getInstance()->createCommand()->delete(self::TABLE_NAME, ['crmid' => $sourceRecordId, 'ossmailviewid' => $destinationRecordId])->execute();
	}

	/** {@inheritdoc} */
	public function create(int $sourceRecordId, int $destinationRecordId): bool
	{
		return false;
	}

	/** {@inheritdoc} */
	public function transfer(int $relatedRecordId, int $fromRecordId, int $toRecordId): bool
	{
		$result = $this->updateDB($toRecordId, ['crmid' => $fromRecordId, 'ossmailviewid' => $relatedRecordId]);
		if ($result && $parentId = \Users_Privileges_Model::getParentRecord($toRecordId)) {
			$parentIdFromRecordId = \Users_Privileges_Model::getParentRecord($fromRecordId);
			$data = ['ossmailviewid' => $relatedRecordId, 'crmid' => $parentIdFromRecordId];
			if ($parentIdFromRecordId && $this->isExists($data)) {
				$this->updateDB($parentId, $data);
			} else {
				$date = \Vtiger_Record_Model::getInstanceById($relatedRecordId, 'OSSMailView')->get('date');
				$this->addToDB(['crmid' => $parentId, 'date' => $date] + $data);
			}
			$parentId = \Users_Privileges_Model::getParentRecord($parentId);
			if ($parentId) {
				if ($parentIdFromRecordId && ($parentIdFromRecordId = \Users_Privileges_Model::getParentRecord($parentIdFromRecordId)) && $this->isExists(['crmid' => $parentIdFromRecordId] + $data)) {
					$this->updateDB($parentId, ['crmid' => $parentIdFromRecordId] + $data);
				} else {
					$date = \Vtiger_Record_Model::getInstanceById($relatedRecordId, 'OSSMailView')->get('date');
					$this->addToDB(['crmid' => $parentId, 'date' => $date] + $data);
				}
			}
		}
		return $result;
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
		$result = true;
		if (!$this->isExists(['ossmailviewid' => $data['ossmailviewid'], 'crmid' => $data['crmid']])) {
			$result = (bool) \App\Db::getInstance()->createCommand()->insert(self::TABLE_NAME, $data)->execute();
		}
		return $result;
	}

	/**
	 * Update relation.
	 *
	 * @param int   $toRecordId
	 * @param array $where
	 *
	 * @return bool
	 */
	public function updateDB(int $toRecordId, array $where): bool
	{
		$result = true;
		if (!$this->isExists(['ossmailviewid' => $where['ossmailviewid'], 'crmid' => $toRecordId])) {
			$result = (bool) \App\Db::getInstance()->createCommand()->update(self::TABLE_NAME, ['crmid' => $toRecordId], $where)->execute();
		}
		return $result;
	}
}
