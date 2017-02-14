<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class ModTracker_Record_Model extends Vtiger_Record_Model
{

	const UPDATE = 0;
	const DELETE = 1;
	const CREATE = 2;
	const RESTORE = 3;
	const LINK = 4;
	const UNLINK = 5;
	const CONVERTTOACCOUNT = 6;
	const DISPLAYED = 7;

	/**
	 * Function to get the history of updates on a record
	 * @param int $parentRecordId
	 * @param Vtiger_Paging_Model $pagingModel
	 * @param string $type
	 * @return array - list of  ModTracker_Record_Model
	 */
	public static function getUpdates($parentRecordId, Vtiger_Paging_Model $pagingModel, $type)
	{
		$recordInstances = [];
		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();
		$where = self::getConditionByType($type);
		$query = (new \App\Db\Query())
			->from('vtiger_modtracker_basic')
			->where(['crmid' => $parentRecordId])
			->andWhere(($where))
			->limit($pageLimit)
			->offset($startIndex)
			->orderBy(['changedon' => SORT_DESC]);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$recordInstance = new self();
			$recordInstance->setData($row)->setParent($row['crmid'], $row['module']);
			$recordInstances[] = $recordInstance;
		}
		return $recordInstances;
	}

	public static function setLastReviewed($recordId)
	{
		$row = (new App\Db\Query())->select('last_reviewed_users,id')
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
		$query->select('last_reviewed_users, id')->from('vtiger_modtracker_basic')->where(['crmid' => $recordId])
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

	public static function isNewChange($recordId, $userId = false)
	{
		if ($userId === false) {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$userId = $currentUser->getId();
		}

		$lastReviewedUsers = (new \App\Db\Query())->select('last_reviewed_users')->from('vtiger_modtracker_basic')
				->where(['crmid' => $recordId])
				->andWhere(['<>', 'status', self::DISPLAYED])->orderBy(['changedon' => SORT_DESC, 'id' => SORT_DESC])->limit(1)->scalar();
		if ($lastReviewedUsers !== false) {
			return strpos($lastReviewedUsers, "#$userId#") === false;
		}
		return true;
	}

	public static function getUnreviewed($recordsId, $userId = false, $sort = false)
	{
		if ($userId === false) {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$userId = $currentUser->getId();
		}

		if (!is_array($recordsId)) {
			$recordsId = [$recordsId];
		}
		$query = (new \App\Db\Query())->select('crmid, last_reviewed_users AS u')->from('vtiger_modtracker_basic')
			->where(['crmid' => $recordsId])
			->andWhere(['<>', 'status', self::DISPLAYED]);
		if ($sort) {
			$query->addSelect('vtiger_ossmailview.type');
			$query->leftJoin('vtiger_modtracker_relations', 'vtiger_modtracker_basic.id = vtiger_modtracker_relations.id');
			$query->leftJoin('vtiger_ossmailview', 'vtiger_modtracker_relations.targetid = vtiger_ossmailview.ossmailviewid');
			$query->orderBy('vtiger_modtracker_basic.crmid ,vtiger_modtracker_basic.id DESC');
		}
		$dataReader = $query->createCommand()->query();

		$changes = [];
		while ($row = $dataReader->read()) {
			$changes[$row['crmid']][] = $row;
		}
		$unreviewed = [];
		foreach ($changes as $crmId => $rows) {
			$all = $mails = 0;
			foreach ($rows as $row) {
				if (strpos($row['u'], "#$userId#") !== false) {
					break;
				}
				if ($row['type'] === 1) {
					++$mails;
				} elseif ($row['type'] !== 0) {
					++$all;
				}
			}
			$unreviewed[$crmId]['a'] = $all;
			$unreviewed[$crmId]['m'] = $mails;
		}
		return $unreviewed;
	}

	/**
	 * Function to get the name of the module to which the record belongs
	 * @return string - Record Module Name
	 */
	public function getModule()
	{
		if (empty($this->parent)) {
			return Vtiger_Module_Model::getInstance($this->getModuleName());
		}
		return $this->getParent()->getModule();
	}

	/**
	 * Function to get the name of the module to which the record belongs
	 * @return string - Record Module Name
	 */
	public function getModuleName()
	{
		return $this->get('module');
	}

	/**
	 * Function to get the Detail View url for the record
	 * @return string - Record Detail View Url
	 */
	public function getDetailViewUrl()
	{
		$moduleName = $this->getModuleName();
		switch ($moduleName) {
			case 'Documents': $action = 'action=DownloadFile';
				break;
			case 'OSSMailView': $action = 'view=preview';
				break;
			default: $action = 'view=Detail';
				break;
		}
		if ($moduleName == 'Events') {
			$moduleName = 'Calendar';
		}
		return "index.php?module=$moduleName&$action&record=" . $this->get('crmid');
	}

	public function setParent($id, $moduleName)
	{
		$this->parent = Vtiger_Record_Model::getInstanceById($id, $moduleName);
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

	public function isDelete()
	{
		return $this->checkStatus(self::DELETE);
	}

	public function isRestore()
	{
		return $this->checkStatus(self::RESTORE);
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

	public function isReviewed($userId = false)
	{
		if ($userId === false) {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$userId = $currentUser->getId();
		}
		$reviewed = $this->get('last_reviewed_users');
		if (empty($reviewed)) {
			return false;
		}
		return strpos($reviewed, "#$userId#") !== false;
	}

	public function getModifiedBy()
	{
		$changeUserId = $this->get('whodid');
		return Users_Record_Model::getInstanceById($changeUserId, 'Users');
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

	public function getFieldInstances()
	{
		$id = $this->get('id');
		$db = PearDatabase::getInstance();

		$fieldInstances = [];
		if ($this->isCreate() || $this->isUpdate()) {
			$result = $db->pquery('SELECT * FROM vtiger_modtracker_detail WHERE id = ?', array($id));
			while ($data = $db->getRow($result)) {
				$row = array_map('html_entity_decode', $data);

				if ($row['fieldname'] == 'record_id' || $row['fieldname'] == 'record_module')
					continue;

				$fieldModel = Vtiger_Field_Model::getInstance($row['fieldname'], $this->getModule());
				if (!$fieldModel)
					continue;

				$fieldInstance = new ModTracker_Field_Model();
				$fieldInstance->setData($row)->setParent($this)->setFieldInstance($fieldModel);
				$fieldInstances[] = $fieldInstance;
			}
		}
		return $fieldInstances;
	}

	public function getRelationInstance()
	{
		$id = $this->get('id');
		$db = PearDatabase::getInstance();

		if ($this->isRelationLink() || $this->isRelationUnLink()) {
			$result = $db->pquery('SELECT * FROM vtiger_modtracker_relations WHERE id = ?', array($id));
			$row = $db->query_result_rowdata($result, 0);
			$relationInstance = new ModTracker_Relation_Model();
			$relationInstance->setData($row)->setParent($this);
		}
		return $relationInstance;
	}

	public static function getTotalRecordCount($recordId, $type = false)
	{
		$where = self::getConditionByType($type);
		$count = (new \App\Db\Query())->from('vtiger_modtracker_basic')->where(['crmid' => $recordId])->andWhere($where)->count();
		return $count;
	}

	public static function getConditionByType($type)
	{
		$where = [];
		switch ($type) {
			case 'changes':
				$where = ['<>', 'status', self::DISPLAYED];
				break;
			case 'review':
				$where = ['status' => self::DISPLAYED];
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
			'last_reviewed_users' => '#' . App\User::getCurrentUserRealId() . '#'
		])->execute();
		$id = $db->getLastInsertID('vtiger_modtracker_basic_id_seq');
		self::unsetReviewed($sourceId, \App\User::getCurrentUserRealId(), $id);
	}

	/**
	 * Function sets the closest time-wise related record from selected modules
	 * @param int $sourceId
	 * @param string $sourceModule
	 * @param bool $byUser
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
			'userid' => $userId
		])->execute();
		return [$sourceId => $type];
	}

	/**
	 * Function gets the closest time-wise related record from database
	 * @param int $sourceIds
	 * @param string $sourceModule
	 * @return array
	 */
	public static function getLastRelation($sourceIds, $sourceModule)
	{
		$colors = Vtiger_HistoryRelation_Widget::$colors;
		if (!is_array($sourceIds)) {
			$sourceIds = [$sourceIds];
		}
		$data = (new \App\Db\Query())->from('u_#__timeline')->where(['crmid' => $sourceIds, 'userid' => \App\User::getCurrentUserId()])->createCommand()->queryAllByGroup(1);
		if (count($data) !== count($sourceIds)) {
			$reSearch = array_diff_key(array_flip($sourceIds), $data);
			foreach (array_keys($reSearch) as $id) {
				$result = ModTracker_Record_Model::setLastRelation($id, $sourceModule, true);
				if ($result) {
					$data[key($result)]['type'] = current($result);
				}
			}
		}
		foreach ($data as $id => &$type) {
			$type['color'] = $colors[$type['type']];
			if (strpos($type['type'], 'OSSMailView') !== false) {
				$type['type'] = 'OSSMailView';
			}
		}
		return $data;
	}
}
