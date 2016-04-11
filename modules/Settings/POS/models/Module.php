<?php

/**
 * 
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_POS_Module_Model extends Settings_Vtiger_Module_Model
{
	static function getUsers(){
		$db = PearDatabase::getInstance();
		$query = 'SELECT * FROM p_yf_users_pos';
		$result = $db->query($query);
		$listUsers = false;
		while($row = $db->getRow($result)){
			$userModel = Users_Record_Model::getInstanceById($row['userid'], 'Users');
			$row['userModel'] = $userModel;
			$listUsers[] = $row;
		}
		return $listUsers;
	}
	static function getListActions(){
		$db = PearDatabase::getInstance();
		$query = 'SELECT * FROM p_yf_actions_pos';
		$result = $db->query($query);
		$listAction = [];
		while($row = $db->getRow($result)){
			$listAction[$row['id']] = $row;
		}
		return $listAction;
	}
}
