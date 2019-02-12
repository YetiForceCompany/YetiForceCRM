<?php
/**
 * Settings SharingAccess RuleMember model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */

/**
 * Sharng Access Vtiger Module Model Class.
 */
class Settings_SharingAccess_RuleMember_Model extends \App\Base
{
	const RULE_MEMBER_TYPE_GROUPS = 'Groups';
	const RULE_MEMBER_TYPE_ROLES = 'Roles';
	const RULE_MEMBER_TYPE_ROLE_AND_SUBORDINATES = 'RoleAndSubordinates';
	const RULE_MEMBER_TYPE_USERS = 'Users';

	/**
	 * Function to get the Qualified Id of the Group RuleMember.
	 *
	 * @return <Number> Id
	 */
	public function getId()
	{
		return $this->get('id');
	}

	public function getIdComponents()
	{
		return explode(':', $$this->getId());
	}

	public function getType()
	{
		$idComponents = $this->getIdComponents();
		if ($idComponents && count($idComponents) > 0) {
			return $idComponents[0];
		}
		return false;
	}

	public function getMemberId()
	{
		$idComponents = $this->getIdComponents();
		if ($idComponents && count($idComponents) > 1) {
			return $idComponents[1];
		}
		return false;
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

	/**
	 * Function to get the Group Name.
	 *
	 * @return string
	 */
	public function getQualifiedName()
	{
		return self::$ruleTypeLabel[$this->getType()] . ' - ' . $this->get('name');
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
	 * Function to get instance of class.
	 *
	 * @param int $qualifiedId
	 *
	 * @return self
	 */
	public static function getInstance($qualifiedId)
	{
		$idComponents = self::getIdComponentsFromQualifiedId($qualifiedId);
		$type = $idComponents[0];
		$memberId = $idComponents[1];
		if ($type === self::RULE_MEMBER_TYPE_GROUPS) {
			$row = (new App\Db\Query())->from('vtiger_groups')
				->where(['groupid' => $memberId])
				->one();
			if ($row) {
				$qualifiedId = self::getQualifiedId(self::RULE_MEMBER_TYPE_GROUPS, $row['groupid']);

				return (new self())->set('id', $qualifiedId)->set('name', $row['groupname']);
			}
		}
		if ($type === self::RULE_MEMBER_TYPE_USERS) {
			$row = (new App\Db\Query())->from('vtiger_users')
				->where(['id' => $memberId])
				->one();
			if ($row) {
				$qualifiedId = self::getQualifiedId(self::RULE_MEMBER_TYPE_USERS, $row['id']);

				return (new self())->set('id', $qualifiedId)->set('name', $row['first_name'] . ' ' . $row['last_name']);
			}
		}
		if ($type === self::RULE_MEMBER_TYPE_ROLES) {
			$row = App\PrivilegeUtil::getRoleDetail($memberId);
			if ($row) {
				$qualifiedId = self::getQualifiedId(self::RULE_MEMBER_TYPE_ROLES, $row['roleid']);

				return (new self())->set('id', $qualifiedId)->set('name', $row['rolename']);
			}
		}
		if ($type === self::RULE_MEMBER_TYPE_ROLE_AND_SUBORDINATES) {
			$row = App\PrivilegeUtil::getRoleDetail($memberId);
			if ($row) {
				$qualifiedId = self::getQualifiedId(self::RULE_MEMBER_TYPE_ROLE_AND_SUBORDINATES, $row['roleid']);

				return (new self())->set('id', $qualifiedId)->set('name', $row['rolename']);
			}
		}
		return false;
	}

	/**
	 * Function to get all the rule members.
	 *
	 * @return <Array> - Array of Settings_SharingAccess_RuleMember_Model instances
	 */
	public static function getAll()
	{
		$rules = [];

		$allGroups = Settings_Groups_Record_Model::getAll();
		foreach ($allGroups as $groupId => $groupModel) {
			$qualifiedId = self::getQualifiedId(self::RULE_MEMBER_TYPE_GROUPS, $groupId);
			$rule = new self();
			$rules[self::RULE_MEMBER_TYPE_GROUPS][$qualifiedId] = $rule->set('id', $qualifiedId)->set('name', $groupModel->getName());
		}

		$allRoles = Settings_Roles_Record_Model::getAll();
		foreach ($allRoles as $roleId => $roleModel) {
			$qualifiedId = self::getQualifiedId(self::RULE_MEMBER_TYPE_ROLES, $roleId);
			$rule = new self();
			$rules[self::RULE_MEMBER_TYPE_ROLES][$qualifiedId] = $rule->set('id', $qualifiedId)->set('name', $roleModel->getName());

			$qualifiedId = self::getQualifiedId(self::RULE_MEMBER_TYPE_ROLE_AND_SUBORDINATES, $roleId);
			$rule = new self();
			$rules[self::RULE_MEMBER_TYPE_ROLE_AND_SUBORDINATES][$qualifiedId] = $rule->set('id', $qualifiedId)->set('name', $roleModel->getName());
		}

		$allUsers = Users_Record_Model::getAll();
		foreach ($allUsers as $userId => $userModel) {
			$qualifiedId = self::getQualifiedId(self::RULE_MEMBER_TYPE_USERS, $userId);
			$rule = new self();
			$rules[self::RULE_MEMBER_TYPE_USERS][$qualifiedId] = $rule->set('id', $qualifiedId)->set('name', $userModel->getDisplayName());
		}
		return $rules;
	}
}
