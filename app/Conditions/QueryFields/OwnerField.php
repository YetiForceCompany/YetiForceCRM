<?php

namespace App\Conditions\QueryFields;

/**
 * Owner Query Field Class.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class OwnerField extends BaseField
{
	/**
	 * Equals operator.
	 *
	 * @return array
	 */
	public function operatorE(): array
	{
		if (!\is_array($this->value)) {
			$this->value = explode('##', $this->value);
		}
		$condition = ['or'];
		foreach ($this->value as $value) {
			$condition[] = [$this->getColumnName() => $this->getMemberValue($value)];
		}
		return $condition;
	}

	/**
	 * Not equal operator.
	 *
	 * @return array
	 */
	public function operatorN(): array
	{
		if (!\is_array($this->value)) {
			$this->value = explode('##', $this->value);
		}
		$condition = ['and'];
		foreach ($this->value as $value) {
			$condition[] = ['not in', $this->getColumnName(), $this->getMemberValue($value)];
		}
		return $condition;
	}

	/**
	 * Gets conditions for member.
	 *
	 * @param int|string $member
	 *
	 * @return \App\Db\Query|int
	 */
	public function getMemberValue($member)
	{
		if (is_numeric($member)) {
			return $member;
		}
		[$type, $id] = explode(':', $member);
		switch ($type) {
			case \App\PrivilegeUtil::MEMBER_TYPE_GROUPS:
				$value = (new \App\Db\Query())->select(['userid'])->from(["condition_{$type}_{$id}_" . \App\Layout::getUniqueId() => \App\PrivilegeUtil::getQueryToUsersByGroup((int) $id)]);
				break;
			case \App\PrivilegeUtil::MEMBER_TYPE_ROLES:
				$value = \App\PrivilegeUtil::getQueryToUsersByRole($id);
				break;
			case \App\PrivilegeUtil::MEMBER_TYPE_ROLE_AND_SUBORDINATES:
				$value = \App\PrivilegeUtil::getQueryToUsersByRoleAndSubordinate($id);
				break;
			default:
				$value = -1;
				break;
		}
		return $value;
	}

	/**
	 * Currently logged user.
	 *
	 * @return array
	 */
	public function operatorOm()
	{
		return [$this->getColumnName() => \App\User::getCurrentUserId()];
	}

	/**
	 * Currently logged-in user groups.
	 *
	 * @return array
	 */
	public function operatorOgr(): array
	{
		$groups = \App\Fields\Owner::getInstance($this->getModuleName())->getGroups(false, 'private');
		return [$this->getColumnName() => \array_keys($groups)];
	}

	/**
	 * Users who belong to the same group as the currently logged in user.
	 *
	 * @return array
	 */
	public function operatorOgu(): array
	{
		$groups = \App\Fields\Owner::getInstance($this->getModuleName())->getGroups(false, 'private');
		if ($groups) {
			$condition = ['or'];
			foreach (array_keys($groups)  as $idGroup) {
				$condition[] = [$this->getColumnName() => (new \App\Db\Query())->select(['userid'])->from(["condition_groups_{$idGroup}_" . \App\Layout::getUniqueId() => \App\PrivilegeUtil::getQueryToUsersByGroup((int) $idGroup)])];
			}
		} else {
			$condition = [$this->getColumnName() => (new \yii\db\Expression('0=1'))];
		}
		return $condition;
	}

	/**
	 * Watched record.
	 *
	 * @return array
	 */
	public function operatorWr()
	{
		$watchdog = \Vtiger_Watchdog_Model::getInstance($this->getModuleName());
		$condition = [];
		if ($watchdog->isActive()) {
			$this->queryGenerator->addJoin(['LEFT JOIN', 'u_#__watchdog_record', 'vtiger_crmentity.crmid = u_#__watchdog_record.record']);
			if ($watchdog->isWatchingModule()) {
				$condition = ['or', ['u_#__watchdog_record.record' => null], ['not', ['u_#__watchdog_record.userid' => $watchdog->get('userId'), 'u_#__watchdog_record.state' => 0]]];
			} else {
				$condition = ['u_#__watchdog_record.state' => 1, 'u_#__watchdog_record.userid' => $watchdog->get('userId')];
			}
		}
		return $condition;
	}

	/**
	 * Watched record not.
	 *
	 * @return array
	 */
	public function operatorNwr()
	{
		$watchdog = \Vtiger_Watchdog_Model::getInstance($this->getModuleName());
		$condition = [];
		if ($watchdog->isActive()) {
			$this->queryGenerator->addJoin(['LEFT JOIN', 'u_#__watchdog_record', 'vtiger_crmentity.crmid = u_#__watchdog_record.record']);
			if ($watchdog->isWatchingModule()) {
				$condition = ['u_#__watchdog_record.userid' => $watchdog->get('userId'), 'u_#__watchdog_record.state' => 0];
			} else {
				$condition = ['or', ['u_#__watchdog_record.record' => null], ['not', ['u_#__watchdog_record.userid' => $watchdog->get('userId'), 'u_#__watchdog_record.state' => 1]]];
			}
		}
		return $condition;
	}

	/**
	 * Get order by.
	 *
	 * @param mixed $order
	 *
	 * @return array
	 */
	public function getOrderBy($order = false): array
	{
		$this->queryGenerator->addJoin(['LEFT JOIN', 'vtiger_users', 'vtiger_users.id = ' . $this->getColumnName()]);
		$this->queryGenerator->addJoin(['LEFT JOIN', 'vtiger_groups', 'vtiger_groups.groupid = ' . $this->getColumnName()]);
		if ($order && 'DESC' === strtoupper($order)) {
			return ['vtiger_users.last_name' => SORT_DESC, 'vtiger_users.first_name' => SORT_DESC, 'vtiger_groups.groupname' => SORT_DESC];
		}
		return ['vtiger_users.last_name' => SORT_ASC, 'vtiger_users.first_name' => SORT_ASC, 'vtiger_groups.groupname' => SORT_ASC];
	}

	/**
	 * Is not empty operator.
	 *
	 * @return array
	 */
	public function operatorNy(): array
	{
		return ['and',
			['not', [$this->getColumnName() => null]],
			['<>', $this->getColumnName(), 0],
		];
	}

	/**
	 * Is empty operator.
	 *
	 * @return array
	 */
	public function operatorY(): array
	{
		return ['or',
			[$this->getColumnName() => null],
			['=', $this->getColumnName(), 0],
		];
	}

	/**
	 * Not Currently logged user.
	 *
	 * @return array
	 */
	public function operatorNom()
	{
		return ['<>', $this->getColumnName(), \App\User::getCurrentUserId()];
	}
}
