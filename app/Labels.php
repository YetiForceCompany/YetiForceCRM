<?php
/**
 * Labels file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * Labels class.
 */
class Labels
{
	/**
	 * Get member name.
	 *
	 * @param string $member
	 *
	 * @return string
	 */
	public static function member(string $member): string
	{
		$name = '';
		[$type, $id] = explode(':', $member);
		switch ($type) {
			case \App\PrivilegeUtil::MEMBER_TYPE_USERS:
				$name = \App\Fields\Owner::getUserLabel((int) $id) ?: '';
				break;
			case \App\PrivilegeUtil::MEMBER_TYPE_GROUPS:
				$name = \App\Fields\Owner::getGroupName((int) $id) ?: '';
				break;
			case \App\PrivilegeUtil::MEMBER_TYPE_ROLES:
			case \App\PrivilegeUtil::MEMBER_TYPE_ROLE_AND_SUBORDINATES:
				$name = self::role($id);
				break;
			default:
				break;
		}
		return $name;
	}

	/**
	 * Get role name.
	 *
	 * @param string $roleId
	 *
	 * @return string
	 */
	public static function role(string $roleId): string
	{
		return \App\PrivilegeUtil::getRoleDetail($roleId)['rolename'] ?? '';
	}
}
