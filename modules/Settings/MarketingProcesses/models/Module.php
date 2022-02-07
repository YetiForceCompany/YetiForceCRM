<?php

/**
 * Settings MarketingProcesses module model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_MarketingProcesses_Module_Model extends \App\Base
{
	public static function getCleanInstance()
	{
		return new self();
	}

	public static function getConfig($type)
	{
		$cache = Vtiger_Cache::get('MarketingProcesses', $type);
		if ($cache) {
			\App\Log::trace('End ' . __METHOD__);

			return $cache;
		}
		$query = (new \App\Db\Query())->from('yetiforce_proc_marketing')->where(['type' => $type]);
		$dataReader = $query->createCommand()->query();
		$noRows = $dataReader->count();
		if (0 === $noRows) {
			return [];
		}
		$config = [];
		while ($row = $dataReader->read()) {
			$param = $row['param'];
			$value = $row['value'];
			if (\in_array($param, ['groups', 'status', 'convert_status'])) {
				$config[$param] = '' == $value ? [] : explode(',', $value);
			} else {
				$config[$param] = $value;
			}
		}
		$dataReader->close();
		Vtiger_Cache::set('MarketingProcesses', $type, $config);

		return $config;
	}

	public static function setConfig($param, $type, $value)
	{
		if (\is_array($value)) {
			$value = implode(',', $value);
		}
		\App\Db::getInstance()->createCommand()->update('yetiforce_proc_marketing', ['value' => $value], ['type' => $type, 'param' => $param])->execute();

		return true;
	}
}
