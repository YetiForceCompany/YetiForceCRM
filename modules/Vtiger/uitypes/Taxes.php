<?php

/**
 * UIType Taxes Field Class
 * @package YetiForce.Fieldsss
 * @license licenses/License.html
 * @author YetiForce.com
 */
class Vtiger_Taxes_UIType extends Vtiger_Base_UIType
{

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return string - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/Taxes.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param string $value
	 * @param int $record
	 * @param Vtiger_Record_Model $recordInstance
	 * @param bool $rawText
	 * @return string
	 */
	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		$display = [];
		if (!empty($value)) {
			$taxes = $this->getPicklistValues();
			$values = explode(',', $value);
			$display = array_intersect_key($taxes, array_flip($values));
		}
		return implode(', ', $display);
	}

	/**
	 * Function to get the display value in edit view
	 * @param string $value
	 * @param int $record - Record ID
	 * @return array
	 */
	public function getEditViewDisplayValue($value, $record = false)
	{
		$display = [];
		if (!empty($value)) {
			$values = explode(',', $value);
			$taxes = $this->getPicklistValues();
			foreach ($values as $tax) {
				if (isset($taxes[$tax])) {
					$display[] = $tax;
				}
			}
		}
		return $display;
	}

	public static function getValues($value)
	{
		$values = explode(',', $value);
		$taxs = self::getTaxes();
		$display = [];

		foreach ($values as $tax) {
			if (isset($taxs[$tax])) {
				$display[$tax] = $taxs[$tax];
			}
		}

		return $display;
	}

	/**
	 * Function to get taxes
	 * @return array
	 */
	public static function getTaxes()
	{
		return Vtiger_Inventory_Model::getGlobalTaxes();
	}

	/**
	 * Function to get all the available picklist values for the current field
	 * @return array List of picklist values if the field
	 */
	public function getPicklistValues()
	{
		$taxes = self::getTaxes();
		foreach ($taxes as $key => $tax) {
			$taxes[$key] = $tax['name'] . ' - ' . $tax['value'] . '%';
		}
		return $taxes;
	}

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return string - Template Name
	 */
	public function getListSearchTemplateName()
	{
		return 'uitypes/MultiSelectFieldSearchView.tpl';
	}

	/**
	 * Function to get the DB Insert Value, for the current field type with given User Value
	 * @param mixed $value
	 * @param \Vtiger_Record_Model $recordModel
	 * @return mixed
	 */
	public function getDBValue($value, $recordModel = false)
	{
		if (is_array($value)) {
			$value = implode(',', $value);
		}
		return $value;
	}
}
