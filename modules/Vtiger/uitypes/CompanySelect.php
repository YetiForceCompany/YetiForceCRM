<?php

/**
 * UIType Company Field Class
 * @package YetiForce.UIType
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Vtiger_CompanySelect_UIType extends Vtiger_Base_UIType
{

	/**
	 * Verification of data
	 * @param int $value
	 * @param bool $isUserFormat
	 * @return null
	 * @throws \App\Exceptions\Security
	 */
	public function validate($value, $isUserFormat = false)
	{
		if ($this->validate || empty($value)) {
			return;
		}
		if (!is_numeric($value)) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->get('field')->getFieldName() . '||' . $value, 406);
		}
		$this->validate = true;
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param string $tree
	 * @param int $record
	 * @param Vtiger_Record_Model $recordInstance
	 * @param boolean $rawText
	 * @return string
	 */
	public function getDisplayValue($values, $record = false, $recordInstance = false, $rawText = false)
	{
		$namesOfCompany = '';
		if (!empty($values)) {
			$companiesList = $this->getPicklistValues();
			$namesOfCompany = $companiesList[$values[0]]['name'];
		}
		return \App\Purifier::encodeHtml($namesOfCompany);
	}

	/**
	 * Function to get all the available picklist values for the company
	 * @return array List of picklist values if the field
	 */
	public function getPicklistValues()
	{
		return Settings_Companies_Module_Model::getAllCompanies();
	}

	/**
	 * Function to get the Template name for the current UI Type Object
	 * @return string - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/CompanySelect.tpl';
	}

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return string - Template Name
	 */
	public function getListSearchTemplateName()
	{
		return 'uitypes/CompanySelectFieldSearchView.tpl';
	}
}
