<?php

namespace App\Conditions\RecordFields;

/**
 * Owner condition record field class.
 *
 * @package UIType
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OwnerField extends BaseField
{
	/**
	 * Is watching record operator.
	 *
	 * @return array
	 */
	public function operatorWr()
	{
		return \Vtiger_Watchdog_Model::getInstanceById($this->recordModel->getId(), $this->recordModel->getModuleName())->isWatchingRecord();
	}

	/**
	 * Is not watching record operator.
	 *
	 * @return array
	 */
	public function operatorNwr()
	{
		return !\Vtiger_Watchdog_Model::getInstanceById($this->recordModel->getId(), $this->recordModel->getModuleName())->isWatchingRecord();
	}

	/** {@inheritdoc} */
	public function operatorE()
	{
		if (!\is_array($this->value)) {
			$this->value = explode('##', $this->value);
		}
		$result = false;
		foreach ($this->value as $value) {
			if (\in_array($this->getValue(), $this->getMemberValue($value))) {
				$result = true;
				break;
			}
		}
		return $result;
	}

	/** {@inheritdoc} */
	public function operatorN()
	{
		if (!\is_array($this->value)) {
			$this->value = explode('##', $this->value);
		}
		$result = true;
		foreach ($this->value as $value) {
			if (\in_array($this->getValue(), $this->getMemberValue($value))) {
				$result = false;
				break;
			}
		}
		return $result;
	}

	/**
	 * Gets conditions for member.
	 *
	 * @param int|string $member
	 *
	 * @return int[]
	 */
	public function getMemberValue($member): array
	{
		if (is_numeric($member)) {
			return [$member];
		}
		[$type, $id] = explode(':', $member);
		switch ($type) {
			case \App\PrivilegeUtil::MEMBER_TYPE_GROUPS:
				$value = (new \App\Db\Query())->select(['userid'])->from(["condition_{$type}_{$id}_" . \App\Layout::getUniqueId() => \App\PrivilegeUtil::getQueryToUsersByGroup((int) $id)])->column();
				break;
			case \App\PrivilegeUtil::MEMBER_TYPE_ROLES:
				$value = \App\PrivilegeUtil::getQueryToUsersByRole($id)->column();
				break;
			case \App\PrivilegeUtil::MEMBER_TYPE_ROLE_AND_SUBORDINATES:
				$value = \App\PrivilegeUtil::getQueryToUsersByRoleAndSubordinate($id)->column();
				break;
			default:
				$value = [-1];
				break;
		}
		return $value;
	}
}
