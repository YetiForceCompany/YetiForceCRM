<?php
/**
 * Privilege File basic class.
 *
 * @package App
 *
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author  Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author  Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App;

/**
 * PrivilegeFile class.
 */
class PrivilegeFile
{
	protected static $usersFile = 'user_privileges/users.php';
	protected static $usersFileCache = false;

	/**
	 * Create users privileges file.
	 */
	public static function createUsersFile()
	{
		$entityData = Module::getEntityInfo('Users');
		$dataReader = (new \App\Db\Query())->select(['id', 'first_name', 'last_name', 'is_admin', 'cal_color', 'status', 'email1', 'user_name', 'deleted'])->from('vtiger_users')->createCommand()->query();
		$users = [];
		// Get the id and the name.
		while ($row = $dataReader->read()) {
			$fullName = '';
			foreach ($entityData['fieldnameArr'] as $field) {
				$fullName .= ' ' . $row[$field];
			}
			$row['fullName'] = trim($fullName);
			$users['id'][$row['id']] = array_map('\App\Purifier::encodeHtml', $row);
			$users['userName'][$row['user_name']] = $row['id'];
		}
		Utils::saveToFile(static::$usersFile, $users, '', 0, true);
	}

	/**
	 * get general users privileges file.
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	public static function getUser($type)
	{
		if (false === static::$usersFileCache) {
			static::$usersFileCache = require static::$usersFile;
		}
		return static::$usersFileCache[$type] ?? false;
	}

	/**
	 * Creates a file with all the user, user-role,user-profile, user-groups informations.
	 *
	 * @param int $userId
	 */
	public static function createUserPrivilegesFile($userId)
	{
		$file = ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . 'user_privileges' . \DIRECTORY_SEPARATOR . "user_privileges_$userId.php";
		$user = [];
		$userInstance = \CRMEntity::getInstance('Users');
		$userInstance->retrieveEntityInfo($userId, 'Users');
		$userInstance->column_fields['is_admin'] = 'on' === $userInstance->is_admin;

		$exclusionEncodeHtml = ['currency_symbol', 'date_format', 'currency_id', 'currency_decimal_separator', 'currency_grouping_separator', 'othereventduration', 'imagename'];
		foreach ($userInstance->column_fields as $field => $value) {
			if (!\in_array($field, $exclusionEncodeHtml)) {
				$userInstance->column_fields[$field] = is_numeric($value) ? $value : \App\Purifier::encodeHtml($value);
			}
		}

		$displayName = '';
		foreach (Module::getEntityInfo('Users')['fieldnameArr'] as $field) {
			$displayName .= ' ' . $userInstance->column_fields[$field];
		}
		$userRoleInfo = PrivilegeUtil::getRoleDetail($userInstance->column_fields['roleid']);
		$user['details'] = $userInstance->column_fields;
		$user['displayName'] = trim($displayName);
		$user['profiles'] = PrivilegeUtil::getProfilesByRole($userInstance->column_fields['roleid']);
		$user['groups'] = PrivilegeUtil::getAllGroupsByUser($userId);
		$user['leadersByGroup'] = PrivilegeUtil::getLeadersGroupByUserId($userId);
		$user['leader'] = PrivilegeUtil::getGroupsWhereUserIsLeader($userId);
		$user['parent_roles'] = $userRoleInfo['parentRoles'];
		$user['parent_role_seq'] = $userRoleInfo['parentrole'];
		$user['roleName'] = $userRoleInfo['rolename'];

		$logo = null;
		if (Record::isExists($userRoleInfo['company'], 'MultiCompany')) {
			$multiCompany = \Vtiger_Record_Model::getInstanceById($userRoleInfo['company'], 'MultiCompany');
			$logo = Json::isEmpty($multiCompany->get('logo')) ? [] : current(Json::decode($multiCompany->get('logo')));
			$user['multiCompanyId'] = $multiCompany->getId();
		} else {
			$user['multiCompanyId'] = null;
		}
		$user['multiCompanyLogo'] = $logo;
		$user['multiCompanyLogoUrl'] = $logo ? "file.php?module=MultiCompany&action=Logo&record={$userId}&key={$logo['key']}" : '';
		file_put_contents($file, 'return ' . Utils::varExport($user) . ';' . PHP_EOL, FILE_APPEND);
	}
}
