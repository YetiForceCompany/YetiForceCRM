<?php

/**
 * 
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_Notifications_Module_Model extends Settings_Vtiger_Module_Model
{

	static function getListContent($roleId)
	{
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT * FROM a_yf_notification_type WHERE role = ?', [$roleId]);
		$list = [];
		while ($row = $db->getRow($result)) {
			$list[] = Settings_Notifications_Record_Model::getInstanceFromArray($row);
		}
		return $list;
	}

	public function getModulesList()
	{
		$db = PearDatabase::getInstance();

		$presence = array(0, 2);
		$restrictedModules = array('SMSNotifier', 'Emails', 'Integration', 'Dashboard', 'ModComments', 'vtmessages', 'vttwitter');

		$query = sprintf('SELECT name FROM vtiger_tab WHERE
						presence IN (%s)
						AND isentitytype = ?
						AND name NOT IN (%s)', generateQuestionMarks($presence), generateQuestionMarks($restrictedModules));
		$result = $db->pquery($query, array($presence, 1, $restrictedModules));
		$numOfRows = $db->num_rows($result);
		$modulesList = array();
		for ($i = 0; $i < $numOfRows; $i++) {
			$moduleName = $db->query_result($result, $i, 'name');
			$modulesList[$moduleName] = $moduleName;
		}
		if (!array_key_exists('Calendar', $modulesList)) {
			unset($modulesList['Events']);
		}
		return $modulesList;
	}
}
