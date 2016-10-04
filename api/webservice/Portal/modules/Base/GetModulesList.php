<?php

/**
 * Get modules list action class
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class API_Base_GetModulesList extends BaseAction
{

	protected $requestMethod = ['get'];

	public function get()
	{
		$db = PearDatabase::getInstance();
		$query = 'SELECT * FROM vtiger_tab WHERE isentitytype = ? && presence = ?';
		$result = $db->pquery($query, [1, 0]);
		$modules = [];
		while ($row = $db->fetch_array($result)) {
			$modules[$row['name']] = vtranslate($row['name']);
		}
		return $modules;
	}
}
