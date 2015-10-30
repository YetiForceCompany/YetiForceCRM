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

	public static function getInstance()
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
		for ($i = 0; $i < $db->num_rows($result); ++$i) {
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
		$db = PearDatabase::getInstance();
		$value = $param['val'];
		if (is_array($value)) {
			$value = implode(',', $value);
		}
		$db->pquery('UPDATE yetiforce_auth SET value = ? WHERE type = ? AND param = ?;', [$value, $param['type'], $param['param']]);
		return true;
	}

	public function saveSwitchUsers($data)
	{
		$content = '<?php' . PHP_EOL . '$switchUsersRaw = [';
		$map = [];
		if (count($data)) {
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
			$users = '';
			foreach (array_unique($accessList) as $ID) {
				$users .= "$ID => '" . $this->getUserName($ID) . "',";
			}
			$content .= "'$user'=>[$users],";
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
		if (substr($data, 0, 1) == 'H') {
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
		$entityData = Vtiger_Functions::getEntityModuleInfo('Users');
		$user = new Users();
		$currentUser = $user->retrieveCurrentUserInfoFromFile($id);
		$colums = [];
		foreach (explode(',', $entityData['fieldname']) as &$fieldname) {
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
			$users = '';
			foreach (array_unique($accessList) as $ID) {
				$users .= "$ID => '" . $this->getUserName($ID) . "',";
			}
			$content .= "'$user'=>[$users],";
		}
		$content .= '];';
		$file = 'user_privileges/switchUsers.php';
		file_put_contents($file, $content);
	}
}
