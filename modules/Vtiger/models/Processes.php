<?php

/**
 * Vtiger processes model class.
 *
 * @package Model
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_Processes_Model
{
	/**
	 * Returns configuration for processes.
	 *
	 * @param string $process
	 * @param string $type
	 * @param string $procesParam
	 *
	 * @return array
	 */
	public static function getConfig($process, $type, $procesParam = false)
	{
		\App\Log::trace('Start ' . __METHOD__ . " | Process: $process, Type: $type");
		$processList = [
			'marketing' => 'yetiforce_proc_marketing',
			'sales' => 'yetiforce_proc_sales',
		];
		$cache = Vtiger_Cache::get('ProcessesModel', $process . $type);
		if ($cache) {
			\App\Log::trace('End ' . __METHOD__);

			return $cache;
		}
		$dataReader = (new \App\Db\Query())->from($processList[$process])->where(['type' => $type])
			->createCommand()->query();
		$config = [];
		while ($row = $dataReader->read()) {
			$param = $row['param'];
			$value = $row['value'];
			if ('users' == $param) {
				$config[$param] = '' == $value ? [] : explode(',', $value);
			} else {
				$config[$param] = $value;
			}
			if (false !== $procesParam && $param == $procesParam) {
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
