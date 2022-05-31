<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Vtiger_FileLocationType_UIType extends Vtiger_Picklist_UIType
{
	/** {@inheritdoc} */
	public function validate($value, $isUserFormat = false)
	{
		if (empty($value) || isset($this->validate[$value])) {
			return;
		}
		parent::validate($value, $isUserFormat);
		$allowedPicklist = $this->getPicklistValues();
		if (!isset($allowedPicklist[$value])) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . $this->getFieldModel()->getName() . '||' . $this->getFieldModel()->getModuleName() . '||' . $value, 406);
		}
		$this->validate[$value] = true;
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		$values = $this->getPicklistValues();
		return \App\Purifier::encodeHtml($values[$value] ?? $value);
	}

	/**
	 * Function to get all the available picklist values for the current field.
	 *
	 * @return array List of picklist values if the field
	 */
	public function getPicklistValues()
	{
		$moduleName = $this->getFieldModel()->getModuleName();
		return [
			'I' => \App\Language::translate('LBL_INTERNAL', $moduleName, false, false),
			'E' => \App\Language::translate('LBL_EXTERNAL', $moduleName, false, false),
		];
	}

	/**
	 * Function defines empty picklist element availability.
	 *
	 * @return bool
	 */
	public function isEmptyPicklistOptionAllowed()
	{
		return false;
	}

	/** {@inheritdoc} */
	public function getQueryOperators()
	{
		return ['e', 'n', 'y', 'ny'];
	}
}
