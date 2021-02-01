<?php
/**
 * UIType multi currency.
 *
 * @package   UIType
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * UIType MultiCurrency Field Class.
 */
class Vtiger_MultiCurrency_UIType extends Vtiger_Base_UIType
{
	/** {@inheritdoc} */
	public function getDBValue($value, $recordModel = false)
	{
		$data = \App\Json::isEmpty($value) ? [] : \App\Json::decode($value);
		foreach ($data['currencies'] ?? [] as $key => $currency) {
			$data['currencies'][$key]['price'] = App\Fields\Double::formatToDb($currency['price']);
		}
		return \App\Json::encode($data);
	}

	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value)) {
			return;
		}
		if (\is_string($value)) {
			$value = \App\Json::decode($value);
		}
		if (!\is_array($value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$currencies = \App\Fields\Currency::getAll(true);
		foreach ($value['currencies'] ?? [] as $id => $currency) {
			if (!isset($currencies[$id])) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $id, 406);
			}
			$price = $currency['price'];
			if ($isUserFormat) {
				$price = App\Fields\Double::formatToDb($price);
			}
			if (!is_numeric($price)) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $price, 406);
			}
		}
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		if (($value = (\App\Json::isEmpty($value) ? 0 : \App\Json::decode($value))) && \is_array($value)) {
			$currencyId = $value['currencyId'];
			$value = App\Fields\Double::formatToDisplay($value['currencies'][$currencyId]['price']);
			$currencySymbol = \App\Fields\Currency::getById($currencyId)['currency_symbol'];
			$value = CurrencyField::appendCurrencySymbol($value, $currencySymbol);
		}
		return \App\Purifier::encodeHtml($value);
	}

	/**
	 * Gets base currency.
	 *
	 * @param string $value
	 *
	 * @return int|null
	 */
	public function getBaseCurrency($value): ?int
	{
		return \App\Json::isEmpty($value) ? null : \App\Json::decode($value)['currencyId'];
	}

	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		if ($value = (\App\Json::isEmpty($value) ? 0 : \App\Json::decode($value))) {
			$currencyId = $value['currencyId'];
			$value = App\Fields\Double::formatToDisplay($value['currencies'][$currencyId]['price'], false);
		}
		return \App\Purifier::encodeHtml($value);
	}

	/**
	 * Convert data.
	 *
	 * @param string $value
	 *
	 * @return array
	 */
	public function getEditViewFormatData($value)
	{
		if ($data = ($value ? \App\Json::decode($value) : [])) {
			foreach ($data['currencies'] ?? [] as $key => $currency) {
				$data['currencies'][$key]['price'] = App\Fields\Double::formatToDisplay($currency['price'], false);
			}
		}
		return $data;
	}

	/**
	 * Gets currencies data.
	 *
	 * @return void
	 */
	public function getCurrencies()
	{
		$priceDetails = [];
		$params = ['uitype' => 71, 'displaytype' => 1, 'typeofdata' => 'N~O', 'isEditableReadOnly' => false, 'maximumlength' => '99999999999999999'];
		$fieldModel = new \Vtiger_Field_Model();
		$fieldModel->setModule($this->getFieldModel()->getModule());
		$fieldInfo = $fieldModel->setData($params)->getFieldInfo();
		foreach (\App\Fields\Currency::getAll(true) as $id => $currency) {
			$name = "currencies[$id]['value']";
			$fieldInfo['name'] = $name;
			$priceDetails[$id] = [
				'name' => $name,
				'conversionRate' => $currency['conversion_rate'],
				'symbol' => $currency['currency_symbol'],
				'currencyName' => $currency['currency_name'],
				'fieldInfo' => $fieldInfo
			];
		}
		return $priceDetails;
	}

	/**
	 * Get value for the currency.
	 *
	 * @param string $value
	 * @param int    $currencyId
	 *
	 * @return float
	 */
	public function getValueForCurrency(string $value, int $currencyId): float
	{
		$result = 0;
		if ($value = (\App\Json::isEmpty($value) ? 0 : \App\Json::decode($value))) {
			$rate = 1;
			if (!isset($value['currencies'][$currencyId])) {
				$currencyInfo = \App\Fields\Currency::getById($currencyId);
				$currencyId = $value['currencyId'];
				$baseRate = 1 / \App\Fields\Currency::getById($currencyId)['conversion_rate'];
				$rate = $baseRate * $currencyInfo['conversion_rate'];
			}
			$result = $value['currencies'][$currencyId]['price'] * $rate;
		}
		return $result;
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['y', 'ny'];
	}

	/** {@inheritdoc} */
	public function getValueToExport($value, int $recordId)
	{
		$result = [];
		$value = \App\Json::isEmpty($value) ? [] : \App\Json::decode($value);
		foreach ($value['currencies'] ?? [] as $key => $currency) {
			$currencyName = \App\Fields\Currency::getById($key)['currency_name'];
			$result['currencies'][$currencyName]['price'] = $currency['price'];
		}
		if ($currencyId = $value['currencyId'] ?? 0) {
			$currencyName = \App\Fields\Currency::getById($currencyId)['currency_name'];
			$result['currencyId'] = $currencyName;
		}
		return $result ? \App\Json::encode($result) : '';
	}

	/** {@inheritdoc} */
	public function getValueFromImport($value)
	{
		$result = [];
		$value = \App\Json::isEmpty($value) ? [] : \App\Json::decode($value);
		foreach ($value['currencies'] ?? [] as $key => $currency) {
			$currencyId = \App\Fields\Currency::getCurrencyIdByName($key);
			$result['currencies'][$currencyId]['price'] = $currency['price'];
		}
		if ($currencyName = $value['currencyId'] ?? 0) {
			$currencyId = \App\Fields\Currency::getCurrencyIdByName($currencyName);
			$result['currencyId'] = $currencyId;
		}
		return \App\Json::encode($result);
	}

	/** {@inheritdoc} */
	public function getTemplateName()
	{
		return 'Edit/Field/MultiCurrency.tpl';
	}

	/** {@inheritdoc} */
	public function isActiveSearchView()
	{
		return false;
	}

	/** {@inheritdoc} */
	public function isAjaxEditable()
	{
		return false;
	}

	/** {@inheritdoc} */
	public function isListviewSortable()
	{
		return false;
	}

	/** {@inheritdoc} */
	public function getAllowedColumnTypes()
	{
		return ['text'];
	}
}
