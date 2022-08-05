<?php

/**
 * Settings SalesProcesses module model class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_SalesProcesses_Module_Model extends \App\Base
{
	/**
	 * Return clean instance of self.
	 *
	 * @return \self
	 */
	public static function getCleanInstance()
	{
		return new self();
	}

	/**
	 * Return sales processess configuration array.
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	public static function getConfig($type = false)
	{
		\App\Log::trace('Start ' . __METHOD__ . " | Type: $type");
		$cache = Vtiger_Cache::get('SalesProcesses', false === $type ? 'all' : $type);
		if ($cache) {
			\App\Log::trace('End ' . __METHOD__);

			return $cache;
		}

		$returnArrayForFields = ['groups', 'status', 'calculationsstatus', 'salesstage', 'salesstage', 'assetstatus', 'statuses_close'];
		$query = (new \App\Db\Query())
			->from('yetiforce_proc_sales');
		if ($type) {
			$query->where(['type' => $type]);
		}
		$rows = $query->all();
		if (!$rows) {
			return [];
		}
		$config = [];
		foreach ($rows as $row) {
			$param = $row['param'];
			$value = $row['value'];
			if (\in_array($param, $returnArrayForFields)) {
				$value = '' === $value ? [] : explode(',', $value);
			}
			if ($type) {
				$config[$param] = $value;
			} else {
				$config[$row['type']][$param] = $value;
			}
		}
		Vtiger_Cache::set('SalesProcesses', false === $type ? 'all' : $type, $config);
		\App\Log::trace('End ' . __METHOD__);

		return $config;
	}

	/**
	 * Set sales processess config variable.
	 *
	 * @param array $param
	 *
	 * @return bool
	 */
	public static function setConfig($param)
	{
		\App\Log::trace('Start ' . __METHOD__);
		$value = $param['val'];
		if (\is_array($value)) {
			$value = implode(',', $value);
		}
		App\Db::getInstance()->createCommand()
			->update('yetiforce_proc_sales', ['value' => $value], ['type' => $param['type'], 'param' => $param['param']])
			->execute();
		\App\Log::trace('End ' . __METHOD__);

		return true;
	}

	/**
	 * Checks if products are set to be narrowed to only those related to Opportunity.
	 *
	 * @param mixed $moduleName
	 *
	 * @return bool
	 */
	public static function checkRelatedToPotentialsLimit($moduleName)
	{
		if (!self::isLimitForModule($moduleName)) {
			return false;
		}
		$popup = self::getConfig('popup');
		if ('true' == $popup['limit_product_service']) {
			return true;
		}
		return false;
	}

	/**
	 * Checks if limit can be applied to this module.
	 *
	 * @param mixed $moduleName
	 *
	 * @return bool
	 */
	public static function isLimitForModule($moduleName)
	{
		$validModules = ['SQuotes', 'SCalculations', 'SQuoteEnquiries', 'SRequirementsCards', 'SSingleOrders', 'SRecurringOrders'];

		return \in_array($moduleName, $validModules);
	}
}
