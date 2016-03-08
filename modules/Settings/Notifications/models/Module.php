<?php
/**
 * 
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_Notifications_Module_Model{
	
	static function getListContent($roleId){
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM a_yf_notification_type WHERE role = ?', [$roleId]);
		$list = [];
		while($row = $db->getRow($result)){
			$list[] = Settings_Notifications_Record_Model::getInstanceFromArray($row);
		}
		return $list;
	}
}
