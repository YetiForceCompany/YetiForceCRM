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

/**
 * Roles Record Model Class.
 */
class Settings_Roles_Record_Model extends Settings_Vtiger_Record_Model
{
	/**
	 * Function to get the Id.
	 *
	 * @return <Number> Role Id
	 */
	public function getId()
	{
		return $this->get('roleid');
	}

	/**
	 * Function to get the Role Name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->get('rolename');
	}

	/**
	 * Function to get the depth of the role.
	 *
	 * @return <Number>
	 */
	public function getDepth()
	{
		return $this->get('depth');
	}

	/**
	 * Function to get Parent Role hierarchy as a string.
	 *
	 * @return string
	 */
	public function getParentRoleString()
	{
		return $this->get('parentrole');
	}

	/**
	 * Function to set the immediate parent role.
	 *
	 * @param mixed $parentRole
	 *
	 * @return <Settings_Roles_Record_Model> instance
	 */
	public function setParent($parentRole)
	{
		$this->parent = $parentRole;

		return $this;
	}

	/**
	 * Function to get the immediate parent role.
	 *
	 * @return <Settings_Roles_Record_Model> instance
	 */
	public function getParent()
	{
		if (!isset($this->parent)) {
			$parentRoleString = $this->getParentRoleString();
			$parentComponents = explode('::', $parentRoleString);
			$noOfRoles = \count($parentComponents);
			if ($noOfRoles > 1) {
				$this->parent = self::getInstanceById($parentComponents[$noOfRoles - 2]);
			} else {
				$this->parent = null;
			}
		}
		return $this->parent;
	}

	/**
	 * Function to get the immediate children roles.
	 *
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
			$dataReader->close();
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
			foreach ($parentRoles as $role) {
				if (empty($parentRoleString)) {
					$parentRoleString = $role;
				} else {
					$parentRoleString = $parentRoleString . '::' . $role;
				}
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
			$dataReader->close();
			$this->children = $roles;
		}
		return $this->children;
	}

	/**
	 * Function to get all the children roles.
	 *
	 * @return Settings_Roles_Record_Model[] List of Settings_Roles_Record_Model instances
	 */
	public function getAllChildren()
	{
		$dataReader = (new App\Db\Query())->from('vtiger_role')
			->where(['like', 'parentrole', $this->getParentRoleString() . '::%', false])
			->createCommand()->query();
		$roles = [];
		while ($row = $dataReader->read()) {
			$role = new self();
			$role->setData($row);
			$roles[$role->getId()] = $role;
		}
		$dataReader->close();

		return $roles;
	}

	/**
	 * Function returns profiles related to the current role.
	 *
	 * @return array - profile ids
	 */
	public function getProfileIdList()
	{
		return (new App\Db\Query())->select(['profileid'])->from('vtiger_role2profile')->where(['roleid' => $this->getId()])->column();
	}

	/**
	 * Function to get the profile id if profile is directly related to role.
	 *
	 * @return id
	 */
	public function getDirectlyRelatedProfileId()
	{
		$roleId = $this->getId();
		if (empty($roleId)) {
			return false;
		}
		$row = (new App\Db\Query())->select(['directly_related_to_role', 'vtiger_profile.profileid'])
			->from('vtiger_role2profile')
			->innerJoin('vtiger_profile', 'vtiger_profile.profileid = vtiger_role2profile.profileid')
			->where(['vtiger_role2profile.roleid' => $this->getId()])
			->one();
		if ($row && 1 === (int) $row['directly_related_to_role']) {
			return $row['profileid'];
		}
		return false;
	}

	/**
	 * Function to get the Edit View Url for the Role.
	 *
	 * @return string
	 */
	public function getEditViewUrl()
	{
		return 'index.php?module=Roles&parent=Settings&view=Edit&record=' . $this->getId();
	}

	/**
	 * Function to get the Create Child Role Url for the current role.
	 *
	 * @return string
	 */
	public function getCreateChildUrl()
	{
		return '?module=Roles&parent=Settings&view=Edit&parent_roleid=' . $this->getId();
	}

	/**
	 * Function to get the Delete Action Url for the current role.
	 *
	 * @return string
	 */
	public function getDeleteActionUrl()
	{
		return '?module=Roles&parent=Settings&view=DeleteAjax&record=' . $this->getId();
	}

	/**
	 * Function to get all the profiles associated with the current role.
	 *
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
	 * Function to add a child role to the current role.
	 *
	 * @param <Settings_Roles_Record_Model> $role
	 *
	 * @return Settings_Roles_Record_Model instance
	 */
	public function addChildRole($role)
	{
		$role->setParent($this);
		$role->save();

		return $role;
	}

	/**
	 * Function to move the current role and all its children nodes to the new parent role.
	 *
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

		foreach ($allChildren as $roleModel) {
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
	 * Function to save the role.
	 */
	public function save()
	{
		$db = App\Db::getInstance();
		$roleId = $this->getId();
		$mode = 'edit';
		$rolePreviousData = [];

		if (empty($roleId)) {
			$mode = '';
			$roleIdNumber = $db->getUniqueId('vtiger_role');
			$roleId = 'H' . $roleIdNumber;
		}
		$parentRole = $this->getParent();
		if (null !== $parentRole) {
			$this->set('depth', $parentRole->getDepth() + 1);
			$this->set('parentrole', $parentRole->getParentRoleString() . '::' . $roleId);
		}
		$searchunpriv = $this->get('searchunpriv');
		if (\is_array($searchunpriv)) {
			$searchunpriv = implode(',', $searchunpriv);
		}
		$permissionsRelatedField = $this->get('permissionsrelatedfield');
		if (\is_array($permissionsRelatedField)) {
			$permissionsRelatedField = implode(',', $permissionsRelatedField);
		}
		$values = [
			'rolename' => $this->getName(),
			'parentrole' => $this->getParentRoleString(),
			'depth' => $this->getDepth(),
			'allowassignedrecordsto' => $this->get('allowassignedrecordsto'),
			'assignedmultiowner' => $this->get('assignedmultiowner'),
			'changeowner' => (int) $this->get('changeowner'),
			'searchunpriv' => $searchunpriv,
			'clendarallorecords' => $this->get('clendarallorecords'),
			'listrelatedrecord' => $this->get('listrelatedrecord'),
			'previewrelatedrecord' => $this->get('previewrelatedrecord'),
			'editrelatedrecord' => (int) $this->get('editrelatedrecord'),
			'permissionsrelatedfield' => $permissionsRelatedField,
			'globalsearchadv' => (int) $this->get('globalsearchadv'),
			'auto_assign' => (int) $this->get('auto_assign'),
			'company' => (int) $this->get('company'),
		];
		if ('edit' === $mode) {
			$rolePreviousData = App\PrivilegeUtil::getRoleDetail($roleId);
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
			$this->set('roleid', $roleId);
		}
		$profileIds = $this->get('profileIds');
		if ($rolePreviousData) {
			$oldProfileIds = $this->getProfileIdList();
			if ($rolePreviousData['listrelatedrecord'] != $this->get('listrelatedrecord')
					|| $rolePreviousData['previewrelatedrecord'] != $this->get('previewrelatedrecord')
					|| $rolePreviousData['editrelatedrecord'] != $this->get('editrelatedrecord')
					|| $rolePreviousData['permissionsrelatedfield'] != $permissionsRelatedField
					|| $rolePreviousData['searchunpriv'] != $searchunpriv
				|| ($profileIds && !empty(array_merge(array_diff($profileIds, $oldProfileIds), array_diff($oldProfileIds, $profileIds))))) {
				\App\Privilege::setAllUpdater();
			}
		}
		if (empty($profileIds)) {
			$profiles = $this->getProfiles();
			if (!empty($profiles) && \count($profiles) > 0) {
				$profileIds = array_keys($profiles);
			}
		}
		if (!empty($profileIds)) {
			$noOfProfiles = \count($profileIds);
			if ($noOfProfiles > 0) {
				$db->createCommand()->delete('vtiger_role2profile', ['roleid' => $roleId])->execute();
				for ($i = 0; $i < $noOfProfiles; ++$i) {
					$db->createCommand()->insert('vtiger_role2profile', ['roleid' => $roleId, 'profileid' => $profileIds[$i]])
						->execute();
				}
			}
		}

		\App\Cache::delete(__CLASS__, $roleId);
		\App\Cache::delete('RoleDetail', $roleId);
		\App\Cache::delete('getUsersByCompany', '');
		if ($this->get('company')) {
			\App\Cache::delete('getUsersByCompany', $this->get('company'));
		}
		\App\Cache::delete('getCompanyRoles', '');
		if (isset($rolePreviousData['company'])) {
			\App\Cache::delete('getUsersByCompany', $rolePreviousData['company']);
		}
	}

	/**
	 * Function to delete the role.
	 *
	 * @param <Settings_Roles_Record_Model> $transferToRole
	 */
	public function delete($transferToRole)
	{
		$db = App\Db::getInstance();
		$roleId = $this->getId();
		$transferRoleId = $transferToRole->getId();
		$usersInRole = $this->getUsersIds();
		$db->createCommand()->update('vtiger_user2role', ['roleid' => $transferRoleId], ['roleid' => $roleId])->execute();
		$db->createCommand()->delete('vtiger_role2profile', ['roleid' => $roleId])->execute();
		$db->createCommand()->delete('vtiger_group2role', ['roleid' => $roleId])->execute();
		$db->createCommand()->delete('vtiger_group2rs', ['roleandsubid' => $roleId])->execute();
		//delete handling for sharing rules
		\App\PrivilegeUtil::deleteRelatedSharingRules($roleId, 'Roles');
		$db->createCommand()->delete('vtiger_role', ['roleid' => $roleId])->execute();
		$allChildren = $this->getAllChildren();
		$transferParentRoleSequence = $transferToRole->getParentRoleString();
		$currentParentRoleSequence = $this->getParentRoleString();
		foreach ($allChildren as $roleId => $roleModel) {
			$oldChildParentRoleString = $roleModel->getParentRoleString();
			$newChildParentRoleString = str_replace($currentParentRoleSequence, $transferParentRoleSequence, $oldChildParentRoleString);
			$newChildDepth = \count(explode('::', $newChildParentRoleString)) - 1;
			$roleModel->set('depth', $newChildDepth);
			$roleModel->set('parentrole', $newChildParentRoleString);
			$roleModel->save();
		}
		\App\Privilege::setAllUpdater();
		if (\is_array($usersInRole)) {
			foreach ($usersInRole as $userid) {
				\App\UserPrivilegesFile::createUserPrivilegesfile($userid);
				\App\UserPrivilegesFile::createUserSharingPrivilegesfile($userid);
			}
		}
	}

	/** {@inheritdoc} */
	public function getRecordLinks(): array
	{
		$links = [];
		if ($this->getParent()) {
			$recordLinks = [
				[
					'linktype' => 'LISTVIEWRECORD',
					'linklabel' => 'LBL_EDIT_RECORD',
					'linkurl' => $this->getEditViewUrl(),
					'linkicon' => 'yfi yfi-full-editing-view',
				],
				[
					'linktype' => 'LISTVIEWRECORD',
					'linklabel' => 'LBL_DELETE_RECORD',
					'linkurl' => $this->getDeleteActionUrl(),
					'linkicon' => 'fas fa-trash-alt',
				],
			];
			foreach ($recordLinks as $recordLink) {
				$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
			}
		}
		return $links;
	}

	/**
	 * Function to get all the roles.
	 *
	 * @param bool $baseRole
	 *
	 * @return <Array> list of Role models <Settings_Roles_Record_Model>
	 */
	public static function getAll($baseRole = false)
	{
		$query = (new App\Db\Query())->from('vtiger_role');
		if (!$baseRole) {
			$query->where(['<>', 'depth', 0]);
		}
		$dataReader = $query->orderBy(['parentrole' => SORT_DESC])
			->createCommand()->query();
		$roles = [];
		while ($row = $dataReader->read()) {
			$role = new self();
			$role->setData($row);
			$roles[$role->getId()] = $role;
		}
		$dataReader->close();

		return $roles;
	}

	/**
	 * Function to get the instance of Role model, given role id.
	 *
	 * @param int $roleId
	 *
	 * @return self|null
	 */
	public static function getInstanceById($roleId)
	{
		if (!\App\Cache::staticHas(__CLASS__, $roleId)) {
			$instance = null;
			$row = \App\PrivilegeUtil::getRoleDetail($roleId);
			if ($row) {
				$instance = (new self())->setData($row);
			}
			\App\Cache::staticSave(__CLASS__, $roleId, $instance);
		}
		return \App\Cache::staticGet(__CLASS__, $roleId);
	}

	/**
	 * Function to get the instance of Base Role model.
	 *
	 * @return Settings_Roles_Record_Model instance, if exists. Null otherwise
	 */
	public static function getBaseRole()
	{
		$row = (new App\Db\Query())->from('vtiger_role')->where(['depth' => 0])->one();
		if ($row) {
			$instance = new self();
			$instance->setData($row);

			return $instance;
		}
		return null;
	}

	/** Function to get the instance of the role by Name.
	 * @param type  $name             -- name of the role
	 * @param mixed $excludedRecordId
	 *
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
	 * Function to get ids users in this role.
	 *
	 * @return int[]
	 */
	public function getUsersIds()
	{
		return (new App\Db\Query())->select(['userid'])
			->from('vtiger_user2role')
			->where(['roleid' => $this->getId()])
			->column();
	}

	/**
	 * Function to get Users who are from this role.
	 *
	 * @return Users_Record_Model[] User record models list Users_Record_Model
	 */
	public function getUsers()
	{
		$userIds = $this->getUsersIds();
		$usersList = [];
		foreach ($userIds as $userId) {
			$usersList[$userId] = Users_Record_Model::getInstanceById($userId, 'Users');
		}
		return $usersList;
	}

	/**
	 * Get multi company.
	 *
	 * @return array
	 */
	public function getMultiCompany()
	{
		return (new App\Db\Query())->select(['multicompanyid', 'company_name'])
			->from('u_#__multicompany')
			->where(['mulcomp_status' => 'PLL_ACTIVE'])
			->all();
	}
}
