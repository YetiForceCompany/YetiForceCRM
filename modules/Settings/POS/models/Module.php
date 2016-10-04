<?php

/**
 * 
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_POS_Module_Model extends Settings_Vtiger_Module_Model
{

	static $actions = false;

	static function getUsers()
	{
		$db = PearDatabase::getInstance();
		$query = 'SELECT * FROM w_yf_pos_users';
		$result = $db->query($query);
		$listUsers = false;
		$actions = self::getListActions();
		while ($row = $db->getRow($result)) {
			$userModel = Users_Record_Model::getInstanceById($row['userid'], 'Users');
			$row['userModel'] = $userModel;
			$actionForUser = explode(',', $row['action']);
			foreach ($actionForUser as &$singleAction) {
				$singleAction = $actions[$singleAction];
			}
			$row['action'] = $actionForUser;
			$listUsers[] = $row;
		}
		return $listUsers;
	}

	static function getListActions()
	{
		if (self::$actions) {
			return self::$actions;
		}
		$db = PearDatabase::getInstance();
		$query = 'SELECT * FROM w_yf_pos_actions';
		$result = $db->query($query);
		$listAction = [];
		while ($row = $db->getRow($result)) {
			$listAction[$row['id']] = $row;
		}
		self::$actions = $listAction;
		return $listAction;
	}
}
