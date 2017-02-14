<?php
namespace App;

/**
 * Privilege File basic class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class PrivilegeFile
{

	protected static $usersFile = 'user_privileges/users.php';
	protected static $usersFileCache = false;

	/**
	 * Create users privileges file
	 */
	public static function createUsersFile()
	{
		$db = \PearDatabase::getInstance();
		$entityData = Module::getEntityInfo('Users');
		$result = $db->query('SELECT id,first_name,last_name,is_admin,cal_color,status,email1,user_name,deleted FROM vtiger_users');
		$users = [];
		// Get the id and the name.
		while ($row = $db->getRow($result)) {
			$fullName = '';
			foreach ($entityData['fieldnameArr'] as &$field) {
				$fullName .= ' ' . $row[$field];
			}
			$row['fullName'] = trim($fullName);
			$users['id'][$row['id']] = $row;
			$users['userName'][$row['user_name']] = $row['id'];
		}
		file_put_contents(static::$usersFile, '<?php return ' . \vtlib\Functions::varExportMin($users) . ';');
	}

	/**
	 * get general users privileges file
	 * @param string $type
	 * @return array
	 */
	public static function getUser($type)
	{
		if (static::$usersFileCache === false) {
			static::$usersFileCache = require static::$usersFile;
		}
		return isset(static::$usersFileCache[$type]) ? static::$usersFileCache[$type] : false;
	}

	/**
	 * Creates a file with all the user, user-role,user-profile, user-groups informations 
	 * @param $userId -- user id:: Type integer
	 */
	public static function createUserPrivilegesFile($userId)
	{
		$file = ROOT_DIRECTORY . DIRECTORY_SEPARATOR . 'user_privileges' . DIRECTORY_SEPARATOR . "user_privileges_$userId.php";
		$user = [];
		$userInstance = \CRMEntity::getInstance('Users');
		$userInstance->retrieve_entity_info($userId, 'Users');
		$userInstance->column_fields['is_admin'] = $userInstance->is_admin === 'on';
		$entityData = Module::getEntityInfo('Users');
		$displayName = '';
		foreach ($entityData['fieldnameArr'] as &$field) {
			$displayName .= ' ' . $userInstance->column_fields[$field];
		}
		$userRoleInfo = PrivilegeUtil::getRoleDetail($userInstance->column_fields['roleid']);
		$user['details'] = $userInstance->column_fields;
		$user['displayName'] = trim($displayName);
		$user['profiles'] = PrivilegeUtil::getProfilesByRole($userInstance->column_fields['roleid']);
		$user['groups'] = PrivilegeUtil::getUserGroups($userId);
		$user['parent_roles'] = $userRoleInfo['parentRoles'];
		$user['parent_role_seq'] = $userRoleInfo['parentrole'];
		file_put_contents($file, 'return ' . \vtlib\Functions::varExportMin($user) . ';', FILE_APPEND);
	}
}
