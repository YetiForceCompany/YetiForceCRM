<?php

/**
 * 
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_Notifications_Module_Model extends Settings_Vtiger_Module_Model
{

	public function getModulesList()
	{
		$db = PearDatabase::getInstance();

		$presence = [0, 2];
		$restrictedModules = ['SMSNotifier', 'Emails', 'Integration', 'Dashboard', 'ModComments', 'vtmessages', 'vttwitter'];
		$query = sprintf('SELECT name FROM vtiger_tab WHERE
						presence IN (%s)
						AND isentitytype = ?
						AND name NOT IN (%s)', generateQuestionMarks($presence), generateQuestionMarks($restrictedModules));
		$result = $db->pquery($query, [$presence, 1, $restrictedModules]);
		$numOfRows = $db->num_rows($result);
		$modulesList = [];
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
