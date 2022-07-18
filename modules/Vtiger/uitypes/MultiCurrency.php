<?php
/**
 * UIType multi currency.
 *
 * @package   UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		if (\is_string($value)) {
			$data = \App\Json::isEmpty($value) ? [] : \App\Json::decode($value);
		} else {
			$data = $value;
		}
		foreach ($data['currencies'] ?? [] as $key => $currency) {
			$data['currencies'][$key]['price'] = App\Fields\Double::formatToDb($currency['price']);
		}
		return \App\Json::encode($data);
	}

	/**
	 * Get validator.
	 *
	 * @return array
	 */
	public function getValidator(): array
	{
		return [['name' => 'Currency']];
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
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$currencies = \App\Fields\Currency::getAll(true);
		foreach ($value['currencies'] ?? [] as $id => $currency) {
			if (!isset($currencies[$id])) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $id, 406);
			}
			$price = $currency['price'];
			if ($isUserFormat) {
				$price = App\Fields\Double::formatToDb($price);
			}
			if (!is_numeric($price)) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $price, 406);
			}
			if ($maximumLength = $this->getFieldModel()->get('maximumlength')) {
				[$minimumLength, $maximumLength] = false !== strpos($maximumLength, ',') ? explode(',', $maximumLength) : [-$maximumLength, $maximumLength];
				if ((float) $minimumLength > $price || (float) $maximumLength < $price) {
					throw new \App\Exceptions\Security('ERR_VALUE_IS_TOO_LONG||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . "||{$maximumLength} < {$price} < {$minimumLength}", 406);
				}
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
		if (\is_string($value)) {
			$data = \App\Json::isEmpty($value) ? [] : \App\Json::decode($value);
		} else {
			$data = $value ?: [];
		}
		return $data['currencyId'] ?? null;
	}

	/** {@inheritdoc} */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		if (\is_string($value)) {
			$data = \App\Json::isEmpty($value) ? [] : \App\Json::decode($value);
		} else {
			$data = $value;
		}
		if ($data) {
			$currencyId = $data['currencyId'];
			$data = App\Fields\Double::formatToDisplay($data['currencies'][$currencyId]['price'], false);
		} else {
			$data = '';
		}
		return \App\Purifier::encodeHtml($data);
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
		if (\is_string($value)) {
			$data = \App\Json::isEmpty($value) ? [] : \App\Json::decode($value);
		} else {
			$data = $value ?: [];
		}
		if ($data) {
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
		$params = ['uitype' => 71, 'displaytype' => 1, 'typeofdata' => 'N~O', 'isEditableReadOnly' => false, 'maximumlength' => $this->getFieldModel()->get('maximumlength')];
		$fieldModel = new \Vtiger_Field_Model();
		$fieldModel->setModule($this->getFieldModel()->getModule());
		$fieldInfo = $fieldModel->setData($params)->getFieldInfo();
		foreach (\App\Fields\Currency::getAll(true) as $id => $currency) {
			$name = "currencies[$id][value]";
			$fieldInfo['name'] = $name;
			$priceDetails[$id] = [
				'name' => $name,
				'conversionRate' => $currency['conversion_rate'],
				'symbol' => $currency['currency_symbol'],
				'currencyName' => $currency['currency_name'],
				'fieldInfo' => $fieldInfo,
			];
		}
		return $priceDetails;
	}

	/**
	 * Get value for the currency.
	 *
	 * @param string|array $value
	 * @param int          $currencyId
	 *
	 * @return float
	 */
	public function getValueForCurrency($value, int $currencyId): float
	{
		$result = 0;
		if (\is_string($value)) {
			$data = \App\Json::isEmpty($value) ? [] : \App\Json::decode($value);
		} else {
			$data = $value;
		}
		if ($data) {
			$rate = 1;
			if (!isset($data['currencies'][$currencyId])) {
				$currencyInfo = \App\Fields\Currency::getById($currencyId);
				$currencyId = $data['currencyId'];
				$baseRate = 1 / \App\Fields\Currency::getById($currencyId)['conversion_rate'];
				$rate = $baseRate * $currencyInfo['conversion_rate'];
			}
			$result = $data['currencies'][$currencyId]['price'] * $rate;
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
		if (\is_string($value)) {
			$data = \App\Json::isEmpty($value) ? [] : \App\Json::decode($value);
		} else {
			$data = $value;
		}
		foreach ($data['currencies'] ?? [] as $key => $currency) {
			$currencyName = \App\Fields\Currency::getById($key)['currency_name'];
			$result['currencies'][$currencyName]['price'] = $currency['price'];
		}
		if ($currencyId = $data['currencyId'] ?? 0) {
			$currencyName = \App\Fields\Currency::getById($currencyId)['currency_name'];
			$result['currencyId'] = $currencyName;
		}
		return $result ? \App\Json::encode($result) : '';
	}

	/** {@inheritdoc} */
	public function getValueFromImport($value, $defaultValue = null)
	{
		$result = [];
		if (\is_string($value)) {
			$data = \App\Json::isEmpty($value) ? [] : \App\Json::decode($value);
		} else {
			$data = $value;
		}
		foreach ($data['currencies'] ?? [] as $key => $currency) {
			$currencyId = \App\Fields\Currency::getCurrencyIdByName($key);
			$result['currencies'][$currencyId]['price'] = $currency['price'];
		}
		if ($currencyName = $data['currencyId'] ?? 0) {
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
