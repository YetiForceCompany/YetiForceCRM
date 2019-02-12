<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Calendar_Datetime_UIType extends Vtiger_Datetime_UIType
{
	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, $record = false, $recordModel = false, $rawText = false, $length = false)
	{
		//Since date_start and due_date fields of calendar can have time appended or removed
		if ($this->hasTimeComponent($value)) {
			return App\Fields\DateTime::formatToDisplay($value);
		} else {
			return App\Fields\Date::formatToDisplay($value);
		}
	}

	public function hasTimeComponent($value)
	{
		$component = explode(' ', $value);
		if (!empty($component[1])) {
			return true;
		}
		return false;
	}
}
