<?php
/**
 * Condition main class.
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
	 */
	public static function validSearchParams(string $moduleName, array $searchParams)
	{
		if (count($searchParams) > 2) {
			throw new Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE');
		}
		$fields = \Vtiger_Module_Model::getInstance($moduleName)->getFields();
		foreach ($searchParams as $params) {
			foreach ($params as $param) {
				$countvariables = count($param);
				if ($countvariables !== 3 && $countvariables !== 4) {
					throw new Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE');
				}
				if (!isset($fields[$param[0]])) {
					throw new Exceptions\IllegalValue('ERR_NOT_ALLOWED_VALUE');
				}
				$fieldModel = $fields[$param[0]];
				$fieldModel->getUITypeModel()->getDbConditionBuilderValue($param[2], $param[1]);
			}
		}
		return $searchParams;
	}
}
