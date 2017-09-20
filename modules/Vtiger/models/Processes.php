<?php

/**
 * Vtiger processes model class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
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
		if ($db->numRows($result) == 0) {
			return [];
		}
		$config = [];
		$numRowsCount = $db->numRows($result);
		for ($i = 0; $i < $numRowsCount; ++$i) {
			$param = $db->queryResultRaw($result, $i, 'param');
			$value = $db->queryResultRaw($result, $i, 'value');
			if ($param == 'users') {
				$config[$param] = $value == '' ? [] : explode(',', $value);
			} else {
				$config[$param] = $value;
			}
			if ($procesParam !== false && $param == $procesParam) {
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
