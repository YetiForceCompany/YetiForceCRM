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

class Home_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Function returns the default view for the Home module.
	 *
	 * @return string
	 */
	public function getDefaultViewName()
	{
		return 'DashBoard';
	}

	/**
	 * Function returns latest comments across CRM.
	 *
	 * @param \Vtiger_Paging_Model $pagingModel
	 *
	 * @return \Vtiger_Record_Model[]
	 */
	public function getComments($pagingModel)
	{
		$query = new \App\Db\Query();
		$query->select(['*', 'createdtime' => 'vtiger_crmentity.createdtime', 'assigned_user_id' => 'vtiger_crmentity.smownerid',
			'parentId' => 'crmentity2.crmid', 'parentModule' => 'crmentity2.setype', ])
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
			if (\App\Privilege::isPermitted($row['setype'], 'DetailView', $row['related_to'])) {
				$commentModel = Vtiger_Record_Model::getCleanInstance('ModComments');
				$commentModel->setData($row);
				$time = $commentModel->get('createdtime');
				$comments[$time] = $commentModel;
			}
		}
		$dataReader->close();

		return $comments;
	}

	/**
	 * Function returns part of the query to  fetch only  activity.
	 *
	 * @param \App\Db\Query $query
	 * @param string        $type
	 */
	public function getActivityQuery(App\Db\Query $query, $type)
	{
		if ('updates' == $type) {
			$query->andWhere(['<>', 'module', 'ModComments']);
		}
	}

	/**
	 * Function returns the Calendar Events for the module.
	 *
	 * @param string              $mode        - upcoming/overdue mode
	 * @param Vtiger_Paging_Model $pagingModel - $pagingModel
	 * @param string              $user        - all/userid
	 * @param string              $recordId    - record id
	 * @param mixed               $paramsMore
	 *
	 * @return array
	 */
	public function getCalendarActivities($mode, Vtiger_Paging_Model $pagingModel, $user, $recordId = false, $paramsMore = [])
	{
		$activities = [];
		$query = new \App\Db\Query();
		if (!$user) {
			$user = \App\User::getCurrentUserId();
		}
		$query->select(['vtiger_crmentity.crmid', 'vtiger_crmentity.smownerid', 'vtiger_crmentity.setype', 'vtiger_activity.*', 'taskpriority' => 'vtiger_activity.priority'])
			->from('vtiger_activity')
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_activity.activityid')
			->where(['vtiger_crmentity.deleted' => 0]);
		\App\PrivilegeQuery::getConditions($query, 'Calendar');
		if ('upcoming' === $mode || 'overdue' === $mode) {
			$query->andWhere(['or', ['vtiger_activity.status' => null], ['vtiger_activity.status' => $paramsMore['status']]]);
		} elseif ('createdByMeButNotMine' === $mode || 'createdByMeButNotMineOverdue' === $mode) {
			$query->andWhere(['or', ['vtiger_activity.status' => null], ['vtiger_activity.status' => $paramsMore['status']]]);
			$query->andWhere(['and', ['vtiger_crmentity.smcreatorid' => $paramsMore['user']], ['NOT IN', 'vtiger_crmentity.smownerid', $paramsMore['user']]]);
		}
		if (isset($paramsMore['activitytype'])) {
			$query->andWhere(['vtiger_activity.activitytype' => $paramsMore['activitytype']]);
		}
		if (isset($paramsMore['taskpriority'])) {
			$query->andWhere(['vtiger_activity.priority' => $paramsMore['taskpriority']]);
		}
		if ('all' !== $user && !empty($user)) {
			$userId = (int) $user;
			if (\App\User::isExists($userId)) {
				$userModel = \App\User::getUserModel($userId);
				$userAndGroups = $userModel->getGroups();
			}
			$userAndGroups[] = $userId;
			$subQuery = (new \App\Db\Query())->select(['crmid'])->from('u_#__crmentity_showners')->innerJoin('vtiger_activity', 'u_#__crmentity_showners.crmid=vtiger_activity.activityid')->where(['userid' => $userAndGroups])->distinct('crmid');
			$query->andWhere(['or', ['vtiger_crmentity.smownerid' => $userAndGroups], ['vtiger_crmentity.crmid' => $subQuery]]);
		}

		$query->orderBy($pagingModel->get('orderby'))
			->limit($pagingModel->getPageLimit() + 1)
			->offset($pagingModel->getStartIndex());
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$model = Vtiger_Record_Model::getCleanInstance('Calendar');
			$model->setData($row);
			$model->setId($row['crmid']);
			if (!empty($row['parent_id']) && \App\Record::isExists($row['parent_id'])) {
				$record = Vtiger_Record_Model::getInstanceById($row['parent_id']);
				if ('Accounts' === $record->getModuleName()) {
					$model->set('contractor', $record);
				} elseif ('Project' === $record->getModuleName()) {
					if (\App\Record::isExists($record->get('linktoaccountscontacts'))) {
						$recordContractor = Vtiger_Record_Model::getInstanceById($record->get('linktoaccountscontacts'));
						$model->set('contractor', $recordContractor);
					}
				} elseif ('ServiceContracts' === $record->getModuleName()) {
					if (\App\Record::isExists($record->get('sc_realted_to'))) {
						$recordContractor = Vtiger_Record_Model::getInstanceById($record->get('sc_realted_to'));
						$model->set('contractor', $recordContractor);
					}
				} elseif ('HelpDesk' === $record->getModuleName()) {
					if (\App\Record::isExists($record->get('parent_id'))) {
						$recordContractor = Vtiger_Record_Model::getInstanceById($record->get('parent_id'));
						$model->set('contractor', $recordContractor);
					}
				}
			}
			$activities[] = $model;
		}
		$pagingModel->calculatePageRange($dataReader->count());
		if ($dataReader->count() > $pagingModel->getPageLimit()) {
			array_pop($activities);
			$pagingModel->set('nextPageExists', true);
		} else {
			$pagingModel->set('nextPageExists', false);
		}
		$dataReader->close();
		return $activities;
	}

	/**
	 * Function returns the Calendar Events for the module.
	 *
	 * @param string                $mode        - upcoming/overdue mode
	 * @param <Vtiger_Paging_Model> $pagingModel - $pagingModel
	 * @param string                $user        - all/userid
	 * @param string                $recordId    - record id
	 *
	 * @return <Array>
	 */
	public function getAssignedProjectsTasks($mode, $pagingModel, $user, $recordId = false)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if (!$user) {
			$user = $currentUser->getId();
		}
		$nowInUserFormat = App\Fields\DateTime::formatToDisplay(date('Y-m-d H:i:s'));
		$nowInDBFormat = App\Fields\DateTime::formatToDb($nowInUserFormat);
		[$currentDate] = explode(' ', $nowInDBFormat);
		$query = (new App\Db\Query())
			->select(['vtiger_crmentity.crmid', 'vtiger_crmentity.smownerid', 'vtiger_crmentity.setype', 'vtiger_projecttask.*'])
			->from('vtiger_projecttask')
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_projecttask.projecttaskid')
			->where(['vtiger_crmentity.deleted' => 0, 'vtiger_crmentity.smcreatorid' => $currentUser->getId()]);
		\App\PrivilegeQuery::getConditions($query, 'ProjectTask');
		if ('upcoming' === $mode) {
			$query->andWhere(['>=', 'targetenddate', $currentDate]);
		} elseif ('overdue' === $mode) {
			$query->andWhere(['<', 'targetenddate', $currentDate]);
		}
		$accessibleUsers = \App\Fields\Owner::getInstance(false, $currentUser)->getAccessibleUsers();
		$accessibleGroups = \App\Fields\Owner::getInstance(false, $currentUser)->getAccessibleGroups();
		if ('all' != $user && '' != $user && (\array_key_exists($user, $accessibleUsers) || \array_key_exists($user, $accessibleGroups))) {
			$query->andWhere(['vtiger_crmentity.smownerid' => $user]);
		}
		$query->orderBy('targetenddate')
			->limit($pagingModel->getPageLimit() + 1)
			->offset($pagingModel->getStartIndex());
		$dataReader = $query->createCommand()->query();
		$projecttasks = [];
		while ($row = $dataReader->read()) {
			$model = Vtiger_Record_Model::getCleanInstance('ProjectTask');
			$model->setData($row);
			$model->setId($row['crmid']);
			if ($row['projectid'] && \App\Record::isExists($row['projectid'])) {
				$record = Vtiger_Record_Model::getInstanceById($row['projectid'], 'Project');
				if (\App\Record::isExists($record->get('linktoaccountscontacts'))) {
					$model->set('account', '<a href="index.php?module=' . \App\Record::getType($record->get('linktoaccountscontacts')) . '&view=Detail&record=' . $record->get('linktoaccountscontacts') . '">' . vtlib\Functions::getCRMRecordLabel($record->get('linktoaccountscontacts')) . '</a>');
				}
			}
			$projecttasks[] = $model;
		}
		$pagingModel->calculatePageRange($dataReader->count());
		if ($dataReader->count() > $pagingModel->getPageLimit()) {
			array_pop($projecttasks);
			$pagingModel->set('nextPageExists', true);
		} else {
			$pagingModel->set('nextPageExists', false);
		}
		$dataReader->close();

		return $projecttasks;
	}

	/**
	 * Function returns comments and recent activities across module.
	 *
	 * @param <Vtiger_Paging_Model> $pagingModel
	 * @param string                $type        - comments, updates or all
	 *
	 * @return <Array>
	 */
	public function getHistory($pagingModel, $type = false)
	{
		if (empty($type)) {
			$type = 'all';
		}
		$comments = [];
		if ('all' == $type || 'comments' == $type) {
			$modCommentsModel = Vtiger_Module_Model::getInstance('ModComments');
			if ($modCommentsModel->isPermitted('DetailView')) {
				$comments = $this->getComments($pagingModel);
			}
			if ('comments' == $type) {
				return $comments;
			}
		}
		//As getComments api is used to get comment infomation,no need of getting
		//comment information again,so avoiding from modtracker
		//updateActivityQuery api is used to update a query to fetch a only activity
		if ('updates' == $type || 'all' == $type) {
			$query = new \App\Db\Query();
			$query->select(['vtiger_modtracker_basic.*'])
				->from('vtiger_modtracker_basic')
				->innerJoin('vtiger_crmentity', 'vtiger_modtracker_basic.crmid = vtiger_crmentity.crmid')
				->where(['vtiger_crmentity.deleted' => 0]);

			$this->getActivityQuery($query, $type);
			$query->orderBy(['vtiger_modtracker_basic.id' => SORT_DESC])
				->limit($pagingModel->getPageLimit())
				->offset($pagingModel->getStartIndex());
			$dataReader = $query->createCommand()->query();
			$activites = [];
			while ($row = $dataReader->read()) {
				$moduleName = $row['module'];
				$recordId = $row['crmid'];
				if (\App\Privilege::isPermitted($moduleName, 'DetailView', $recordId)) {
					$modTrackerRecorModel = new ModTracker_Record_Model();
					$modTrackerRecorModel->setData($row)->setParent($recordId, $moduleName);
					$time = $modTrackerRecorModel->get('changedon');
					$activites[$time] = $modTrackerRecorModel;
				}
			}
			$dataReader->close();
		}
		$history = array_merge($activites, $comments);

		$dateTime = [];
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
