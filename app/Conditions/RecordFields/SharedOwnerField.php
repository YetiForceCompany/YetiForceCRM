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
	public function operatorOgrU(): bool
	{
		$result = false;
		$groups = \App\Fields\Owner::getInstance($this->recordModel->getModuleName())->getGroups(false, 'private');
		$usersByGroup = [];
		if ($groups) {
			foreach (array_keys($groups)  as $idGroup) {
				$usersByGroup = (new \App\Db\Query())->select(['userid'])->from(["condition_groups_{$idGroup}_" . \App\Layout::getUniqueId() => \App\PrivilegeUtil::getQueryToUsersByGroup((int) $idGroup)])->column();
			}
		}
		foreach (explode(',', $this->getValue()) as $userValue) {
			if (in_array($userValue, $usersByGroup)) {
				$result = true;
			}
		}
		return $result;
	}
}
