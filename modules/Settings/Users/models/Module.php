<?php
/**
 * Settings users module model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */

/**
 * Settings users module model class.
 */
class Settings_Users_Module_Model extends Settings_Vtiger_Module_Model
{
	/**
	 * Get instance.
	 *
	 * @param string $name
	 *
	 * @return \self
	 */
	public static function getInstance($name = 'Settings:Vtiger')
	{
		return new self();
	}

	/**
	 * Get config.
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	public static function getConfig($type)
	{
		$query = (new \App\Db\Query())->from('yetiforce_auth')->where(['type' => $type]);
		$config = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$config[$row['param']] = $row['value'];
		}
		$dataReader->close();
		return $config;
	}

	/**
	 * Set config type, parameter value pair.
	 *
	 * @param array $param
	 *
	 * @return bool
	 */
	public static function setConfig($param)
	{
		$value = $param['val'];
		if (is_array($value)) {
			$value = implode(',', $value);
		}
		App\Db::getInstance()->createCommand()
			->update('yetiforce_auth', ['value' => $value], ['type' => $param['type'], 'param' => $param['param']])
			->execute();

		return true;
	}

	/**
	 * Save configuration about switching between users.
	 *
	 * @param array $data
	 */
	public function saveSwitchUsers($data)
	{
		$map = $switchUsers = $switchUsersRaw = [];
		if (!empty($data) && count($data)) {
			foreach ($data as $row) {
				$switchUsersRaw[$row['user']] = $row['access'];
				$accessList = [];
				if (count($row['access'])) {
					foreach ($row['access'] as $access) {
						$accessList = array_merge($accessList, $this->getUserID($access));
					}
				}
				foreach ($this->getUserID($row['user']) as $user) {
					$map[$user] = array_merge($map[$user] ?? [], $accessList);
				}
			}
		}
		foreach ($map as $user => $accessList) {
			$usersForSort = [];
			$usersForSort[$user] = $this->getUserName($user);
			foreach ($accessList as $ID) {
				$usersForSort[$ID] = $this->getUserName($ID);
			}
			asort($usersForSort);
			$switchUsers[$user] = $usersForSort;
		}
		$content = '<?php' . PHP_EOL .
			'$switchUsersRaw = ' . \App\Utils::varExport($switchUsersRaw) . ';' . PHP_EOL .
			'$switchUsers = ' . \App\Utils::varExport($switchUsers) . ';' . PHP_EOL;
		file_put_contents('user_privileges/switchUsers.php', $content);
	}

	/**
	 * Returns the list of users to switch.
	 *
	 * @return array
	 */
	public function getSwitchUsers()
	{
		require 'user_privileges/switchUsers.php';

		return $switchUsersRaw;
	}

	/**
	 * Users id.
	 *
	 * @var array
	 */
	public static $usersID = [];

	/**
	 * Get user id.
	 *
	 * @param string $data
	 *
	 * @return int
	 */
	public function getUserID($data)
	{
		if (array_key_exists($data, self::$usersID)) {
			return self::$usersID[$data];
		}
		if (substr($data, 0, 1) === 'H') {
			$return = (new \App\Db\Query())->select(['userid'])
				->from('vtiger_user2role')
				->innerJoin('vtiger_users', 'vtiger_users.id = vtiger_user2role.userid')
				->where(['and', ['roleid' => $data], ['<>', 'status', 'Inactive']])
				->column();
		} else {
			$return = [(int) $data];
		}
		self::$usersID[$data] = $return;

		return $return;
	}

	/**
	 * Users array.
	 *
	 * @var array
	 */
	public static $users = [];

	/**
	 * Get user name by id.
	 *
	 * @param int $id
	 *
	 * @return string
	 */
	public function getUserName($id)
	{
		if (array_key_exists($id, self::$users)) {
			return self::$users[$id];
		}
		$entityData = \App\Module::getEntityInfo('Users');
		$userPrivileges = App\User::getPrivilegesFile($id);
		$colums = [];
		foreach ($entityData['fieldnameArr'] as $fieldname) {
			$colums[] = $userPrivileges['user_info'][$fieldname];
		}
		$name = implode(' ', $colums);
		self::$users[$id] = $name;

		return $name;
	}

	/**
	 * Refresh list users to switch.
	 */
	public function refreshSwitchUsers()
	{
		$switchUsersRaw = $this->getSwitchUsers();
		$map = $switchUsers = [];
		if (count($switchUsersRaw)) {
			foreach ($switchUsersRaw as $key => $row) {
				$accessList = [];
				if (count($row)) {
					foreach ($row as $access) {
						$accessList = array_merge($accessList, $this->getUserID($access));
					}
				}
				foreach ($this->getUserID($key) as $user) {
					$map[$user] = array_merge($map[$user] ?? [], $accessList);
				}
			}
		}
		foreach ($map as $user => $accessList) {
			$usersForSort = [];
			$usersForSort[$user] = $this->getUserName($user);
			foreach ($accessList as $ID) {
				$usersForSort[$ID] = $this->getUserName($ID);
			}
			asort($usersForSort);
			$switchUsers[$user] = $usersForSort;
		}
		$content = '<?php' . PHP_EOL .
			'$switchUsersRaw = ' . \App\Utils::varExport($switchUsersRaw) . ';' . PHP_EOL .
			'$switchUsers = ' . \App\Utils::varExport($switchUsers) . ';' . PHP_EOL;
		file_put_contents('user_privileges/switchUsers.php', $content);
	}

	/**
	 * Function to get locks.
	 *
	 * @return array
	 */
	public function getLocks()
	{
		include 'user_privileges/locks.php';

		return $locksRaw;
	}

	/**
	 * Return type of locks.
	 *
	 * @return string[]
	 */
	public function getLocksTypes()
	{
		return [
			'copy' => 'LBL_LOCK_COPY',
			'cut' => 'LBL_LOCK_CUT',
			'paste' => 'LBL_LOCK_PASTE',
			'contextmenu' => 'LBL_LOCK_RIGHT_MENU',
			'selectstart' => 'LBL_LOCK_SELECT_TEXT',
			'drag' => 'LBL_LOCK_DRAG',
		];
	}

	/**
	 * Function to save locks for users.
	 *
	 * @param array $data
	 */
	public function saveLocks($data)
	{
		$oldValues = $this->getLocks();
		$map = $toSave = [];
		if (!empty($data)) {
			foreach ($data as $row) {
				if (empty($row['locks'])) {
					continue;
				}
				if (array_key_exists($row['user'], $toSave)) {
					$toSave[$row['user']] = array_merge($toSave[$row['user']], $row['locks']);
				} else {
					$toSave[$row['user']] = $row['locks'];
				}
			}
			foreach ($toSave as $user => &$locks) {
				$locks = array_unique($locks);
				foreach ($this->getUserID($user) as $userID) {
					$map[$userID] = array_merge($map[$userID] ?? [], $locks);
				}
			}
		}
		$content = '<?php' . PHP_EOL .
			'$locksRaw = ' . \App\Utils::varExport($toSave) . ';' . PHP_EOL .
			'$locks = ' . \App\Utils::varExport($map) . ';';
		file_put_contents('user_privileges/locks.php', $content);
		$newValues = $this->getLocks();
		$difference = vtlib\Functions::arrayDiffAssocRecursive($newValues, $oldValues);
		if (!empty($difference)) {
			foreach ($difference as $id => $locks) {
				if (strpos($id, 'H') === false) {
					$name = Users_Record_Model::getInstanceById($id, 'Users');
				} else {
					$name = Settings_Roles_Record_Model::getInstanceById($id);
				}
				$name = $name->getName();
				if (!empty($oldValues[$id])) {
					$prev[$name] = implode(',', $oldValues[$id]);
				} else {
					$prev[$name] = '';
				}
				$post[$name] = implode(',', $newValues[$id]);
				Settings_Vtiger_Tracker_Model::addDetail($prev, $post);
			}
		}

		$difference = vtlib\Functions::arrayDiffAssocRecursive($oldValues, $newValues);
		if (!empty($difference)) {
			Settings_Vtiger_Tracker_Model::changeType('delete');
			foreach ($difference as $id => $locks) {
				if (strpos($id, 'H') === false) {
					$name = Users_Record_Model::getInstanceById($id, 'Users');
				} else {
					$name = Settings_Roles_Record_Model::getInstanceById($id);
				}
				$name = $name->getName();
				$prev[$name] = implode(',', $oldValues[$id]);
				if (!empty($newValues[$id])) {
					$post[$name] = implode(',', $newValues[$id]);
				} else {
					$post[$name] = '';
				}
				Settings_Vtiger_Tracker_Model::addDetail($prev, $post);
			}
		}
	}
}
