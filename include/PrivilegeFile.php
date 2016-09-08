<?php namespace includes;

/**
 * Privilege File basic class
 * @package YetiForce.Include
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class PrivilegeFile
{

	protected static $usersFile = 'user_privileges/users.php';
	protected static $usersFileCache = false;

	public static function createUsersFile()
	{
		$db = \PearDatabase::getInstance();
		$entityData = \includes\Modules::getEntityInfo('Users');
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
		file_put_contents(self::$usersFile, '<?php return ' . \vtlib\Functions::varExportMin($users) . ';');
	}

	public static function getUser($type)
	{
		if (self::$usersFileCache === false) {
			self::$usersFileCache = require self::$usersFile;
		}
		return isset(self::$usersFileCache[$type]) ? self::$usersFileCache[$type] : false;
	}
}
