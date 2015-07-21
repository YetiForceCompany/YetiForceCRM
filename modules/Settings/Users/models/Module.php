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
}
