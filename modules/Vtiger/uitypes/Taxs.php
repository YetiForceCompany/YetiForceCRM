<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_Taxs_UIType extends Vtiger_Base_UIType
{

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/Taxs.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($value)
	{
		$values = explode(',', $value);
		$taxs = $this->getTaxs();
		$display = [];

		foreach ($values as $tax) {
			if (isset($taxs[$tax])) {
				$display[] = $taxs[$tax]['value'] . '% - ' . $taxs[$tax]['name'];
			}
		}

		return implode(',', $display);
	}

	public function getListSearchTemplateName()
	{
		return 'uitypes/TaxsFieldSearchView.tpl';
	}

	/**
	 * Function to get all the available picklist values for the current field
	 * @return <Array> List of picklist values if the field is of type picklist or multipicklist, null otherwise.
	 */
	public function getTaxs()
	{
		$taxs = Vtiger_Cache::get('taxs', 'global');
		if (!$taxs) {
			$db = PearDatabase::getInstance();
			$taxs = [];
			$result = $db->pquery('SELECT * FROM a_yf_taxs_global WHERE status = ?', [1]);
			while ($row = $db->fetch_array($result)) {
				$taxs[$row['id']] = $row;
			}
			Vtiger_Cache::set('taxs', 'global', $taxs);
		}

		return $taxs;
	}
}
