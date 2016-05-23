<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

require_once 'include/events/include.inc';

/**
 * Roles Record Model Class
 */
class Settings_Groups_Record_Model extends Settings_Vtiger_Record_Model
{

	/**
	 * Function to get the Id
	 * @return <Number> Group Id
	 */
	public function getId()
	{
		return $this->get('groupid');
	}

	/**
	 * Function to set the Id
	 * @param <Number> Group Id
	 * @return <Settings_Groups_Reord_Model> instance
	 */
	public function setId($id)
	{
		return $this->set('groupid', $id);
	}

	/**
	 * Function to get the Group Name
	 * @return <String>
	 */
	public function getName()
	{
		return $this->get('groupname');
	}

	/**
	 * Function to get the description of the group
	 * @return <String>
	 */
	public function getDescription()
	{
		return $this->get('description');
	}

	/**
	 * Function to get the Edit View Url for the Group
	 * @return <String>
	 */
	public function getEditViewUrl()
	{
		return '?module=Groups&parent=Settings&view=Edit&record=' . $this->getId();
	}

	/**
	 * Function to get the Delete Action Url for the current group
	 * @return <String>
	 */
	public function getDeleteActionUrl()
	{
		return 'index.php?module=Groups&parent=Settings&view=DeleteAjax&record=' . $this->getId();
	}

	/**
	 * Function to get the Detail Url for the current group
	 * @return <String>
	 */
	public function getDetailViewUrl()
	{
		return '?module=Groups&parent=Settings&view=Detail&record=' . $this->getId();
	}

	/**
	 * Function to get all the members of the groups
	 * @return <Array> Settings_Profiles_Record_Model instances
	 */
	public function getMembers()
	{
		if (!isset($this->members)) {
			$this->members = Settings_Groups_Member_Model::getAllByGroup($this);
		}
		return $this->members;
	}

	/**
	 * Function to get the Modules
	 * @return <Array>
	 */
	public function getModules()
	{
		if (!isset($this->modules)) {
			$db = PearDatabase::getInstance();

			$sql = 'SELECT vtiger_tab.tabid, vtiger_tab.name FROM vtiger_group2modules INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_group2modules.tabid WHERE vtiger_group2modules.groupid=?';
			$result = $db->pquery($sql, [$this->getId()]);
			$modules = [];
			for ($i = 0; $i < $db->num_rows($result); ++$i) {
				$row = $db->query_result_rowdata($result, $i);
				$modules[$row['tabid']] = $row['name'];
			}
			$this->modules = $modules;
		}
		return $this->modules;
	}

	/**
	 * Function to save the role
	 */
	public function save()
	{
		$db = PearDatabase::getInstance();
		$groupId = $this->getId();
		$mode = 'edit';
		$oldUsersList = $this->getUsersList(true);

		if (empty($groupId)) {
			$mode = '';
			$groupId = $db->getUniqueId('vtiger_users');
			$this->setId($groupId);
		}

		if ($mode == 'edit') {
			$sql = 'UPDATE vtiger_groups SET groupname=?, description=? WHERE groupid=?';
			$params = array($this->getName(), $this->getDescription(), $groupId);
		} else {
			$sql = 'INSERT INTO vtiger_groups(groupid, groupname, description) VALUES (?,?,?)';
			$params = array($groupId, $this->getName(), $this->getDescription());
		}
		$db->pquery($sql, $params);

		$members = $this->get('group_members');
		if (is_array($members)) {
			$db->pquery('DELETE FROM vtiger_users2group WHERE groupid=?', array($groupId));
			$db->pquery('DELETE FROM vtiger_group2grouprel WHERE groupid=?', array($groupId));
			$db->pquery('DELETE FROM vtiger_group2role WHERE groupid=?', array($groupId));
			$db->pquery('DELETE FROM vtiger_group2rs WHERE groupid=?', array($groupId));

			$noOfMembers = count($members);
			for ($i = 0; $i < $noOfMembers; ++$i) {
				$id = $members[$i];
				$idComponents = Settings_Groups_Member_Model::getIdComponentsFromQualifiedId($id);
				if ($idComponents && count($idComponents) == 2) {
					$memberType = $idComponents[0];
					$memberId = $idComponents[1];

					if ($memberType == Settings_Groups_Member_Model::MEMBER_TYPE_USERS) {
						$db->pquery('INSERT INTO vtiger_users2group(userid, groupid) VALUES (?,?)', array($memberId, $groupId));
					}
					if ($memberType == Settings_Groups_Member_Model::MEMBER_TYPE_GROUPS) {
						$db->pquery('INSERT INTO vtiger_group2grouprel(containsgroupid, groupid) VALUES (?,?)', array($memberId, $groupId));
					}
					if ($memberType == Settings_Groups_Member_Model::MEMBER_TYPE_ROLES) {
						$db->pquery('INSERT INTO vtiger_group2role(roleid, groupid) VALUES (?,?)', array($memberId, $groupId));
					}
					if ($memberType == Settings_Groups_Member_Model::MEMBER_TYPE_ROLE_AND_SUBORDINATES) {
						$db->pquery('INSERT INTO vtiger_group2rs(roleandsubid, groupid) VALUES (?,?)', array($memberId, $groupId));
					}
				}
			}
		}
		$modules = $this->get('modules');
		if (is_array($modules)) {
			$db->pquery('DELETE FROM vtiger_group2modules WHERE groupid=?', array($groupId));
			for ($i = 0; $i < count($modules); ++$i) {
				$db->pquery('INSERT INTO vtiger_group2modules(tabid, groupid) VALUES (?,?)', array($modules[$i], $groupId));
			}
		}
		$this->recalculate($oldUsersList);
		$em = new VTEventsManager($db);
		$em->initTriggerCache();
		$entityData = array();
		$entityData['groupid'] = $groupId;
		$entityData['group_members'] = $members;
		$entityData['memberId'] = $memberId;
		$entityData['modules'] = $modules;
		$em->triggerEvent("vtiger.entity.aftergroupsave", $entityData);
	}

	/**
	 * Function to recalculate user priviliges files
	 * @param <Array> $oldUsersList
	 */
	public function recalculate($oldUsersList)
	{
		$php_max_execution_time = vglobal('php_max_execution_time');
		set_time_limit($php_max_execution_time);
		require_once('modules/Users/CreateUserPrivilegeFile.php');

		$userIdsList = array();
		foreach ($oldUsersList as $userId => $userRecordModel) {
			$userIdsList[$userId] = $userId;
		}

		$this->members = null;
		foreach ($this->getUsersList(true) as $userId => $userRecordModel) {
			$userIdsList[$userId] = $userId;
		}

		foreach ($userIdsList as $userId) {
			createUserPrivilegesfile($userId);
		}
	}

	/**
	 * Function to get all users related to this group
	 * @param <Boolean> $nonAdmin true/false
	 * @return <Array> Users models list <Users_Record_Model>
	 */
	public function getUsersList($nonAdmin = false)
	{
		$userIdsList = $usersList = array();
		$members = $this->getMembers();

		foreach ($members['Users'] as $memberModel) {
			$userId = $memberModel->get('userId');
			$userIdsList[$userId] = $userId;
		}

		foreach ($members['Groups'] as $memberModel) {
			$groupModel = Settings_Groups_Record_Model::getInstance($memberModel->get('groupId'));
			$groupMembers = $groupModel->getMembers();

			foreach ($groupMembers['Users'] as $groupMemberModel) {
				$userId = $groupMemberModel->get('userId');
				$userIdsList[$userId] = $userId;
			}
		}

		foreach ($members['Roles'] as $memberModel) {
			$roleModel = new Settings_Roles_Record_Model();
			$roleModel->set('roleid', $memberModel->get('roleId'));

			$roleUsers = $roleModel->getUsers();
			foreach ($roleUsers as $userId => $userRecordModel) {
				$userIdsList[$userId] = $userId;
			}
		}

		foreach ($members['RoleAndSubordinates'] as $memberModel) {
			$roleModel = new Settings_Roles_Record_Model();
			$roleModel->set('roleid', $memberModel->get('roleId'));

			$roleUsers = $roleModel->getUsers();
			foreach ($roleUsers as $userId => $userRecordModel) {
				$userIdsList[$userId] = $userId;
			}
		}

		if (array_key_exists(1, $userIdsList)) {
			unset($userIdsList[1]);
		}

		foreach ($userIdsList as $userId) {
			$userRecordModel = Users_Record_Model::getInstanceById($userId, 'Users');
			if ($nonAdmin && $userRecordModel->isAdminUser()) {
				continue;
			}
			$usersList[$userId] = $userRecordModel;
		}
		return $usersList;
	}

	protected function transferOwnership($transferToGroup)
	{
		$db = PearDatabase::getInstance();
		$groupId = $this->getId();
		$transferGroupId = $transferToGroup->getId();

		$query = 'UPDATE vtiger_crmentity SET smownerid=? WHERE smownerid=?';
		$params = array($transferGroupId, $groupId);
		$db->pquery($query, $params);

		if (Vtiger_Utils::CheckTable('vtiger_customerportal_prefs')) {
			$query = 'UPDATE vtiger_customerportal_prefs SET prefvalue = ? WHERE prefkey = ? AND prefvalue = ?';
			$params = array($transferGroupId, 'defaultassignee', $groupId);
			$db->pquery($query, $params);

			$query = 'UPDATE vtiger_customerportal_prefs SET prefvalue = ? WHERE prefkey = ? AND prefvalue = ?';
			$params = array($transferGroupId, 'userid', $groupId);
			$db->pquery($query, $params);
		}

		//update workflow tasks Assigned User from Deleted Group to Transfer Owner
		$newOwnerModel = $this->getInstance($transferGroupId);
		if (!$newOwnerModel) {
			$newOwnerModel = Users_Record_Model::getInstanceById($transferGroupId, 'Users');
		}
		$ownerModel = $this->getInstance($groupId);
		vtws_transferOwnershipForWorkflowTasks($ownerModel, $newOwnerModel);
		vtws_updateWebformsRoundrobinUsersLists($groupId, $transferGroupId);
	}

	/**
	 * Function to delete the group
	 * @param <Settings_Groups_Record_Model> $transferToGroup
	 */
	public function delete($transferToGroup)
	{
		$db = PearDatabase::getInstance();
		$groupId = $this->getId();
		$transferGroupId = $transferToGroup->getId();

		$em = new VTEventsManager($db);
		// Initialize Event trigger cache
		$em->initTriggerCache();

		$entityData = array();
		$entityData['groupid'] = $groupId;
		$entityData['transferToId'] = $transferGroupId;
		$em->triggerEvent("vtiger.entity.beforegroupdelete", $entityData);

		$this->transferOwnership($transferToGroup);

		deleteGroupRelatedSharingRules($groupId);

		$db->pquery('DELETE FROM vtiger_group2grouprel WHERE groupid=?', array($groupId));
		$db->pquery('DELETE FROM vtiger_group2role WHERE groupid=?', array($groupId));
		$db->pquery('DELETE FROM vtiger_group2rs WHERE groupid=?', array($groupId));
		$db->pquery('DELETE FROM vtiger_users2group WHERE groupid=?', array($groupId));
		$db->pquery("DELETE FROM vtiger_reportsharing WHERE shareid=? AND setype='groups'", array($groupId));
		$db->pquery('DELETE FROM vtiger_group2modules WHERE groupid=?', array($groupId));
		$db->pquery('DELETE FROM vtiger_groups WHERE groupid=?', array($groupId));
	}

	/**
	 * Function to get the list view actions for the record
	 * @return <Array> - Associate array of Vtiger_Link_Model instances
	 */
	public function getRecordLinks()
	{

		$links = array();
		$recordLinks = array(
			array(
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => $this->getEditViewUrl(),
				'linkicon' => 'glyphicon glyphicon-pencil'
			),
			array(
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => "javascript:Settings_Vtiger_List_Js.triggerDelete(event,'" . $this->getDeleteActionUrl() . "')",
				'linkicon' => 'glyphicon glyphicon-trash'
			)
		);
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}

		return $links;
	}

	/**
	 * Function to get the instance of Groups record model from query result
	 * @param <Object> $result
	 * @param <Number> $rowNo
	 * @return Settings_Groups_Record_Model instance
	 */
	public static function getInstanceFromQResult($result, $rowNo)
	{
		$db = PearDatabase::getInstance();
		$row = $db->query_result_rowdata($result, $rowNo);
		$role = new self();
		return $role->setData($row);
	}

	/**
	 * Function to get all the groups
	 * @return <Array> - Array of Settings_Groups_Record_Model instances
	 */
	public static function getAll()
	{
		$db = PearDatabase::getInstance();

		$sql = 'SELECT * FROM vtiger_groups';
		$params = array();
		$result = $db->pquery($sql, $params);
		$noOfGroups = $db->num_rows($result);
		$groups = array();
		for ($i = 0; $i < $noOfGroups; ++$i) {
			$group = self::getInstanceFromQResult($result, $i);
			$groups[$group->getId()] = $group;
		}
		return $groups;
	}

	/**
	 * Function to get the instance of Group model, given group id or name
	 * @param <Object> $value
	 * @return Settings_Groups_Record_Model instance, if exists. Null otherwise
	 */
	public static function getInstance($value)
	{
		$db = PearDatabase::getInstance();

		if (Vtiger_Utils::isNumber($value)) {
			$sql = 'SELECT * FROM vtiger_groups WHERE groupid = ?';
		} else {
			$sql = 'SELECT * FROM vtiger_groups WHERE groupname = ?';
		}
		$params = array($value);
		$result = $db->pquery($sql, $params);
		if ($db->num_rows($result) > 0) {
			return self::getInstanceFromQResult($result, 0);
		}
		return null;
	}
	/* Function to get the instance of the group by Name
	 * @param type $name -- name of the group
	 * @return null/group instance
	 */

	public static function getInstanceByName($name, $excludedRecordId = array())
	{
		$db = PearDatabase::getInstance();
		$sql = 'SELECT * FROM vtiger_groups WHERE groupname=?';
		$params = array($name);

		if (!empty($excludedRecordId)) {
			$sql.= ' AND groupid NOT IN (' . generateQuestionMarks($excludedRecordId) . ')';
			$params = array_merge($params, $excludedRecordId);
		}

		$result = $db->pquery($sql, $params);
		if ($db->num_rows($result) > 0) {
			return self::getInstanceFromQResult($result, 0);
		}
		return null;
	}
}
