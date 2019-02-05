<?php
/**
 * Condition main class.
 *
 * @package   App
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */

namespace App;

/**
 * Condition main class.
 */
class Condition
{
	/**
	 * Checks structure search_params.
	 *
	 * @param string $moduleName
	 * @param array  $searchParams
	 *
	 * @throws \App\Exceptions\IllegalValue
	 *
	 * @return array
	 */
	public static function validSearchParams(string $moduleName, array $searchParams): array
	{
		if (count($searchParams) > 2) {
			throw new Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE');
		}
		$fields = \Vtiger_Module_Model::getInstance($moduleName)->getFields();
		$result = [];
		foreach ($searchParams as $params) {
			$tempParam = [];
			foreach ($params as $param) {
				if (empty($param)) {
					continue;
				}
				$countvariables = count($param);
				if ($countvariables !== 3 && $countvariables !== 4) {
					throw new Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE');
				}
				if (!isset($fields[$param[0]])) {
					throw new Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE');
				}
				$fieldModel = $fields[$param[0]];
				$fieldModel->getUITypeModel()->getDbConditionBuilderValue($param[2], $param[1]);
				$tempParam[]= $param;
			}
			$result[]= $tempParam;
		}
		return $result;
	}

	/**
	 * Checks value search_value.
	 *
	 * @param string $value
	 * @param string $moduleName
	 * @param string $fieldName
	 * @param string $operator
	 *
	 * @return string
	 */
	public static function validSearchValue(string $value, string $moduleName, string $fieldName, string $operator): string
	{
		if ($value !== '') {
			\Vtiger_Module_Model::getInstance($moduleName)->getField($fieldName)->getUITypeModel()->getDbConditionBuilderValue($value, $operator);
		}
		return $value;
	}
}
