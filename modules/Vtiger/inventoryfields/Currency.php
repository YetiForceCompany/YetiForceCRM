<?php

/**
 * Inventory Currency Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Currency_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'Currency';
	protected $defaultLabel = 'LBL_CURRENCY';
	protected $columnName = 'currency';
	protected $dbType = [\yii\db\Schema::TYPE_INTEGER, 11];
	protected $customColumn = [
		'currencyparam' => [\yii\db\Schema::TYPE_STRING, 1024],
	];
	protected $blocks = [0];
	protected $maximumLength = '-2147483648,2147483647';
	protected $customMaximumLength = [
		'currencyparam' => 1024
	];
	protected $purifyType = \App\Purifier::INTEGER;
	protected $customPurifyType = [
		'currencyparam' => App\Purifier::TEXT
	];

	/** {@inheritdoc} */
	public function getEditTemplateName()
	{
		return 'inventoryTypes/Currency.tpl';
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		if (empty($value)) {
			return '';
		}
		return \App\Fields\Currency::getById($value)['currency_name'];
	}

	/**
	 * Gets currency param.
	 *
	 * @param array  $currencies
	 * @param string $param
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return array
	 */
	public function getCurrencyParam(array $currencies, $param = '')
	{
		$params = [];
		if ($param) {
			$params = \App\Json::decode($param);
		}
		foreach ($currencies as $currency) {
			if (!isset($params[$currency['id']])) {
				$params[$currency['id']] = vtlib\Functions::getConversionRateInfo($currency['id']);
			}
		}
		return $params;
	}

	/** {@inheritdoc} */
	public function getDBValue($value, ?string $name = '')
	{
		if ($name === $this->getColumnName()) {
			$value = (int) $value;
		}
		return $value;
	}

	/** {@inheritdoc} */
	public function validate($value, string $columnName, bool $isUserFormat, $originalValue = null)
	{
		if ($columnName === $this->getColumnName()) {
			if (!is_numeric($value) || !isset(\App\Fields\Currency::getAll()[$value])) {
				throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||" . print_r($value, true), 406);
			}
		} elseif (!\is_array($value) && \App\TextUtils::getTextLength($value) > $this->customMaximumLength[$columnName]) {
			throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||" . print_r($value, true), 406);
		}
	}
}
