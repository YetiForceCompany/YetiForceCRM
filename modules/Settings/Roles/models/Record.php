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
 * Roles Record Model Class
 */
class Settings_Roles_Record_Model extends Settings_Vtiger_Record_Model
{

	/**
	 * Function to get the Id
	 * @return <Number> Role Id
	 */
	public function getId()
	{
		return $this->get('roleid');
	}

	/**
	 * Function to get the Role Name
	 * @return string
	 */
	public function getName()
	{
		return $this->get('rolename');
	}

	/**
	 * Function to get the depth of the role
	 * @return <Number>
	 */
	public function getDepth()
	{
		return $this->get('depth');
	}

	/**
	 * Function to get Parent Role hierarchy as a string
	 * @return string
	 */
	public function getParentRoleString()
	{
		return $this->get('parentrole');
	}

	/**
	 * Function to set the immediate parent role
	 * @return <Settings_Roles_Record_Model> instance
	 */
	public function setParent($parentRole)
	{
		$this->parent = $parentRole;
		return $this;
	}

	/**
	 * Function to get the immediate parent role
	 * @return <Settings_Roles_Record_Model> instance
	 */
	public function getParent()
	{
		if (!isset($this->parent)) {
			$parentRoleString = $this->getParentRoleString();
			$parentComponents = explode('::', $parentRoleString);
			$noOfRoles = count($parentComponents);
			if ($noOfRoles > 1) {
				$this->parent = self::getInstanceById($parentComponents[$noOfRoles - 2]);
			} else {
				$this->parent = null;
			}
		}
		return $this->parent;
	}

	/**
	 * Function to get the immediate children roles
	 * @return <Array> - List of Settings_Roles_Record_Model instances
	 */
	public function getChildren()
	{
		if (!isset($this->children)) {
			$parentRoleString = $this->getParentRoleString();
			$currentRoleDepth = $this->getDepth();

			$dataReader = (new \App\Db\Query())->from('vtiger_role')
					->where(['like', 'parentrole', $parentRoleString . '::%', false])
					->andWhere(['depth' => $currentRoleDepth + 1])
					->createCommand()->query();
			$roles = [];
			while ($row = $dataReader->read()) {
				$role = new self();
				$role->setData($row);
				$roles[$role->getId()] = $role;
			}
			$this->children = $roles;
		}
		return $this->children;
	}

	public function getSameLevelRoles()
	{
		if (!isset($this->children)) {
			$parentRoles = \App\PrivilegeUtil::getParentRole($this->getId());
			$currentRoleDepth = $this->getDepth();
			$parentRoleString = '';
			foreach ($parentRoles as $key => $role) {
				if (empty($parentRoleString))
					$parentRoleString = $role;
				else
					$parentRoleString = $parentRoleString . '::' . $role;
			}
			$dataReader = (new \App\Db\Query())->from('vtiger_role')
					->where(['like', 'parentrole', $parentRoleString . '::%', false])
					->andWhere(['depth' => $currentRoleDepth])
					->createCommand()->query();

			$roles = [];
			while ($row = $dataReader->read()) {
				$role = new self();
				$role->setData($row);
				$roles[$role->getId()] = $role;
			}
			$this->children = $roles;
		}
		return $this->children;
	}

	/**
	 * Function to get all the children roles
	 * @return <Array> - List of Settings_Roles_Record_Model instances
	 */
	public function getAllChildren()
	{
		$db = PearDatabase::getInstance();

		$parentRoleString = $this->getParentRoleString();

		$sql = 'SELECT * FROM vtiger_role WHERE parentrole LIKE ?';
		$params = array($parentRoleString . '::%');
		$result = $db->pquery($sql, $params);
		$roles = [];
		while ($row = $db->getRow($result)) {
			$role = new self();
			$role->setData($row);
			$roles[$role->getId()] = $role;
		}
		return $roles;
	}

	/**
	 * Function returns profiles related to the current role
	 * @return <Array> - profile ids
	 */
	public function getProfileIdList()
	{

		$db = PearDatabase::getInstance();
		$query = 'SELECT profileid FROM vtiger_role2profile WHERE roleid=?';

		$result = $db->pquery($query, array($this->getId()));
		$num_rows = $db->num_rows($result);

		$profilesList = [];
		for ($i = 0; $i < $num_rows; $i++) {
			$profilesList[] = $db->query_result($result, $i, 'profileid');
		}
		return $profilesList;
	}

	/**
	 * Function to get the profile id if profile is directly related to role
	 * @return id
	 */
	public function getDirectlyRelatedProfileId()
	{
		$roleId = $this->getId();
		if (empty($roleId)) {
			return false;
		}

		$db = PearDatabase::getInstance();

		$query = 'SELECT directly_related_to_role, vtiger_profile.profileid FROM vtiger_role2profile 
                  INNER JOIN vtiger_profile ON vtiger_profile.profileid = vtiger_role2profile.profileid 
                  WHERE vtiger_role2profile.roleid=?';
		$params = array($this->getId());

		$result = $db->pquery($query, $params);

		if ($db->num_rows($result) == 1 && $db->query_result($result, 0, 'directly_related_to_role') == '1') {
			return $db->query_result($result, 0, 'profileid');
		}
		return false;
	}

	/**
	 * Function to get the Edit View Url for the Role
	 * @return string
	 */
	public function getEditViewUrl()
	{
		return 'index.php?module=Roles&parent=Settings&view=Edit&record=' . $this->getId();
	}

	/**
	 * Function to get the Create Child Role Url for the current role
	 * @return string
	 */
	public function getCreateChildUrl()
	{
		return '?module=Roles&parent=Settings&view=Edit&parent_roleid=' . $this->getId();
	}

	/**
	 * Function to get the Delete Action Url for the current role
	 * @return string
	 */
	public function getDeleteActionUrl()
	{
		return '?module=Roles&parent=Settings&view=DeleteAjax&record=' . $this->getId();
	}

	/**
	 * Function to get the Popup Window Url for the current role
	 * @return string
	 */
	public function getPopupWindowUrl()
	{
		return 'module=Roles&parent=Settings&view=Popup&src_record=' . $this->getId();
	}

	/**
	 * Function to get all the profiles associated with the current role
	 * @return <Array> Settings_Profiles_Record_Model instances
	 */
	public function getProfiles()
	{
		if (!isset($this->profiles)) {
			$this->profiles = Settings_Profiles_Record_Model::getAllByRole($this->getId());
		}
		return $this->profiles;
	}

	/**
	 * Function to add a child role to the current role
	 * @param <Settings_Roles_Record_Model> $role
	 * @return Settings_Roles_Record_Model instance
	 */
	public function addChildRole($role)
	{
		$role->setParent($this);
		$role->save();
		return $role;
	}

	/**
	 * Function to move the current role and all its children nodes to the new parent role
	 * @param <Settings_Roles_Record_Model> $newParentRole
	 */
	public function moveTo($newParentRole)
	{
		$currentDepth = $this->getDepth();
		$currentParentRoleString = $this->getParentRoleString();

		$newDepth = $newParentRole->getDepth() + 1;
		$newParentRoleString = $newParentRole->getParentRoleString() . '::' . $this->getId();

		$depthDifference = $newDepth - $currentDepth;
		$allChildren = $this->getAllChildren();

		$this->set('depth', $newDepth);
		$this->set('parentrole', $newParentRoleString);
		$this->set('allowassignedrecordsto', $this->get('allowassignedrecordsto'));
		$this->save();

		foreach ($allChildren as $roleId => $roleModel) {
			$oldChildDepth = $roleModel->getDepth();
			$newChildDepth = $oldChildDepth + $depthDifference;

			$oldChildParentRoleString = $roleModel->getParentRoleString();
			$newChildParentRoleString = str_replace($currentParentRoleString, $newParentRoleString, $oldChildParentRoleString);

			$roleModel->set('depth', $newChildDepth);
			$roleModel->set('parentrole', $newChildParentRoleString);
			$roleModel->set('allowassignedrecordsto', $roleModel->get('allowassignedrecordsto'));
			$roleModel->save();
		}
	}

	/**
	 * Function to save the role
	 */
	public function save()
	{
		$db = App\Db::getInstance();
		$roleId = $this->getId();
		$mode = 'edit';

		if (empty($roleId)) {
			$mode = '';
			$roleIdNumber = $db->getUniqueId('vtiger_role');
			$roleId = 'H' . $roleIdNumber;
		}
		$parentRole = $this->getParent();
		if ($parentRole != null) {
			$this->set('depth', $parentRole->getDepth() + 1);
			$this->set('parentrole', $parentRole->getParentRoleString() . '::' . $roleId);
		}
		$searchunpriv = $this->get('searchunpriv');
		$searchunpriv = implode(',', empty($searchunpriv) ? [] : $searchunpriv);
		$permissionsRelatedField = $this->get('permissionsrelatedfield');
		$permissionsRelatedField = implode(',', empty($permissionsRelatedField) ? [] : $permissionsRelatedField);
		$values = [
			'rolename' => $this->getName(),
			'parentrole' => $this->getParentRoleString(),
			'depth' => $this->getDepth(),
			'allowassignedrecordsto' => $this->get('allowassignedrecordsto'),
			'assignedmultiowner' => $this->get('assignedmultiowner'),
			'changeowner' => (int) $this->get('change_owner'),
			'searchunpriv' => $searchunpriv,
			'clendarallorecords' => $this->get('clendarallorecords'),
			'listrelatedrecord' => $this->get('listrelatedrecord'),
			'previewrelatedrecord' => $this->get('previewrelatedrecord'),
			'editrelatedrecord' => (int) $this->get('editrelatedrecord'),
			'permissionsrelatedfield' => $permissionsRelatedField,
			'globalsearchadv' => (int) $this->get('globalsearchadv'),
			'auto_assign' => (int) $this->get('auto_assign')
		];
		if ($mode == 'edit') {
			$db->createCommand()->update('vtiger_role', $values, ['roleid' => $roleId])
				->execute();
		} else {
			$values['roleid'] = $roleId;
			$db->createCommand()->insert('vtiger_role', $values)->execute();
			$insertedData = (new App\Db\Query())
				->select([new \yii\db\Expression($db->quoteValue($roleId)), 'picklistvalueid', 'picklistid', 'sortid'])
				->from('vtiger_role2picklist')
				->where(['roleid' => $parentRole->getId()])
				->all();

			$db->createCommand()->batchInsert('vtiger_role2picklist', ['roleid', 'picklistvalueid', 'picklistid', 'sortid'], $insertedData)->execute();
		}
		$profileIds = $this->get('profileIds');
		$oldRole = Vtiger_Cache::get('RolesArray', $roleId);
		if ($oldRole !== false) {
			$oldProfileIds = array_keys($this->getProfiles());
			if ($profileIds === false || !empty(array_merge(array_diff($profileIds, $oldProfileIds), array_diff($oldProfileIds, $profileIds))) ||
				$oldRole['listrelatedrecord'] != $this->get('listrelatedrecord') ||
				$oldRole['previewrelatedrecord'] != $this->get('previewrelatedrecord') ||
				$oldRole['editrelatedrecord'] != $this->get('editrelatedrecord') ||
				$oldRole['permissionsrelatedfield'] != $permissionsRelatedField ||
				$oldRole['searchunpriv'] != $searchunpriv) {
				\App\Privilege::setAllUpdater();
			}
		}
		if (empty($profileIds)) {
			$profiles = $this->getProfiles();
			if (!empty($profiles) && count($profiles) > 0) {
				$profileIds = array_keys($profiles);
			}
		}
		if (!empty($profileIds)) {
			$noOfProfiles = count($profileIds);
			if ($noOfProfiles > 0) {
				$db->createCommand()->delete('vtiger_role2profile', ['roleid' => $roleId])->execute();
				for ($i = 0; $i < $noOfProfiles; ++$i) {
					$db->createCommand()->insert('vtiger_role2profile', ['roleid' => $roleId, 'profileid' => $profileIds[$i]])
						->execute();
				}
			}
		}
	}

	/**
	 * Function to delete the role
	 * @param <Settings_Roles_Record_Model> $transferToRole
	 */
	public function delete($transferToRole)
	{
		$db = PearDatabase::getInstance();
		$roleId = $this->getId();
		$transferRoleId = $transferToRole->getId();

		$db->pquery('UPDATE vtiger_user2role SET roleid=? WHERE roleid=?', array($transferRoleId, $roleId));

		$db->pquery('DELETE FROM vtiger_role2profile WHERE roleid=?', array($roleId));
		$db->pquery('DELETE FROM vtiger_group2role WHERE roleid=?', array($roleId));
		$db->pquery('DELETE FROM vtiger_group2rs WHERE roleandsubid=?', array($roleId));
		/*
		  $noOfUsers = $db->num_rows($user_result);
		  $array_users = [];
		  if($noOfUsers > 0) {
		  for($i=0; $i<$noOfUsers; ++$i) {
		  $array_users[] = $db->query_result($user_result, $i, 'userid');
		  }
		  }
		 */
		//delete handling for sharing rules
		deleteRoleRelatedSharingRules($roleId);

		$db->pquery('DELETE FROM vtiger_role WHERE roleid=?', array($roleId));

		$allChildren = $this->getAllChildren();
		$transferParentRoleSequence = $transferToRole->getParentRoleString();
		$currentParentRoleSequence = $this->getParentRoleString();

		foreach ($allChildren as $roleId => $roleModel) {
			$oldChildParentRoleString = $roleModel->getParentRoleString();
			$newChildParentRoleString = str_replace($currentParentRoleSequence, $transferParentRoleSequence, $oldChildParentRoleString);
			$newChildDepth = count(explode('::', $newChildParentRoleString)) - 1;
			$roleModel->set('depth', $newChildDepth);
			$roleModel->set('parentrole', $newChildParentRoleString);
			$roleModel->save();
		}
		if (is_array($array_users)) {
			require_once('modules/Users/CreateUserPrivilegeFile.php');
			foreach ($array_users as $userid) {
				createUserPrivilegesfile($userid);
				createUserSharingPrivilegesfile($userid);
			}
		}
		\App\Privilege::setAllUpdater();
	}

	/**
	 * Function to get the list view actions for the record
	 * @return <Array> - Associate array of Vtiger_Link_Model instances
	 */
	public function getRecordLinks()
	{

		$links = [];
		if ($this->getParent()) {
			$recordLinks = array(
				array(
					'linktype' => 'LISTVIEWRECORD',
					'linklabel' => 'LBL_EDIT_RECORD',
					'linkurl' => $this->getListViewEditUrl(),
					'linkicon' => 'glyphicon glyphicon-pencil'
				),
				array(
					'linktype' => 'LISTVIEWRECORD',
					'linklabel' => 'LBL_DELETE_RECORD',
					'linkurl' => $this->getDeleteActionUrl(),
					'linkicon' => 'glyphicon glyphicon-trash'
				)
			);
			foreach ($recordLinks as $recordLink) {
				$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
			}
		}

		return $links;
	}

	/**
	 * Function to get all the roles
	 * @param boolean $baseRole
	 * @return <Array> list of Role models <Settings_Roles_Record_Model>
	 */
	public static function getAll($baseRole = false)
	{
		$db = PearDatabase::getInstance();
		$params = [];

		$sql = 'SELECT * FROM vtiger_role';
		if (!$baseRole) {
			$sql .= ' WHERE depth != ?';
			$params[] = 0;
		}
		$sql .= ' ORDER BY parentrole';

		$result = $db->pquery($sql, $params);

		$roles = [];
		while ($row = $db->getRow($result)) {
			$role = new self();
			$role->setData($row);
			$roles[$role->getId()] = $role;
		}
		return $roles;
	}

	/**
	 * Function to get the instance of Role model, given role id
	 * @param <Integer> $roleId
	 * @return Settings_Roles_Record_Model instance, if exists. Null otherwise
	 */
	public static function getInstanceById($roleId)
	{
		$instance = Vtiger_Cache::get('Settings_Roles_Record_Model', $roleId);
		if ($instance !== false) {
			return $instance;
		}
		$row = App\PrivilegeUtil::getRoleDetail($roleId);
		if ($row) {
			$instance = new self();
			$instance->setData($row);
			Vtiger_Cache::set('Settings_Roles_Record_Model', $roleId, $instance);
			Vtiger_Cache::set('RolesArray', $roleId, $row);
			return $instance;
		}
		return $instance;
	}

	/**
	 * Function to get the instance of Base Role model
	 * @return Settings_Roles_Record_Model instance, if exists. Null otherwise
	 */
	public static function getBaseRole()
	{
		$db = PearDatabase::getInstance();

		$result = $db->query('SELECT * FROM vtiger_role WHERE depth=0 LIMIT 1');
		if ($db->getRowCount($result) > 0) {
			$instance = new self();
			$instance->setData($db->getRow($result));
			return $instance;
		}
		return null;
	}
	/* Function to get the instance of the role by Name
	 * @param type $name -- name of the role
	 * @return null/role instance
	 */

	public static function getInstanceByName($name, $excludedRecordId = [])
	{
		$query = (new App\Db\Query())->from('vtiger_role')->where(['rolename' => $name]);
		if (!empty($excludedRecordId)) {
			$query->andWhere(['NOT IN', 'roleid', $excludedRecordId]);
		}
		$row = $query->one();
		if ($row) {
			$instance = new self();
			$instance->setData($row);
			return $instance;
		}
		return null;
	}

	/**
	 * Function to get Users who are from this role
	 * @return <Array> User record models list <Users_Record_Model>
	 */
	public function getUsers()
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT userid FROM vtiger_user2role WHERE roleid = ?', array($this->getId()));
		$numOfRows = $db->num_rows($result);

		$usersList = [];
		for ($i = 0; $i < $numOfRows; $i++) {
			$userId = $db->query_result($result, $i, 'userid');
			$usersList[$userId] = Users_Record_Model::getInstanceById($userId, 'Users');
		}
		return $usersList;
	}
}
