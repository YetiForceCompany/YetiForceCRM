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
		$dataReader = (new \App\Db\Query())->from('w_#__pos_users')
			->createCommand()->query();
		$listUsers = false;
		$actions = self::getListActions();
		while ($row = $dataReader->read()) {
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
		$dataReader = (new \App\Db\Query())->from('w_#__pos_actions')
			->createCommand()->query();
		$listAction = [];
		while ($row = $dataReader->read()) {
			$listAction[$row['id']] = $row;
		}
		self::$actions = $listAction;
		return $listAction;
	}
}
