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
class Settings_Groups_Member_Model extends \App\Base
{
	const MEMBER_TYPE_USERS = 'Users';
	const MEMBER_TYPE_GROUPS = 'Groups';
	const MEMBER_TYPE_ROLES = 'Roles';
	const MEMBER_TYPE_ROLE_AND_SUBORDINATES = 'RoleAndSubordinates';

	/**
	 * Function to get the Qualified Id of the Group Member.
	 *
	 * @return <Number> Id
	 */
	public function getId()
	{
		return $this->get('id');
	}

	/**
	 * Function to get the Group Name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->get('name');
	}

	public static function getIdComponentsFromQualifiedId($id)
	{
		return explode(':', $id);
	}

	public static function getQualifiedId($type, $id)
	{
		return $type . ':' . $id;
	}

	/**
	 * Gets members by type.
	 *
	 * @param int         $groupId
	 * @param string|null $type
	 *
	 * @return array
	 */
	public static function getAllByTypeForGroup(int $groupId, ?string $type = null): array
	{
		$tables = [
			self::MEMBER_TYPE_USERS => ['vtiger_users2group', 'userid'],
			self::MEMBER_TYPE_GROUPS => ['vtiger_group2grouprel', 'containsgroupid'],
			self::MEMBER_TYPE_ROLES => ['vtiger_group2role', 'roleid'],
			self::MEMBER_TYPE_ROLE_AND_SUBORDINATES => ['vtiger_group2rs', 'roleandsubid']
		];
		if (null !== $type) {
			$tables = isset($tables[$type]) ? [$type => $tables[$type]] : [];
		}
		$queryAll = null;
		foreach ($tables as $type => $indexes) {
			[$tableName, $index] = $indexes;
			$query = (new App\Db\Query())
				->select(['member' => new \yii\db\Expression("CONCAT('{$type}',':',{$tableName}.{$index})")])
				->from($tableName)
				->where(["{$tableName}.groupid" => $groupId]);
			if ($queryAll) {
				$queryAll->union($query, true);
			} else {
				$queryAll = $query;
			}
		}

		return $queryAll ? $queryAll->column() : [];
	}

	/**
	 * Function to get Detail View Url of this member
	 * return string url.
	 */
	public function getDetailViewUrl()
	{
		[$type, $recordId] = self::getIdComponentsFromQualifiedId($this->getId());
		switch ($type) {
			case 'Users':
				$recordModel = Users_Record_Model::getCleanInstance($type);
				$recordModel->setId($recordId);

				return $recordModel->getDetailViewUrl();
			case 'RoleAndSubordinates':
			case 'Roles':
				$recordModel = new Settings_Roles_Record_Model();
				$recordModel->set('roleid', $recordId);

				return $recordModel->getEditViewUrl();
			case 'Groups':
				$recordModel = new Settings_Groups_Record_Model();
				$recordModel->setId($recordId);

				return $recordModel->getDetailViewUrl();
			default:
				break;
		}
	}

	/**
	 * Function to get all the groups.
	 *
	 * @param mixed $onlyActive
	 *
	 * @return <Array> - Array of Settings_Groups_Record_Model instances
	 */
	public static function getAll($onlyActive = true)
	{
		$members = [];

		$allUsers = Users_Record_Model::getAll($onlyActive);
		foreach ($allUsers as $userId => $userModel) {
			$qualifiedId = self::getQualifiedId(self::MEMBER_TYPE_USERS, $userId);
			$member = new self();
			$members[self::MEMBER_TYPE_USERS][$qualifiedId] = $member->set('id', $qualifiedId)->set('name', $userModel->getName());
		}

		$allGroups = Settings_Groups_Record_Model::getAll();
		foreach ($allGroups as $groupId => $groupModel) {
			$qualifiedId = self::getQualifiedId(self::MEMBER_TYPE_GROUPS, $groupId);
			$member = new self();
			$members[self::MEMBER_TYPE_GROUPS][$qualifiedId] = $member->set('id', $qualifiedId)->set('name', $groupModel->getName());
		}

		$allRoles = Settings_Roles_Record_Model::getAll();
		foreach ($allRoles as $roleId => $roleModel) {
			$qualifiedId = self::getQualifiedId(self::MEMBER_TYPE_ROLES, $roleId);
			$member = new self();
			$members[self::MEMBER_TYPE_ROLES][$qualifiedId] = $member->set('id', $qualifiedId)->set('name', $roleModel->getName());

			$qualifiedId = self::getQualifiedId(self::MEMBER_TYPE_ROLE_AND_SUBORDINATES, $roleId);
			$member = new self();
			$members[self::MEMBER_TYPE_ROLE_AND_SUBORDINATES][$qualifiedId] = $member->set('id', $qualifiedId)->set('name', $roleModel->getName());
		}
		return $members;
	}
}
