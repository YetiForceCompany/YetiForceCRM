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

class Settings_MarketingProcesses_Module_Model extends Vtiger_Base_Model
{

	public static function getCleanInstance()
	{
		$instance = new self();
		return $instance;
	}

	public static function getConfig($type)
	{
		$log = vglobal('log');
		$log->debug('Start ' . __CLASS__ . ':' . __FUNCTION__ . " | Type: $type");
		$cache = Vtiger_Cache::get('MarketingProcesses', $type);
		if ($cache) {
			$log->debug('End ' . __CLASS__ . ':' . __FUNCTION__);
			return $cache;
		}
		$db = PearDatabase::getInstance();

		$result = $db->pquery('SELECT * FROM yetiforce_proc_marketing WHERE type = ?;', [$type]);
		if ($db->num_rows($result) == 0) {
			return [];
		}
		$config = [];
		for ($i = 0; $i < $db->num_rows($result); ++$i) {
			$param = $db->query_result_raw($result, $i, 'param');
			$value = $db->query_result_raw($result, $i, 'value');
			if (in_array($param, ['groups', 'status', 'convert_status'])) {
				$config[$param] = $value == '' ? [] : explode(',', $value);
			} else {
				$config[$param] = $value;
			}
		}
		Vtiger_Cache::set('MarketingProcesses', $type, $config);
		$log->debug('End ' . __CLASS__ . ':' . __FUNCTION__);
		return $config;
	}

	public static function setConfig($param)
	{
		$log = vglobal('log');
		$log->debug('Start ' . __CLASS__ . ':' . __FUNCTION__);
		$db = PearDatabase::getInstance();
		$value = $param['val'];
		if (is_array($value)) {
			$value = implode(',', $value);
		}
		$db->pquery('UPDATE yetiforce_proc_marketing SET value = ? WHERE type = ? && param = ?;', [$value, $param['type'], $param['param']]);
		$log->debug('End ' . __CLASS__ . ':' . __FUNCTION__);
		return true;
	}
}
