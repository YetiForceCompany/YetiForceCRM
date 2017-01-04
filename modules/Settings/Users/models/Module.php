<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class Settings_Users_Module_Model extends Settings_Vtiger_Module_Model
{

	public static function getInstance($name = 'Settings:Vtiger')
	{
		$instance = new self();
		return $instance;
	}

	public static function getConfig($type)
	{
		$db = PearDatabase::getInstance();

		$result = $db->pquery('SELECT * FROM yetiforce_auth WHERE type = ?;', [$type]);
		if ($db->num_rows($result) == 0) {
			return [];
		}
		$config = [];
		$numRowsCount = $db->num_rows($result);
		for ($i = 0; $i < $numRowsCount; ++$i) {
			$param = $db->query_result_raw($result, $i, 'param');
			$value = $db->query_result_raw($result, $i, 'value');
			if ($param == 'users') {
				$config[$param] = $value == '' ? [] : explode(',', $value);
			} else {
				$config[$param] = $value;
			}
		}
		return $config;
	}

	public static function setConfig($param)
	{
		$value = $param['val'];
		if (is_array($value)) {
			$value = implode(',', $value);
		}
		App\Db::getInstance()->createCommand()
			->update('yetiforce_auth', ['value' => $value], ['type' =>  $param['type'], 'param' => $param['param']])
			->execute();
		return true;
	}

	public function saveSwitchUsers($data)
	{
		$content = '<?php' . PHP_EOL . '$switchUsersRaw = [';
		$map = [];
		if (!empty($data) && count($data)) {
			foreach ($data as $row) {
				$content .= "'" . $row['user'] . "'=>['" . implode("','", $row['access']) . "'],";
				$accessList = [];
				if (count($row['access'])) {
					foreach ($row['access'] as $access) {
						$accessList = array_merge($accessList, $this->getUserID($access));
					}
				}
				foreach ($this->getUserID($row['user']) as $user) {
					$map[$user] = array_merge(isset($map[$user]) ? $map[$user] : [], $accessList);
				}
			}
		}
		$content .= '];' . PHP_EOL . '$switchUsers = [';
		foreach ($map as $user => $accessList) {
			$usersForSort = [];
			foreach ($accessList as $ID) {
				$usersForSort[$ID] = $this->getUserName($ID);
			}
			asort($usersForSort);
			$users = "$user => '" . $this->getUserName($user) . "',";
			foreach ($usersForSort as $ID => $name) {
				$users .= "$ID => '" . $name . "',";
			}
			$content .= "'$user'=>[" . rtrim($users, ',') . "],";
		}
		$content .= '];';
		$file = 'user_privileges/switchUsers.php';
		file_put_contents($file, $content);
	}

	public function getSwitchUsers()
	{
		require('user_privileges/switchUsers.php');
		return $switchUsersRaw;
	}

	public static $usersID = [];

	public function getUserID($data)
	{
		if (key_exists($data, self::$usersID)) {
			return self::$usersID[$data];
		}
		if (substr($data, 0, 1) === 'H') {
			$db = PearDatabase::getInstance();
			$return = [];
			$result = $db->pquery('SELECT userid FROM vtiger_user2role INNER JOIN vtiger_users ON vtiger_users.id = vtiger_user2role.userid WHERE roleid = ? AND deleted=0 AND status <> ?', [$data, 'Inactive']);
			while ($userid = $db->getSingleValue($result)) {
				$return[] = $userid;
			}
		} else {
			$return = [(int) $data];
		}
		self::$usersID[$data] = $return;
		return $return;
	}

	public static $users = [];

	public function getUserName($id)
	{
		if (key_exists($id, self::$users)) {
			return self::$users[$id];
		}
		$entityData = \App\Module::getEntityInfo('Users');
		$user = new Users();
		$currentUser = $user->retrieveCurrentUserInfoFromFile($id);
		$colums = [];
		foreach ($entityData['fieldnameArr'] as &$fieldname) {
			$colums[] = $currentUser->column_fields[$fieldname];
		}
		$name = implode(' ', $colums);
		self::$users[$id] = $name;
		return $name;
	}

	public function refreshSwitchUsers()
	{
		$switchUsers = $this->getSwitchUsers();
		$content = '<?php' . PHP_EOL . '$switchUsersRaw = [';
		$map = [];
		if (count($switchUsers)) {
			foreach ($switchUsers as $key => $row) {
				$content .= "'" . $key . "'=>['" . implode("','", $row) . "'],";
				$accessList = [];
				if (count($row)) {
					foreach ($row as $access) {
						$accessList = array_merge($accessList, $this->getUserID($access));
					}
				}
				foreach ($this->getUserID($key) as $user) {
					$map[$user] = array_merge(isset($map[$user]) ? $map[$user] : [], $accessList);
				}
			}
		}

		$content .= '];' . PHP_EOL . '$switchUsers = [';
		foreach ($map as $user => $accessList) {
			$usersForSort = [];
			foreach ($accessList as $ID) {
				$usersForSort[$ID] = $this->getUserName($ID);
			}
			asort($usersForSort);
			$users = "$user => '" . $this->getUserName($user) . "',";
			foreach ($usersForSort as $ID => $name) {
				$users .= "$ID => '" . $name . "',";
			}
			$content .= "'$user'=>[" . rtrim($users, ',') . "],";
		}
		$content .= '];';
		$file = 'user_privileges/switchUsers.php';
		file_put_contents($file, $content);
	}

	public function getLocks()
	{
		include('user_privileges/locks.php');
		return $locksRaw;
	}

	public function getLocksTypes()
	{
		return [
			'copy' => 'LBL_LOCK_COPY',
			'cut' => 'LBL_LOCK_CUT',
			'paste' => 'LBL_LOCK_PASTE',
			'contextmenu' => 'LBL_LOCK_RIGHT_MENU',
			'selectstart' => 'LBL_LOCK_SELECT_TEXT',
			'drag' => 'LBL_LOCK_DRAG'
		];
	}

	public function saveLocks($data)
	{
		$oldValues = $this->getLocks();
		$content = '<?php' . PHP_EOL . '$locksRaw = [';
		$map = $toSave = [];
		if (!empty($data)) {
			foreach ($data as &$row) {
				if (empty($row['locks'])) {
					continue;
				}
				if (key_exists($row['user'], $toSave)) {
					$toSave[$row['user']] = array_merge($toSave[$row['user']], $row['locks']);
				} else {
					$toSave[$row['user']] = $row['locks'];
				}
			}
			foreach ($toSave as $user => &$locks) {
				$locks = array_unique($locks);
				$content .= "'" . $user . "'=>['" . implode("','", $locks) . "'],";
				foreach ($this->getUserID($user) as $userID) {
					$map[$userID] = array_merge(isset($map[$userID]) ? $map[$userID] : [], $locks);
				}
			}
		}
		$content = rtrim($content, ',');
		$content .= '];' . PHP_EOL . '$locks = [';
		foreach ($map as $user => &$lockList) {
			$userLocks = '';
			foreach ($lockList as $name) {
				$userLocks .= "'" . $name . "',";
			}
			$content .= "$user=>[" . rtrim($userLocks, ',') . "],";
		}
		$content = rtrim($content, ',');
		$content .= '];';
		$file = 'user_privileges/locks.php';
		file_put_contents($file, $content);
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
				if ($oldValues[$id])
					$prev[$name] = implode(',', $oldValues[$id]);
				else
					$prev[$name] = '';
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
				if ($newValues[$id])
					$post[$name] = implode(',', $newValues[$id]);
				else
					$post[$name] = '';
				Settings_Vtiger_Tracker_Model::addDetail($prev, $post);
			}
		}
	}
}
