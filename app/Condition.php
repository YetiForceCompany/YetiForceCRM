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
		$searchParamsCount = count($searchParams);
		if ($searchParamsCount > 2) {
			throw new Exceptions\IllegalValue("ERR_NUMBER_OF_ARGUMENTS_NOT_ALLOWED||{$searchParamsCount}|| > 2||" . Utils::varExport($searchParams, true), 406);
		}
		$fields = \Vtiger_Module_Model::getInstance($moduleName)->getFields();
		$result = [];
		foreach ($searchParams as $params) {
			$tempParam = [];
			foreach ($params as $param) {
				if (empty($param)) {
					continue;
				}
				$count = count($param);
				if (3 !== $count && 4 !== $count) {
					throw new Exceptions\IllegalValue("ERR_NUMBER_OF_ARGUMENTS_NOT_ALLOWED||{$count}|| <> 3 or 4||" . Utils::varExport($param, true), 406);
				}
				[$relatedFieldName, $relatedModule, $referenceField] = explode(':', $param[0]);
				if ($relatedModule) {
					$relatedFields = \Vtiger_Module_Model::getInstance($relatedModule)->getFields();
					if (!isset($fields[$referenceField], $relatedFields[$relatedFieldName])) {
						throw new Exceptions\IllegalValue("ERR_FIELD_NOT_FOUND||{$param[0]}||" . Utils::varExport($param, true), 406);
					}
					$fieldModel = $relatedFields[$relatedFieldName];
				} else {
					if (!isset($fields[$param[0]])) {
						throw new Exceptions\IllegalValue("ERR_FIELD_NOT_FOUND||{$param[0]}||" . Utils::varExport($param, true), 406);
					}
					$fieldModel = $fields[$param[0]];
				}
				$fieldModel->getUITypeModel()->getDbConditionBuilderValue($param[2], $param[1]);
				$tempParam[] = $param;
			}
			$result[] = $tempParam;
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
		if ('' !== $value) {
			\Vtiger_Module_Model::getInstance($moduleName)->getField($fieldName)->getUITypeModel()->getDbConditionBuilderValue($value, $operator);
		}
		return $value;
	}
}
