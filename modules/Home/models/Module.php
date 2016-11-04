<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * *********************************************************************************** */

class Home_Module_Model extends Vtiger_Module_Model
{

	/**
	 * Function returns the default view for the Home module
	 * @return <String>
	 */
	public function getDefaultViewName()
	{
		return 'DashBoard';
	}

	/**
	 * Function returns latest comments across CRM
	 * @param <Vtiger_Paging_Model> $pagingModel
	 * @return <Array>
	 */
	public function getComments($pagingModel)
	{
		$query = new \App\Db\Query();
		$query->select(['*', 'createdtime' => 'vtiger_crmentity.createdtime', 'smownerid' => 'vtiger_crmentity.smownerid',
				'parentId' => 'crmentity2.crmid', 'parentModule' => 'crmentity2.setype'])
			->from('vtiger_modcomments')
			->innerJoin('vtiger_crmentity', 'vtiger_modcomments.modcommentsid = vtiger_crmentity.crmid')
			->innerJoin('vtiger_crmentity crmentity2', 'vtiger_modcomments.related_to = crmentity2.crmid')
			->where(['vtiger_crmentity.deleted' => 0, 'crmentity2.deleted' => 0]);
		\App\PrivilegeQuery::getConditions($query, 'ModComments');
		$query->orderBy(['vtiger_modcomments.modcommentsid' => SORT_DESC])
			->limit($pagingModel->getPageLimit())
			->offset($pagingModel->getStartIndex());
		$dataReader = $query->createCommand()->query();
		$comments = [];
		while ($row = $dataReader->read()) {
			if (Users_Privileges_Model::isPermitted($row['setype'], 'DetailView', $row['related_to'])) {
				$commentModel = Vtiger_Record_Model::getCleanInstance('ModComments');
				$commentModel->setData($row);
				$time = $commentModel->get('createdtime');
				$comments[$time] = $commentModel;
			}
		}
		return $comments;
	}

	/**
	 * Function returns part of the query to  fetch only  activity
	 * @param \App\Db\Query $query
	 * @param string $type
	 */
	public function getActivityQuery(\App\Db\Query $query, $type)
	{
		if ($type == 'updates') {
			$query->andWhere(['<>', 'module', 'ModComments']);
		}
	}

	/**
	 * Function returns the Calendar Events for the module
	 * @param <String> $mode - upcoming/overdue mode
	 * @param <Vtiger_Paging_Model> $pagingModel - $pagingModel
	 * @param <String> $user - all/userid
	 * @param <String> $recordId - record id
	 * @return <Array>
	 */
	public function getCalendarActivities($mode, $pagingModel, $user, $recordId = false, $paramsMore = [])
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		if (!$user) {
			$user = $currentUser->getId();
		}

		$orderBy = $pagingModel->getForSql('orderby');
		$sortOrder = $pagingModel->getForSql('sortorder');

		if (empty($sortOrder) || !in_array(strtolower($sortOrder), ['asc', 'desc'])) {
			$sortOrder = 'ASC';
		}
		if (empty($orderBy)) {
			$orderBy = "due_date $sortOrder, time_end $sortOrder";
		} else {
			$orderBy .= ' ' . $sortOrder;
		}

		$params = [];
		$query = 'SELECT vtiger_crmentity.crmid, vtiger_crmentity.smownerid, vtiger_crmentity.setype, vtiger_activity.*
			FROM vtiger_activity
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid
			WHERE vtiger_crmentity.deleted=0 ';
		$query .= \App\PrivilegeQuery::getAccessConditions('Calendar', $currentUser->getId());
		if ($mode === 'upcoming') {
			if (!is_array($paramsMore['status'])) {
				$paramsMore['status'] = [$paramsMore['status']];
			}
			$query .= "AND (vtiger_activity.activitytype NOT IN ('Emails'))
			AND (vtiger_activity.status is NULL || vtiger_activity.status IN (" . generateQuestionMarks($paramsMore['status']) . "))";
			$params = array_merge($params, $paramsMore['status']);
		} elseif ($mode === 'overdue') {
			$query .= "AND (vtiger_activity.activitytype NOT IN ('Emails'))
			AND (vtiger_activity.status is NULL || vtiger_activity.status IN (?))";
			array_push($params, $paramsMore);
		} elseif ($mode === 'assigned_upcoming') {
			$query .= "AND (vtiger_activity.status is NULL || vtiger_activity.status IN (" . generateQuestionMarks($paramsMore['status']) . ")) && vtiger_crmentity.smcreatorid = ?";
			$params = array_merge($params, $paramsMore);
		} elseif ($mode === 'assigned_over') {
			$overdueActivityLabels = Calendar_Module_Model::getComponentActivityStateLabel('overdue');
			$query .= "AND (vtiger_activity.status is NULL || vtiger_activity.status IN (?)) && vtiger_crmentity.smcreatorid = ?";
			array_push($params, $paramsMore['status'], $paramsMore['user']);
		} elseif ($mode === 'createdByMeButNotMine') {
			$query .= "AND (vtiger_activity.status is NULL || vtiger_activity.status IN (" . generateQuestionMarks($paramsMore['status']) . ")) && vtiger_crmentity.smcreatorid = ? && vtiger_crmentity.smownerid NOT IN (?) ";
			array_push($params, $paramsMore['status'], $paramsMore['user'], $paramsMore['user']);
		}

		$accessibleUsers = \App\Fields\Owner::getInstance(false, $currentUser)->getAccessibleUsers();
		$accessibleGroups = \App\Fields\Owner::getInstance(false, $currentUser)->getAccessibleGroups();
		if ($user != 'all' && $user != '' && (array_key_exists($user, $accessibleUsers) || array_key_exists($user, $accessibleGroups))) {
			$query .= ' && vtiger_crmentity.smownerid = ?';
			$params[] = $user;
		}

		$query .= sprintf(' ORDER BY %s LIMIT ?, ?', $orderBy);
		$params[] = $pagingModel->getStartIndex();
		$params[] = $pagingModel->getPageLimit() + 1;

		$result = $db->pquery($query, $params);

		$activities = [];
		while ($row = $db->fetch_array($result)) {
			$model = Vtiger_Record_Model::getCleanInstance('Calendar');
			$model->setData($row);
			$model->setId($row['crmid']);
			if ($row['parent_id']) {
				if (isRecordExists($row['parent_id'])) {
					$record = Vtiger_Record_Model::getInstanceById($row['parent_id']);
					if ($record->getModuleName() == 'Accounts') {
						$model->set('contractor', $record);
					} else if ($record->getModuleName() == 'Project') {
						if (isRecordExists($record->get('linktoaccountscontacts'))) {
							$recordContractor = Vtiger_Record_Model::getInstanceById($record->get('linktoaccountscontacts'));
							$model->set('contractor', $recordContractor);
						}
					} else if ($record->getModuleName() == 'ServiceContracts') {
						if (isRecordExists($record->get('sc_realted_to'))) {
							$recordContractor = Vtiger_Record_Model::getInstanceById($record->get('sc_realted_to'));
							$model->set('contractor', $recordContractor);
						}
					} else if ($record->getModuleName() == 'HelpDesk') {
						if (isRecordExists($record->get('parent_id'))) {
							$recordContractor = Vtiger_Record_Model::getInstanceById($record->get('parent_id'));
							;
							$model->set('contractor', $recordContractor);
						}
					}
				}
			}

			$contactsA = getActivityRelatedContacts($row['activityid']);
			if (count($contactsA)) {
				foreach ($contactsA as $j => $rcA2) {
					$contactsA[$j] = '<a href="index.php?module=Contacts&view=Detail&record=' . $j . '">' . $rcA2 . '</a>';
					$model->set('contact_id', $contactsA);
				}
			}
			$activities[] = $model;
		}

		$pagingModel->calculatePageRange($activities);
		if ($result->rowCount() > $pagingModel->getPageLimit()) {
			array_pop($activities);
			$pagingModel->set('nextPageExists', true);
		} else {
			$pagingModel->set('nextPageExists', false);
		}

		return $activities;
	}

	/**
	 * Function returns the Calendar Events for the module
	 * @param <String> $mode - upcoming/overdue mode
	 * @param <Vtiger_Paging_Model> $pagingModel - $pagingModel
	 * @param <String> $user - all/userid
	 * @param <String> $recordId - record id
	 * @return <Array>
	 */
	public function getAssignedProjectsTasks($mode, $pagingModel, $user, $recordId = false)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		if (!$user) {
			$user = $currentUser->getId();
		}

		$nowInUserFormat = Vtiger_Datetime_UIType::getDisplayDateTimeValue(date('Y-m-d H:i:s'));
		$nowInDBFormat = Vtiger_Datetime_UIType::getDBDateTimeValue($nowInUserFormat);
		list($currentDate, $currentTime) = explode(' ', $nowInDBFormat);

		$params = array();
		$query = "SELECT vtiger_crmentity.crmid, vtiger_crmentity.smownerid, vtiger_crmentity.setype, vtiger_projecttask.*
			FROM vtiger_projecttask
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_projecttask.projecttaskid
			WHERE vtiger_crmentity.deleted=0 && vtiger_crmentity.smcreatorid = ?";
		$params[] = $currentUser->getId();
		$query .= \App\PrivilegeQuery::getAccessConditions('ProjectTask', $currentUser->getId());
		if ($mode === 'upcoming') {
			$query .= " && targetenddate >= ?";
		} elseif ($mode === 'overdue') {
			$query .= " && targetenddate < ?";
		}
		$params[] = $currentDate;

		$accessibleUsers = \App\Fields\Owner::getInstance(false, $currentUser)->getAccessibleUsers();
		$accessibleGroups = \App\Fields\Owner::getInstance(false, $currentUser)->getAccessibleGroups();
		if ($user != 'all' && $user != '' && (array_key_exists($user, $accessibleUsers) || array_key_exists($user, $accessibleGroups))) {
			$query .= " && vtiger_crmentity.smownerid = ?";
			$params[] = $user;
		}

		$query .= " ORDER BY targetenddate LIMIT ?, ?";
		$params[] = $pagingModel->getStartIndex();
		$params[] = $pagingModel->getPageLimit() + 1;

		$result = $db->pquery($query, $params);
		$numOfRows = $db->num_rows($result);

		$projecttasks = array();
		for ($i = 0; $i < $numOfRows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$model = Vtiger_Record_Model::getCleanInstance('ProjectTask');
			$model->setData($row);
			$model->setId($row['crmid']);
			if ($row['projectid']) {
				if (isRecordExists($row['projectid'])) {
					$record = Vtiger_Record_Model::getInstanceById($row['projectid'], 'Project');
					if (isRecordExists($record->get('linktoaccountscontacts'))) {
						$model->set('account', '<a href="index.php?module=' . vtlib\Functions::getCRMRecordType($record->get('linktoaccountscontacts')) . '&view=Detail&record=' . $record->get('linktoaccountscontacts') . '">' . vtlib\Functions::getCRMRecordLabel($record->get('linktoaccountscontacts')) . '</a>');
					}
				}
			}
			$projecttasks[] = $model;
		}
		$pagingModel->calculatePageRange($projecttasks);
		if ($numOfRows > $pagingModel->getPageLimit()) {
			array_pop($projecttasks);
			$pagingModel->set('nextPageExists', true);
		} else {
			$pagingModel->set('nextPageExists', false);
		}

		return $projecttasks;
	}

	/**
	 * Function returns comments and recent activities across module
	 * @param <Vtiger_Paging_Model> $pagingModel
	 * @param <String> $type - comments, updates or all
	 * @return <Array>
	 */
	public function getHistory($pagingModel, $type = false)
	{
		if (empty($type)) {
			$type = 'all';
		}
		$comments = array();
		if ($type == 'all' || $type == 'comments') {
			$modCommentsModel = Vtiger_Module_Model::getInstance('ModComments');
			if ($modCommentsModel->isPermitted('DetailView')) {
				$comments = $this->getComments($pagingModel);
			}
			if ($type == 'comments') {
				return $comments;
			}
		}
		//As getComments api is used to get comment infomation,no need of getting
		//comment information again,so avoiding from modtracker
		//updateActivityQuery api is used to update a query to fetch a only activity
		if ($type == 'updates' || $type == 'all') {
			$query = new \App\Db\Query();
			$query->select('vtiger_modtracker_basic.*')
				->from('vtiger_modtracker_basic')
				->innerJoin('vtiger_crmentity', 'vtiger_modtracker_basic.crmid = vtiger_crmentity.crmid')
				->where(['vtiger_crmentity.deleted' => 0]);

			$this->getActivityQuery($query, $type);
			$query->orderBy(['vtiger_modtracker_basic' => SORT_DESC])
				->limit($pagingModel->getPageLimit())
				->offset($pagingModel->getStartIndex());
			$dataReader = $query->createCommand()->query();
			$activites = [];
			while ($row = $dataReader->read()) {
				$moduleName = $row['module'];
				$recordId = $row['crmid'];
				if (Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $recordId)) {
					$modTrackerRecorModel = new ModTracker_Record_Model();
					$modTrackerRecorModel->setData($row)->setParent($recordId, $moduleName);
					$time = $modTrackerRecorModel->get('changedon');
					$activites[$time] = $modTrackerRecorModel;
				}
			}
		}
		$history = array_merge($activites, $comments);

		$dateTime = array();
		foreach ($history as $time => $model) {
			$dateTime[] = $time;
		}

		if (!empty($history)) {
			array_multisort($dateTime, SORT_DESC, SORT_STRING, $history);
			return $history;
		}
		return false;
	}
}
