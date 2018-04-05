<?php

/**
 * UIType country field class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Country_UIType extends Vtiger_Base_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getDBValue($value, $recordModel = false)
	{
		return \App\Purifier::decodeHtml($value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$value = \App\Language::translateSingleMod($value, 'Other.Country');
		if (is_int($length)) {
			$value = \App\TextParser::textTruncate($value, $length);
		}

		return \App\Purifier::encodeHtml($value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTemplateName()
	{
		return 'Edit/Field/Country.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getListSearchTemplateName()
	{
		return 'List/Field/Country.tpl';
	}

	/**
	 * Function to get all the available picklist values for the current field.
	 *
	 * @return array List of picklist values if the field
	 */
	public function getPicklistValues()
	{
		return \App\Fields\Country::getAll('uitype');
	}
}
