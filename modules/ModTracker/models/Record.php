<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class ModTracker_Record_Model extends Vtiger_Record_Model
{
	const UPDATE = 0;
	const DELETE = 1;
	const CREATE = 2;
	const ACTIVE = 3;
	const LINK = 4;
	const UNLINK = 5;
	const CONVERTTOACCOUNT = 6;
	const DISPLAYED = 7;
	const ARCHIVED = 8;
	const REMOVED = 9;
	const TRANSFER_EDIT = 10;
	const TRANSFER_DELETE = 11;
	const TRANSFER_UNLINK = 12;
	const TRANSFER_LINK = 13;
	const SHOW_HIDDEN_DATA = 14;

	/**
	 * Status labels.
	 *
	 * @var string[]
	 */
	public static $statusLabel = [
		0 => 'LBL_UPDATED',
		1 => 'LBL_DELETED',
		2 => 'LBL_CREATED',
		3 => 'LBL_ACTIVE',
		4 => 'LBL_ADDED',
		5 => 'LBL_UNLINK',
		6 => 'LBL_CONVERTED_FROM_LEAD',
		7 => 'LBL_DISPLAYED',
		8 => 'LBL_ARCHIVED',
		9 => 'LBL_REMOVED',
		10 => 'LBL_TRANSFER_EDIT',
		11 => 'LBL_TRANSFER_DELETE',
		12 => 'LBL_TRANSFER_UNLINK',
		13 => 'LBL_TRANSFER_LINK',
		14 => 'LBL_SHOW_HIDDEN_DATA',
	];

	/**
	 * Function to get the history of updates on a record.
	 *
	 * @param int                 $parentRecordId
	 * @param Vtiger_Paging_Model $pagingModel
	 * @param string              $type
	 * @param int|null            $startWith
	 *
	 * @return self[] - list of  ModTracker_Record_Model
	 */
	public static function getUpdates(int $parentRecordId, Vtiger_Paging_Model $pagingModel, string $type, ?int $startWith = null)
	{
		$recordInstances = [];
		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();
		$where = self::getConditionByType($type);
		$query = (new \App\Db\Query())
			->from('vtiger_modtracker_basic')
			->where(['crmid' => $parentRecordId])
			->andWhere($where)
			->limit($pageLimit)
			->offset($startIndex)
			->orderBy(['changedon' => SORT_DESC]);
		if (!empty($startWith)) {
			$query->andWhere(['>=', 'id', $startWith]);
		}
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$recordInstance = new self();
			$recordInstance->setData($row)->setParent($row['crmid'], $row['module']);
			$recordInstances[] = $recordInstance;
		}
		$dataReader->close();
		return $recordInstances;
	}

	public static function setLastReviewed($recordId)
	{
		$row = (new App\Db\Query())->select(['last_reviewed_users', 'id'])
			->from('vtiger_modtracker_basic')
			->where(['crmid' => $recordId])
			->andWhere(['<>', 'status', self::DISPLAYED])
			->orderBy(['changedon' => SORT_DESC, 'id' => SORT_DESC])
			->limit(1)
			->one();
		if ($row) {
			$lastReviewedUsers = explode('#', $row['last_reviewed_users']);
			$lastReviewedUsers[] = Users_Record_Model::getCurrentUserModel()->getRealId();
			\App\Db::getInstance()->createCommand()
				->update('vtiger_modtracker_basic', ['last_reviewed_users' => '#' . implode('#', array_filter($lastReviewedUsers)) . '#'], ['id' => $row['id']])
				->execute();

			return $row['id'];
		}
		return false;
	}

	public static function unsetReviewed($recordId, $userId = false, $exception = false)
	{
		if (!$userId) {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$userId = $currentUser->getRealId();
		}
		$query = new \App\Db\Query();
		$query->select(['last_reviewed_users', 'id'])->from('vtiger_modtracker_basic')->where(['crmid' => $recordId])
			->andWhere(['<>', 'status', self::DISPLAYED])->andWhere(['like', 'last_reviewed_users', "#$userId#"])->orderBy(['changedon' => SORT_DESC, 'id' => SORT_DESC])->limit(1);
		if ($exception) {
			$query->andWhere(['<>', 'id', $exception]);
		}
		$row = $query->one();
		if ($row) {
			$lastReviewedUsers = array_filter(explode('#', $row['last_reviewed_users']));
			$key = array_search($userId, $lastReviewedUsers);
			unset($lastReviewedUsers[$key]);
			$value = empty($lastReviewedUsers) ? '' : '#' . implode('#', array_filter($lastReviewedUsers)) . '#';

			return App\Db::getInstance()->createCommand()->update('vtiger_modtracker_basic', ['last_reviewed_users' => $value], ['id' => $row['id']])->execute();
		}
		return false;
	}

	/**
	 * Checks if is new changes.
	 *
	 * @param int $recordId
	 * @param int $userId
	 *
	 * @return bool
	 */
	public static function isNewChange(int $recordId, int $userId = 0): bool
	{
		if (0 === $userId) {
			$userId = App\User::getCurrentUserId();
		}
		$lastReviewedUsers = (new \App\Db\Query())->select(['last_reviewed_users'])->from('vtiger_modtracker_basic')
			->where(['crmid' => $recordId])
			->andWhere(['<>', 'status', self::DISPLAYED])->orderBy(['changedon' => SORT_DESC, 'id' => SORT_DESC])->scalar();
		if (false !== $lastReviewedUsers) {
			return false === strpos($lastReviewedUsers, "#$userId#");
		}
		return true;
	}

	/**
	 * Gets unreviewed entries.
	 *
	 * @param int|int[] $recordsId
	 * @param bool|int  $userId
	 * @param bool      $sort
	 *
	 * @return array
	 */
	public static function getUnreviewed($recordsId, $userId = false, $sort = false)
	{
		if (false === $userId) {
			$userId = \App\User::getCurrentUserId();
		}
		$query = (new \App\Db\Query())->select(['crmid', 'u' => 'last_reviewed_users'])->from('vtiger_modtracker_basic')
			->where(['crmid' => $recordsId])
			->andWhere(['<>', 'status', self::DISPLAYED]);
		if ($sort) {
			$query->addSelect(['vtiger_ossmailview.type'])
				->leftJoin('vtiger_modtracker_relations', 'vtiger_modtracker_basic.id = vtiger_modtracker_relations.id')
				->leftJoin('vtiger_ossmailview', 'vtiger_modtracker_relations.targetid = vtiger_ossmailview.ossmailviewid')
				->orderBy('vtiger_modtracker_basic.crmid ,vtiger_modtracker_basic.id DESC');
		}
		$dataReader = $query->createCommand()->query();
		$changes = [];
		while ($row = $dataReader->read()) {
			$changes[$row['crmid']][] = $row;
		}
		$dataReader->close();
		$unreviewed = [];
		foreach ($changes as $crmId => $rows) {
			$all = $mails = 0;
			foreach ($rows as $row) {
				if (false !== strpos($row['u'], "#$userId#")) {
					break;
				}
				if (isset($row['type']) && 1 === (int) $row['type']) {
					++$mails;
				} elseif (!isset($row['type'])) {
					++$all;
				}
			}
			$unreviewed[$crmId]['a'] = $all;
			$unreviewed[$crmId]['m'] = $mails;
		}
		return $unreviewed;
	}

	/**
	 * Function to get the name of the module to which the record belongs.
	 *
	 * @return Vtiger_Module_Model
	 */
	public function getModule(): Vtiger_Module_Model
	{
		if (empty($this->parent)) {
			return Vtiger_Module_Model::getInstance($this->getModuleName());
		}
		return $this->getParent()->getModule();
	}

	/**
	 * Function to get the name of the module to which the record belongs.
	 *
	 * @return string - Record Module Name
	 */
	public function getModuleName(): string
	{
		return $this->get('module');
	}

	/**
	 * Function to get the Detail View url for the record.
	 *
	 * @return string - Record Detail View Url
	 */
	public function getDetailViewUrl()
	{
		$moduleName = $this->getModuleName();
		switch ($moduleName) {
			case 'Documents':
				return 'file.php?module=Documents&action=DownloadFile&record=' . $this->get('crmid');
			case 'OSSMailView':
				$action = 'view=preview';
				break;
			default:
				$action = 'view=Detail';
				break;
		}
		return "index.php?module=$moduleName&$action&record=" . $this->get('crmid');
	}

	/**
	 * Undocumented function.
	 *
	 * @param int    $id
	 * @param string $moduleName
	 *
	 * @return $this
	 */
	public function setParent($id, $moduleName)
	{
		$this->parent = Vtiger_Record_Model::getInstanceById($id, $moduleName);
		return $this;
	}

	public function getParent()
	{
		return $this->parent;
	}

	public function checkStatus($callerStatus)
	{
		$status = $this->get('status');
		if ($status == $callerStatus) {
			return true;
		}
		return false;
	}

	public function isConvertToAccount()
	{
		return $this->checkStatus(self::CONVERTTOACCOUNT);
	}

	public function isCreate()
	{
		return $this->checkStatus(self::CREATE);
	}

	public function isUpdate()
	{
		return $this->checkStatus(self::UPDATE);
	}

	public function isRelationLink()
	{
		return $this->checkStatus(self::LINK);
	}

	public function isRelationUnLink()
	{
		return $this->checkStatus(self::UNLINK);
	}

	public function isDisplayed()
	{
		return $this->checkStatus(self::DISPLAYED);
	}

	/**
	 * Function check if status is Transfer.
	 *
	 * @return bool
	 */
	public function isTransferEdit()
	{
		return $this->checkStatus(static::TRANSFER_EDIT);
	}

	/**
	 * Function check if status is Transfer.
	 *
	 * @return bool
	 */
	public function isTransferLink()
	{
		return $this->checkStatus(static::TRANSFER_LINK);
	}

	/**
	 * Function check if status is Transfer.
	 *
	 * @return bool
	 */
	public function isTransferUnLink()
	{
		return $this->checkStatus(static::TRANSFER_UNLINK);
	}

	/**
	 * Function check if status is Transfer.
	 *
	 * @return bool
	 */
	public function isTransferDelete()
	{
		return $this->checkStatus(static::TRANSFER_DELETE);
	}

	/**
	 * Function check if status is Transfer.
	 *
	 * @return bool
	 */
	public function isShowHiddenData()
	{
		return $this->checkStatus(static::SHOW_HIDDEN_DATA);
	}

	/**
	 * Has changed state.
	 *
	 * @return bool
	 */
	public function isChangeState()
	{
		return \in_array($this->get('status'), [1, 3, 8]);
	}

	public function isReviewed($userId = false)
	{
		if (false === $userId) {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$userId = $currentUser->getId();
		}
		$reviewed = $this->get('last_reviewed_users');
		if (empty($reviewed)) {
			return false;
		}
		return false !== strpos($reviewed, "#$userId#");
	}

	/**
	 * Get status label.
	 *
	 * @return string
	 */
	public function getStatusLabel()
	{
		return static::$statusLabel[$this->get('status')];
	}

	/**
	 * Get the modifier object.
	 *
	 * @return \App\User
	 */
	public function getModifiedBy()
	{
		return \App\User::getUserModel($this->get('whodid'));
	}

	/**
	 * Get name for modifier by.
	 *
	 * @return string|bool
	 */
	public function getModifierName()
	{
		return \App\Fields\Owner::getUserLabel($this->get('whodid'));
	}

	public function getDisplayActivityTime()
	{
		$time = $this->getActivityTime();
		$time = new DateTimeField($time);

		return $time->getFullcalenderDateTimevalue();
	}

	public function getActivityTime()
	{
		return $this->get('changedon');
	}

	/**
	 * Function return Modtracker Field Model.
	 *
	 * @return \ModTracker_Field_Model[]
	 */
	public function getFieldInstances()
	{
		$fieldInstances = [];
		if ($this->isCreate() || $this->isUpdate() || $this->isTransferEdit()) {
			$dataReader = (new \App\Db\Query())->from('vtiger_modtracker_detail')->where(['id' => $this->get('id')])->createCommand()->query();
			while ($row = $dataReader->read()) {
				$row['prevalue'] = html_entity_decode((string) $row['prevalue']);
				$row['postvalue'] = html_entity_decode((string) $row['postvalue']);
				if ('record_id' === $row['fieldname'] || 'record_module' === $row['fieldname']) {
					continue;
				}
				if (!($fieldModel = $this->getModule()->getFieldByName($row['fieldname']))) {
					continue;
				}
				$fieldInstance = new ModTracker_Field_Model();
				$fieldInstance->setData($row)->setParent($this->getParent())->setFieldInstance($fieldModel);
				$fieldInstances[] = $fieldInstance;
			}
			$dataReader->close();
		}
		return $fieldInstances;
	}

	/**
	 * Gets inventory changes.
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	public function getInventoryChanges()
	{
		if (!isset($this->inventoryChanges)) {
			$changes = [];
			if ($this->isCreate() || $this->isUpdate() || $this->isTransferEdit()) {
				$inventoryModel = Vtiger_Inventory_Model::getInstance($this->getParent()->getModuleName());
				$data = (new \App\Db\Query())->select(['changes'])->from('u_#__modtracker_inv')->where(['id' => $this->get('id')])->scalar();
				$data = $data ? \App\Json::decode($data) : [];
				foreach ($data as $key => $changed) {
					if (!\vtlib\Functions::getCRMRecordMetadata($changed['item'])) {
						continue;
					}
					$changes[$key]['item'] = $changed['item'];
					$changes[$key]['historyState'] = empty($changed['prevalue']) ? 'LBL_INV_ADDED' : (empty($changed['postvalue']) ? 'LBL_INV_DELETED' : 'LBL_INV_UPDATED');
					foreach ($changed['prevalue'] as $fieldName => $value) {
						if ($inventoryModel->isField($fieldName)) {
							$changes[$key]['data'][$fieldName]['field'] = $inventoryModel->getField($fieldName);
							$changes[$key]['data'][$fieldName]['prevalue'] = $value;
						}
					}
					foreach ($changed['postvalue'] as $fieldName => $value) {
						if ($inventoryModel->isField($fieldName)) {
							$changes[$key]['data'][$fieldName]['field'] = $inventoryModel->getField($fieldName);
							$changes[$key]['data'][$fieldName]['postvalue'] = $value;
						}
					}
				}
			}
			$this->inventoryChanges = $changes;
		}
		return $this->inventoryChanges;
	}

	/**
	 * Function return modtracker relation model.
	 *
	 * @return \ModTracker_Relation_Model
	 */
	public function getRelationInstance()
	{
		if ($this->isRelationLink() || $this->isRelationUnLink() || $this->isTransferLink() || $this->isTransferUnLink()) {
			$row = (new \App\Db\Query())->from('vtiger_modtracker_relations')->where(['id' => $this->get('id')])->one();
			$relationInstance = new ModTracker_Relation_Model();
			$relationInstance->setData($row)->setParent($this);
		}
		return $relationInstance;
	}

	public static function getTotalRecordCount($recordId, $type = false)
	{
		$where = self::getConditionByType($type);
		return (new \App\Db\Query())->from('vtiger_modtracker_basic')->where(['crmid' => $recordId])->andWhere($where)->count();
	}

	public static function getConditionByType($type)
	{
		$where = [];
		switch ($type) {
			case 'changes':
				$where = ['not in', 'status', [self::DISPLAYED, self::SHOW_HIDDEN_DATA]];
				break;
			case 'review':
				$where = ['status' => [self::DISPLAYED, self::SHOW_HIDDEN_DATA]];
				break;
			default:
				break;
		}
		return $where;
	}

	public static function addConvertToAccountRelation($sourceModule, $sourceId, $current_user)
	{
		$db = \App\Db::getInstance();
		$db->createCommand()->insert('vtiger_modtracker_basic', [
			'crmid' => $sourceId,
			'module' => $sourceModule,
			'whodid' => $current_user,
			'changedon' => date('Y-m-d H:i:s'),
			'status' => 6,
			'last_reviewed_users' => '#' . App\User::getCurrentUserRealId() . '#',
		])->execute();
		$id = $db->getLastInsertID('vtiger_modtracker_basic_id_seq');
		self::unsetReviewed($sourceId, \App\User::getCurrentUserRealId(), $id);
	}

	/**
	 * Function sets the closest time-wise related record from selected modules.
	 *
	 * @param int    $sourceId
	 * @param string $sourceModule
	 * @param bool   $byUser
	 *
	 * @return array
	 */
	public static function setLastRelation($sourceId, $sourceModule, $byUser = false)
	{
		$db = \App\Db::getInstance();
		$userId = \App\User::getCurrentUserId();
		$query = Vtiger_HistoryRelation_Widget::getQuery($sourceId, $sourceModule, Vtiger_HistoryRelation_Widget::getActions());
		if (!$query) {
			return false;
		}
		$data = $query->limit(1)->one();
		$type = $data ? $data['type'] : '';
		$where = ['crmid' => $sourceId];
		if ($byUser) {
			$where['userid'] = $userId;
		}
		$db->createCommand()->delete('u_#__timeline', $where)->execute();
		$db->createCommand()->insert('u_#__timeline', [
			'crmid' => $sourceId,
			'type' => $type,
			'userid' => $userId,
		])->execute();

		return [$sourceId => $type];
	}

	/**
	 * Function gets the closest time-wise related record from database.
	 *
	 * @param int    $sourceIds
	 * @param string $sourceModule
	 *
	 * @return array
	 */
	public static function getLastRelation($sourceIds, $sourceModule)
	{
		$colors = Vtiger_HistoryRelation_Widget::$colors;
		if (!\is_array($sourceIds)) {
			$sourceIds = [$sourceIds];
		}
		$data = (new \App\Db\Query())->from('u_#__timeline')->where(['crmid' => $sourceIds, 'userid' => \App\User::getCurrentUserId()])->createCommand()->queryAllByGroup(1);
		if (\count($data) !== \count($sourceIds)) {
			$reSearch = array_diff_key(array_flip($sourceIds), $data);
			foreach (array_keys($reSearch) as $id) {
				$result = self::setLastRelation($id, $sourceModule, true);
				if ($result) {
					$data[key($result)]['type'] = current($result);
				}
			}
		}
		foreach ($data as $id => &$type) {
			if (isset($colors[$type['type']])) {
				$type['color'] = $colors[$type['type']];
			} else {
				$type['color'] = false;
			}
			if (false !== strpos($type['type'], 'OSSMailView')) {
				$type['type'] = 'OSSMailView';
			}
		}
		return $data;
	}

	/**
	 * Get field history.
	 *
	 * @param int    $record
	 * @param string $fieldName
	 *
	 * @return array
	 */
	public static function getFieldHistory(int $record, string $fieldName): array
	{
		$rows = [];
		$query = (new \App\Db\Query())
			->select(['vtiger_modtracker_basic.changedon', 'vtiger_modtracker_detail.prevalue', 'vtiger_modtracker_detail.postvalue'])
			->from('vtiger_modtracker_detail')
			->leftJoin('vtiger_modtracker_basic', 'vtiger_modtracker_detail.id = vtiger_modtracker_basic.id')
			->where(['vtiger_modtracker_basic.crmid' => $record, 'vtiger_modtracker_detail.fieldname' => $fieldName])->orderBy(['vtiger_modtracker_basic.id' => SORT_ASC]);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$rows[] = $row;
		}
		$dataReader->close();
		return $rows;
	}
}
