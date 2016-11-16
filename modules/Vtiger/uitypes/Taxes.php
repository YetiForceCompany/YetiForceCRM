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
		return implode(', ', $this->getEditViewDisplayValue($value, $record));
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
		$values = explode(',', $value);
		$taxes = $this->getPicklistValues();
		foreach ($values as $tax) {
			if (isset($taxes[$tax])) {
				$display[$tax] = $taxes[$tax];
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
		if (\App\Cache::has('Inventory', 'Taxes')) {
			return \App\Cache::get('Inventory', 'Taxes');
		}
		$taxes = (new App\Db\Query())->from('a_#__taxes_global')->where(['status' => 0])
				->createCommand(App\Db::getInstance('admin'))->queryAllByGroup(1);
		\App\Cache::save('Inventory', 'Taxes', $taxes, \App\Cache::LONG);
		return $taxes;
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
		return 'uitypes/TaxesFieldSearchView.tpl';
	}
}
