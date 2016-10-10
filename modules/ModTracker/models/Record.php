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
	 * @param <type> $record - Record model
	 * @param <type> $limit - number of latest changes that need to retrieved
	 * @return <array> - list of  ModTracker_Record_Model
	 */
	public static function getUpdates($parentRecordId, $pagingModel, $type)
	{
		$db = PearDatabase::getInstance();
		$recordInstances = [];
		$params = [];

		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();

		$where = self::getConditionByType($type);
		$listQuery = sprintf('SELECT * FROM vtiger_modtracker_basic WHERE crmid = ? %s ORDER BY changedon DESC LIMIT ?, ?;', $where);
		array_push($params, $parentRecordId, $startIndex, $pageLimit);
		$result = $db->pquery($listQuery, $params);
		$rows = $db->num_rows($result);

		for ($i = 0; $i < $rows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$recordInstance = new self();
			$recordInstance->setData($row)->setParent($row['crmid'], $row['module']);
			$recordInstances[] = $recordInstance;
		}
		return $recordInstances;
	}

	public static function setLastReviewed($recordId)
	{
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$listQuery = 'SELECT `last_reviewed_users`, `id` FROM vtiger_modtracker_basic WHERE crmid = ? && status <> ? ORDER BY changedon DESC, id DESC LIMIT 1;';
		$result = $db->pquery($listQuery, [$recordId, self::DISPLAYED]);
		if ($result->rowCount()) {
			$row = $db->getRow($result);
			$lastReviewedUsers = explode('#', $row['last_reviewed_users']);
			$lastReviewedUsers[] = $currentUser->getRealId();
			$db->update('vtiger_modtracker_basic', ['last_reviewed_users' => '#' . implode('#', array_filter($lastReviewedUsers)) . '#'], ' `id` = ?', [$row['id']]);
			return $row['id'];
		}
		return false;
	}

	public static function unsetReviewed($recordId, $userId = false, $exception = false)
	{
		$db = PearDatabase::getInstance();
		if (!$userId) {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$userId = $currentUser->getRealId();
		}
		if ($exception) {
			$where = ' && `id` <> ' . $exception;
		}
		$listQuery = sprintf('SELECT last_reviewed_users,id FROM vtiger_modtracker_basic WHERE crmid = ? && status <> ? && last_reviewed_users LIKE "%s" %s ORDER BY changedon DESC, id DESC LIMIT 1;', "%#$userId#%", $where);
		$result = $db->pquery($listQuery, [$recordId, self::DISPLAYED]);
		if ($result->rowCount()) {
			$row = $db->getRow($result);
			$lastReviewedUsers = array_filter(explode('#', $row['last_reviewed_users']));
			$key = array_search($userId, $lastReviewedUsers);
			unset($lastReviewedUsers[$key]);
			$value = empty($lastReviewedUsers) ? '' : '#' . implode('#', array_filter($lastReviewedUsers)) . '#';
			return $db->update('vtiger_modtracker_basic', ['last_reviewed_users' => $value], ' `id` = ?', [$row['id']]);
		}
		return false;
	}

	public static function isNewChange($recordId, $userId = false)
	{
		$db = PearDatabase::getInstance();
		if ($userId === false) {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$userId = $currentUser->getId();
		}

		$listQuery = 'SELECT `last_reviewed_users` FROM vtiger_modtracker_basic WHERE crmid = ? && status <> ? ORDER BY changedon DESC, id DESC LIMIT 1;';
		$result = $db->pquery($listQuery, [$recordId, self::DISPLAYED]);
		$lastReviewedUsers = $db->getSingleValue($result);
		if (!empty($lastReviewedUsers)) {
			return strpos($lastReviewedUsers, "#$userId#") === false;
		}
		return true;
	}

	public static function getUnreviewed($recordsId, $userId = false, $sort = false)
	{
		$db = PearDatabase::getInstance();
		if ($userId === false) {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$userId = $currentUser->getId();
		}
		$unreviewed = [];
		if (!is_array($recordsId)) {
			$recordsId = [$recordsId];
		}
		$listQuery = sprintf('SELECT `crmid`,`last_reviewed_users` FROM vtiger_modtracker_basic WHERE crmid IN (%s) && status <> ?', $db->generateQuestionMarks($recordsId));
		if ($sort) {
			$listQuery .=' ORDER BY crmid, id DESC';
		}
		$result = $db->pquery($listQuery, [$recordsId, self::DISPLAYED]);
		$changes = $db->getColumnByGroup($result);
		foreach ($changes as $crmId => $reviewedUsers) {
			$count = 0;
			foreach ($reviewedUsers as $users) {
				if (strpos($users, "#$userId#") !== false) {
					break;
				}
				++$count;
				$unreviewed[$crmId] = $count;
			}
		}
		return $unreviewed;
	}

	/**
	 * Function to get the name of the module to which the record belongs
	 * @return <String> - Record Module Name
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
	 * @return <String> - Record Module Name
	 */
	public function getModuleName()
	{
		return $this->get('module');
	}

	/**
	 * Function to get the Detail View url for the record
	 * @return <String> - Record Detail View Url
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
		$db = PearDatabase::getInstance();
		$where = self::getConditionByType($type);
		$query = sprintf('SELECT COUNT(*) AS count FROM vtiger_modtracker_basic WHERE crmid = ? %s', $where);
		$result = $db->pquery($query, [$recordId]);
		return $db->query_result($result, 0, 'count');
	}

	public static function getConditionByType($type)
	{
		$where = '';
		switch ($type) {
			case 'changes':
				$where = ' && status <> ' . self::DISPLAYED;
				break;
			case 'review':
				$where = ' && status = ' . self::DISPLAYED;
				break;
			default:
				break;
		}
		return $where;
	}

	public static function addConvertToAccountRelation($sourceModule, $sourceId, $current_user)
	{
		$adb = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$adb->insert('vtiger_modtracker_basic', [
			'id' => $adb->getUniqueId('vtiger_modtracker_basic'),
			'crmid' => $sourceId,
			'module' => $sourceModule,
			'whodid' => $current_user,
			'changedon' => date('Y-m-d H:i:s'),
			'status' => 6,
			'last_reviewed_users' => '#' . $currentUser->getRealId() . '#'
		]);
	}
}
