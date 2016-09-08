<?php

/**
 * 
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Settings_WebserviceApps_Module_Model extends Settings_Vtiger_Module_Model
{

	static public function getTypes()
	{
		return ['Portal', 'POS'];
	}

	static public function getServers()
	{
		$db = PearDatabase::getInstance();
		$query = 'SELECT * FROM w_yf_servers';
		$result = $db->query($query);
		$listServers = [];
		while ($row = $db->getRow($result)) {
			$listServers[$row['id']] = $row;
		}
		return $listServers;
	}

	static public function getActiveServers($type = '')
	{
		$db = PearDatabase::getInstance();
		$query = 'SELECT * FROM w_yf_servers WHERE status = ?';
		$params[] = 1;
		if (!empty($type)) {
			$params[] = $type;
			$query.= ' && type = ?';
		}
		$result = $db->pquery($query, $params);
		$listServers = [];
		while ($row = $db->getRow($result)) {
			$listServers[$row['id']] = $row;
		}
		return $listServers;
	}
}
