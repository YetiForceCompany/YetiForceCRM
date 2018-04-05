<?php

/**
 * UIType Taxes Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author YetiForce.com
 */
class Vtiger_Taxes_UIType extends Vtiger_Base_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getDBValue($value, $recordModel = false)
	{
		if (is_array($value)) {
			$value = implode(',', $value);
		}

		return \App\Purifier::decodeHtml($value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if ($this->validate || empty($value)) {
			return;
		}
		if (!is_array($value)) {
			$value = [$value];
		}
		foreach ($value as $id) {
			if (!is_numeric($id)) {
				throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getFieldName() . '||' . $id, 406);
			}
		}
		$this->validate = true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$display = [];
		if (!empty($value)) {
			$taxes = $this->getPicklistValues();
			$values = explode(',', $value);
			$display = array_intersect_key($taxes, array_flip($values));
		}

		return \App\Purifier::encodeHtml(implode(', ', $display));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEditViewDisplayValue($value, $recordModel = false)
	{
		$display = [];
		if (!empty($value)) {
			$values = explode(',', $value);
			$taxes = $this->getPicklistValues();
			foreach ($values as $tax) {
				if (isset($taxes[$tax])) {
					$display[] = \App\Purifier::encodeHtml($tax);
				}
			}
		}

		return $display;
	}

	public static function getValues($value)
	{
		$values = explode(',', $value);
		$taxs = Vtiger_Inventory_Model::getGlobalTaxes();
		$display = [];

		foreach ($values as $tax) {
			if (isset($taxs[$tax])) {
				$display[$tax] = $taxs[$tax];
			}
		}

		return $display;
	}

	/**
	 * Function to get all the available picklist values for the current field.
	 *
	 * @return array List of picklist values if the field
	 */
	public function getPicklistValues()
	{
		$taxes = Vtiger_Inventory_Model::getGlobalTaxes();
		foreach ($taxes as $key => $tax) {
			$taxes[$key] = $tax['name'] . ' - ' . $tax['value'] . '%';
		}

		return $taxes;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getListSearchTemplateName()
	{
		return 'List/Field/MultiPicklist.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTemplateName()
	{
		return 'Edit/Field/Taxes.tpl';
	}
}
