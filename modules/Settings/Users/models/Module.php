<?php
/**
 * Settings users module model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		if (\is_array($value)) {
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
	 *
	 * @return void
	 */
	public function saveSwitchUsers($data): void
	{
		$switchUsers = $switchUsersRaw = [];
		if (!empty($data) && \count($data)) {
			foreach ($data as $row) {
				$switchUsersRaw[$row['user']] = $row['access'];
				$accessList = [];
				if (\count($row['access'])) {
					foreach ($row['access'] as $access) {
						$accessList = array_merge($accessList, $this->getUserID($access));
					}
				}
				foreach ($this->getUserID($row['user']) as $user) {
					$switchUsers[$user] = array_merge($switchUsers[$user] ?? [], $accessList);
				}
			}
		}
		$content = '$switchUsersRaw = ' . \App\Utils::varExport($switchUsersRaw) . ';' . PHP_EOL .
			'$switchUsers = ' . \App\Utils::varExport($switchUsers) . ';';
		\App\Utils::saveToFile(ROOT_DIRECTORY . '/user_privileges/switchUsers.php', $content, 'File generated from the panel');
	}

	/**
	 * Returns the list of users to switch.
	 *
	 * @return array
	 */
	public function getSwitchUsers(): array
	{
		require ROOT_DIRECTORY . '/user_privileges/switchUsers.php';
		return $switchUsersRaw ?? [];
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
	 * @return int[]
	 */
	public function getUserID($data): array
	{
		if (\array_key_exists($data, self::$usersID)) {
			return self::$usersID[$data];
		}
		if ('H' === substr($data, 0, 1)) {
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
				if (\array_key_exists($row['user'], $toSave)) {
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
		$content = '$locksRaw = ' . \App\Utils::varExport($toSave) . ';' . PHP_EOL .
			'$locks = ' . \App\Utils::varExport($map) . ';';
		\App\Utils::saveToFile('user_privileges/locks.php', $content);
		$newValues = $this->getLocks();
		$difference = vtlib\Functions::arrayDiffAssocRecursive($newValues, $oldValues);
		if (!empty($difference)) {
			foreach ($difference as $id => $locks) {
				if (false === strpos($id, 'H')) {
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
				if (false === strpos($id, 'H')) {
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
