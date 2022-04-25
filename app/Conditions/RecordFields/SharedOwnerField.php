<?php

namespace App\Conditions\RecordFields;

/**
 * Shared Owner condition record field class.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class SharedOwnerField extends BaseField
{
	/**
	 * Users who belong to the same group as the currently logged in user.
	 *
	 * @return bool
	 */
	public function operatorOgu(): bool
	{
		$result = false;
		$groups = \App\User::getCurrentUserModel()->getGroups();
		if ($groups) {
			$usersByGroups = [];
			foreach ($groups as $groupId) {
				$usersByGroups[] = (new \App\Db\Query())->select(['userid'])->from(["condition_groups_{$groupId}_" . \App\Layout::getUniqueId() => \App\PrivilegeUtil::getQueryToUsersByGroup((int) $groupId)])->column();
			}
			foreach ($usersByGroups as $usersByGroup) {
				if (array_intersect(explode(',', $this->getValue()), $usersByGroup)) {
					$result = true;
					break;
				}
			}
		}
		return $result;
	}
}
