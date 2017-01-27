<?php

/**
 * UIType Company Field Class
 * @package YetiForce.UIType
 * @license licenses/License.html
 * @author Adrian Koń <a.kon@yetiforce.com>
 */
class Vtiger_CompanySelect_UIType extends Vtiger_Base_UIType
{

	public function getTemplateName()
	{
		return 'uitypes/CompanySelect.tpl';
	}

	public function getDisplayValue($values, $record = false, $recordInstance = false, $rawText = false)
	{
		$namesOfCompany = '';
		if (!empty($values)) {
			$companiesList = $this->getPicklistValues();
			$namesOfCompany = $companiesList[$values[0]]['name'];
		}
		return $namesOfCompany;
	}

	public function getPicklistValues()
	{
		return Settings_Companies_Module_Model::getAllCompanies();
	}

	public function getListSearchTemplateName()
	{
		return 'uitypes/CompanySelectFieldSearchView.tpl';
	}
}
