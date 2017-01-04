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

class Vtiger_Processes_Model
{

	public static function getConfig($process, $type, $procesParam = false)
	{
		
		\App\Log::trace('Start ' . __METHOD__ . " | Process: $process, Type: $type");
		$db = PearDatabase::getInstance();
		$processList = [
			'marketing' => 'yetiforce_proc_marketing',
			'sales' => 'yetiforce_proc_sales',
		];
		$cache = Vtiger_Cache::get('ProcessesModel', $process . $type);
		if ($cache) {
			\App\Log::trace('End ' . __METHOD__);
			return $cache;
		}

		$result = $db->pquery(sprintf('SELECT * FROM %s WHERE type = ?;', $processList[$process]), [$type]);
		if ($db->num_rows($result) == 0) {
			return [];
		}
		$config = [];
		$numRowsCount = $db->num_rows($result);
		for ($i = 0; $i < $numRowsCount; ++$i) {
			$param = $db->query_result_raw($result, $i, 'param');
			$value = $db->query_result_raw($result, $i, 'value');
			if ($param == 'users') {
				$config[$param] = $value == '' ? [] : explode(',', $value);
			} else {
				$config[$param] = $value;
			}
			if ($procesParam != false && $param == $procesParam) {
				Vtiger_Cache::set('ProcessesModel', $process . $type . $procesParam, $value);
				\App\Log::trace('End ' . __METHOD__);
				return $value;
			}
		}
		Vtiger_Cache::set('ProcessesModel', $process . $type, $config);
		\App\Log::trace('End ' . __METHOD__);
		return $config;
	}
}
