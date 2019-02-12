<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/**
 * Roles Record Model Class.
 */
class Settings_Groups_Record_Model extends Settings_Vtiger_Record_Model
{
	/**
	 * Function to get the Id.
	 *
	 * @return <Number> Group Id
	 */
	public function getId()
	{
		return $this->get('groupid');
	}

	/**
	 * Function to set the Id.
	 *
	 * @param  <Number> Group Id
	 *
	 * @return <Settings_Groups_Reord_Model> instance
	 */
	public function setId($id)
	{
		return $this->set('groupid', $id);
	}

	/**
	 * Function to get the Group Name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->get('groupname');
	}

	/**
	 * Function to get the description of the group.
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return $this->get('description');
	}

	/**
	 * Function to get the Edit View Url for the Group.
	 *
	 * @return string
	 */
	public function getEditViewUrl()
	{
		return '?module=Groups&parent=Settings&view=Edit&record=' . $this->getId();
	}

	/**
	 * Function to get the Delete Action Url for the current group.
	 *
	 * @return string
	 */
	public function getDeleteActionUrl()
	{
		return 'index.php?module=Groups&parent=Settings&view=DeleteAjax&record=' . $this->getId();
	}

	/**
	 * Function to get the Detail Url for the current group.
	 *
	 * @return string
	 */
	public function getDetailViewUrl()
	{
		return '?module=Groups&parent=Settings&view=Detail&record=' . $this->getId();
	}

	/**
	 * Function to get all the members of the groups.
	 *
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
	 * Function to get the Modules.
	 *
	 * @return <Array>
	 */
	public function getModules()
	{
		if (!isset($this->modules)) {
			$groupId = $this->getId();
			if (empty($groupId)) {
				return [];
			}
			$dataReader = (new App\Db\Query())->select(['vtiger_tab.tabid', 'vtiger_tab.name'])
				->from('vtiger_group2modules')
				->innerJoin('vtiger_tab', 'vtiger_tab.tabid = vtiger_group2modules.tabid')
				->where(['vtiger_group2modules.groupid' => $groupId])
				->createCommand()->query();
			$modules = [];
			while ($row = $dataReader->read()) {
				$modules[$row['tabid']] = $row['name'];
			}
			$dataReader->close();
			$this->modules = $modules;
		}
		return $this->modules;
	}

	/**
	 * Function to save the role.
	 */
	public function save()
	{
		$db = App\Db::getInstance();
		$groupId = $this->getId();
		$mode = 'edit';
		$oldUsersList = $this->getUsersList();

		if (empty($groupId)) {
			$mode = '';
			$groupId = $db->getUniqueId('vtiger_users');
			$this->setId($groupId);
		}

		if ($mode == 'edit') {
			$db->createCommand()->update('vtiger_groups', [
				'groupname' => $this->getName(),
				'description' => $this->getDescription(),
			], ['groupid' => $groupId])->execute();
		} else {
			$db->createCommand()->insert('vtiger_groups', [
				'groupid' => $groupId,
				'groupname' => $this->getName(),
				'description' => $this->getDescription(),
			])->execute();
		}
		$members = $this->get('group_members');
		if (is_array($members)) {
			$db->createCommand()->delete('vtiger_users2group', ['groupid' => $groupId])->execute();
			$db->createCommand()->delete('vtiger_group2grouprel', ['groupid' => $groupId])->execute();
			$db->createCommand()->delete('vtiger_group2role', ['groupid' => $groupId])->execute();
			$db->createCommand()->delete('vtiger_group2rs', ['groupid' => $groupId])->execute();

			$noOfMembers = count($members);
			for ($i = 0; $i < $noOfMembers; ++$i) {
				$id = $members[$i];
				$idComponents = Settings_Groups_Member_Model::getIdComponentsFromQualifiedId($id);
				if ($idComponents && count($idComponents) == 2) {
					$memberType = $idComponents[0];
					$memberId = $idComponents[1];

					if ($memberType == Settings_Groups_Member_Model::MEMBER_TYPE_USERS) {
						$db->createCommand()->insert('vtiger_users2group', ['userid' => $memberId, 'groupid' => $groupId])->execute();
					}
					if ($memberType == Settings_Groups_Member_Model::MEMBER_TYPE_GROUPS) {
						$db->createCommand()->insert('vtiger_group2grouprel', ['containsgroupid' => $memberId, 'groupid' => $groupId])->execute();
					}
					if ($memberType == Settings_Groups_Member_Model::MEMBER_TYPE_ROLES) {
						$db->createCommand()->insert('vtiger_group2role', ['roleid' => $memberId, 'groupid' => $groupId])->execute();
					}
					if ($memberType == Settings_Groups_Member_Model::MEMBER_TYPE_ROLE_AND_SUBORDINATES) {
						$db->createCommand()->insert('vtiger_group2rs', ['roleandsubid' => $memberId, 'groupid' => $groupId])->execute();
					}
				}
			}
		}
		$modules = $this->get('modules');
		if (is_array($modules)) {
			$oldModules = array_flip($this->getModules());
			$removed = array_diff($oldModules, $modules);
			$add = array_diff($modules, $oldModules);

			foreach ($removed as $moduleName => &$tabId) {
				$db->createCommand()->delete('vtiger_group2modules', ['groupid' => $groupId, 'tabid' => $tabId])->execute();
				\App\Privilege::setUpdater($moduleName);
			}
			foreach ($add as &$tabId) {
				$db->createCommand()->insert('vtiger_group2modules', ['groupid' => $groupId, 'tabid' => $tabId])->execute();
				\App\Privilege::setUpdater(\App\Module::getModuleName($tabId));
			}
		}
		\App\Cache::clear();
		$this->recalculate($oldUsersList);
		$eventHandler = new App\EventHandler();
		$eventHandler->setParams([
			'groupsRecordModel' => $this,
			'oldUsersList' => $oldUsersList,
			'removedModules' => $removed,
			'addModules' => $add,
		]);
		$eventHandler->trigger('GroupAfterSave');
	}

	/**
	 * Function to recalculate user priviliges files.
	 *
	 * @param <Array> $oldUsersList
	 */
	public function recalculate($oldUsersList)
	{
		$php_max_execution_time = \AppConfig::main('php_max_execution_time');
		set_time_limit($php_max_execution_time);

		$userIdsList = [];
		foreach ($oldUsersList as $userId => $userRecordModel) {
			$userIdsList[$userId] = $userId;
		}

		$this->members = null;
		foreach ($this->getUsersList() as $userId => $userRecordModel) {
			$userIdsList[$userId] = $userId;
		}

		foreach ($userIdsList as $userId) {
			\App\UserPrivilegesFile::createUserPrivilegesfile($userId);
		}
	}

	/**
	 * Function to get all users related to this group.
	 *
	 * @param bool $nonAdmin true/false
	 *
	 * @return <Array> Users models list <Users_Record_Model>
	 */
	public function getUsersList($nonAdmin = false)
	{
		$userIdsList = [];
		$members = $this->getMembers();

		if (isset($members['Users'])) {
			foreach ($members['Users'] as $memberModel) {
				$userId = $memberModel->get('userId');
				$userIdsList[$userId] = $userId;
			}
		}

		if (isset($members['Groups'])) {
			foreach ($members['Groups'] as $memberModel) {
				$groupModel = self::getInstance($memberModel->get('groupId'));
				$groupMembers = $groupModel->getMembers();

				foreach ($groupMembers['Users'] as $groupMemberModel) {
					$userId = $groupMemberModel->get('userId');
					$userIdsList[$userId] = $userId;
				}
			}
		}

		if (isset($members['Roles'])) {
			foreach ($members['Roles'] as $memberModel) {
				$roleModel = new Settings_Roles_Record_Model();
				$roleModel->set('roleid', $memberModel->get('roleId'));

				$roleUsers = $roleModel->getUsers();
				foreach ($roleUsers as $userId => $userRecordModel) {
					$userIdsList[$userId] = $userId;
				}
			}
		}

		if (isset($members['RoleAndSubordinates'])) {
			foreach ($members['RoleAndSubordinates'] as $memberModel) {
				$roleModel = Settings_Roles_Record_Model::getInstanceById($memberModel->get('roleId'));
				$roleUsers = $roleModel->getUsers();
				foreach ($roleUsers as $userId => $userRecordModel) {
					$userIdsList[$userId] = $userId;
				}
				$childernRoles = $roleModel->getAllChildren();
				foreach ($childernRoles as $role) {
					$childRoleModel = new Settings_Roles_Record_Model();
					$childRoleModel->set('roleid', $role->getId());

					$roleUsers = $childRoleModel->getUsers();
					foreach ($roleUsers as $userId => $userRecordModel) {
						$userIdsList[$userId] = $userId;
					}
				}
			}
		}
		if ($nonAdmin) {
			foreach ($userIdsList as $key => $userId) {
				$userRecordModel = Users_Record_Model::getInstanceById($userId, 'Users');
				if ($userRecordModel->isAdminUser()) {
					unset($userIdsList[$key]);
				}
			}
		}
		return $userIdsList;
	}

	/**
	 * TransferOwnership.
	 *
	 * @param Settings_Groups_Record_Model|Users_Record_Model $transferToGroup
	 */
	protected function transferOwnership($transferToGroup)
	{
		$groupId = $this->getId();
		$transferGroupId = $transferToGroup->getId();

		App\Db::getInstance()->createCommand()->update('vtiger_crmentity', ['smownerid' => $transferGroupId], ['smownerid' => $groupId])->execute();
		App\Fields\Owner::transferOwnership($groupId, $transferGroupId);
	}

	/**
	 * Function to delete the group.
	 *
	 * @param Settings_Groups_Record_Model $transferToGroup
	 */
	public function delete($transferToGroup)
	{
		$db = App\Db::getInstance();
		$groupId = $this->getId();
		$eventHandler = new App\EventHandler();
		$eventHandler->setParams(['groupId' => $groupId, 'transferToGroup' => $transferToGroup]);
		$eventHandler->trigger('GroupBeforeDelete');
		$this->transferOwnership($transferToGroup);
		\App\PrivilegeUtil::deleteRelatedSharingRules($groupId, 'Groups');
		$db->createCommand()->delete('vtiger_group2grouprel', ['groupid' => $groupId])->execute();
		$db->createCommand()->delete('vtiger_group2role', ['groupid' => $groupId])->execute();
		$db->createCommand()->delete('vtiger_group2rs', ['groupid' => $groupId])->execute();
		$db->createCommand()->delete('vtiger_users2group', ['groupid' => $groupId])->execute();
		$db->createCommand()->delete('vtiger_group2modules', ['groupid' => $groupId])->execute();
		$db->createCommand()->delete('vtiger_groups', ['groupid' => $groupId])->execute();
		\App\Cache::clear();
	}

	/**
	 * Function to get the list view actions for the record.
	 *
	 * @return <Array> - Associate array of Vtiger_Link_Model instances
	 */
	public function getRecordLinks()
	{
		$links = [];
		$recordLinks = [
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => $this->getEditViewUrl(),
				'linkicon' => 'fas fa-edit',
				'linkclass' => 'btn-sm btn-primary'
			],
			[
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => "javascript:Settings_Vtiger_List_Js.triggerDelete(event,'" . $this->getDeleteActionUrl() . "')",
				'linkicon' => 'fas fa-trash-alt',
				'linkclass' => 'btn-sm btn-danger'
			],
		];
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}
		return $links;
	}

	/**
	 * Function to get all the groups.
	 *
	 * @return <Array> - Array of Settings_Groups_Record_Model instances
	 */
	public static function getAll()
	{
		$dataReader = (new App\Db\Query())->from('vtiger_groups')->createCommand()->query();
		$groups = [];
		while ($row = $dataReader->read()) {
			$group = new self();
			$group->setData($row);
			$groups[$group->getId()] = $group;
		}
		$dataReader->close();

		return $groups;
	}

	/**
	 * Function to get the instance of Group model, given group id or name.
	 *
	 * @param <Object> $value
	 *
	 * @return Settings_Groups_Record_Model instance, if exists. Null otherwise
	 */
	public static function getInstance($value)
	{
		if (vtlib\Utils::isNumber($value)) {
			$dataReader = (new App\Db\Query())->from('vtiger_groups')->where(['groupid' => $value])->createCommand()->query();
		} else {
			$dataReader = (new App\Db\Query())->from('vtiger_groups')->where(['groupname' => $value])->createCommand()->query();
		}
		if ($dataReader->count() > 0) {
			$role = new self();
			$role->setData($dataReader->read());

			return $role;
		}
		return null;
	}

	/* Function to get the instance of the group by Name
	 * @param type $name -- name of the group
	 * @return null/group instance
	 */

	public static function getInstanceByName($name, $excludedRecordId = [])
	{
		$query = new App\Db\Query();
		$query->from('vtiger_groups')->where(['groupname' => $name]);
		$containsEmpty = in_array('', $excludedRecordId, true);

		if (!empty($excludedRecordId && !$containsEmpty)) {
			$query->andWhere(['not in', 'groupid', $excludedRecordId]);
		}
		$dataReader = $query->createCommand()->query();
		if ($dataReader->count() > 0) {
			$role = new self();
			$role->setData($dataReader->read());

			return $role;
		}
		return null;
	}

	public function getDisplayData()
	{
		$data = $this->getData();
		$modules = [];
		if (!is_array($data['modules'])) {
			$data['modules'] = [$data['modules']];
		}
		if (!is_array($data['group_members'])) {
			$data['group_members'] = [$data['group_members']];
		}
		foreach ($data['modules'] as $tabId) {
			$modules[] = \App\Module::getModuleName($tabId);
		}
		$modules = implode(',', $modules);
		$data['modules'] = $modules;
		$groupMembers = [];
		foreach ($data['group_members'] as $member) {
			$info = explode(':', $member);
			if ($info[0] == 'Users') {
				$userModel = Users_Record_Model::getInstanceById($info[1], 'Users');
				$groupMembers[] = $userModel->getName();
			}
			if ($info[0] == 'Roles' || $info[0] == 'RoleAndSubordinates') {
				$roleModel = Settings_Roles_Record_Model::getInstanceById($info[1]);
				$groupMembers[] = $roleModel->getName();
			}
			if ($info[0] == 'Groups') {
				$groupModel = self::getInstance($info[1]);
				$groupMembers[] = $groupModel->getName();
			}
		}
		$data['group_members'] = implode(',', $groupMembers);

		return $data;
	}
}
