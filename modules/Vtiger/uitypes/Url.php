<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_Url_UIType extends Vtiger_Base_UIType
{

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return string - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/Url.tpl';
	}

	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		$matchPattern = "^[\w]+:\/\/^";
		preg_match($matchPattern, $value, $matches);
		if (!empty($matches[0])) {
			$value = '<a class="urlField cursorPointer" title="' . $value . '" href="' . $value . '" target="_blank">' . \vtlib\Functions::textLength($value) . '</a>';
		} else {
			$value = '<a class="urlField cursorPointer" title="' . $value . '" href="http://' . $value . '" target="_blank">' . \vtlib\Functions::textLength($value) . '</a>';
		}
		return $value;
	}

	public function getListViewDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		$matchPattern = "^[\w]+:\/\/^";
		preg_match($matchPattern, $value, $matches);
		if (!empty($matches[0])) {
			$value = '<a class="urlField cursorPointer" title="' . $value . '" href="' . $value . '" target="_blank">' . \vtlib\Functions::textLength($value, $this->get('field')->get('maxlengthtext')) . '</a>';
		} else {
			$value = '<a class="urlField cursorPointer" title="' . $value . '" href="http://' . $value . '" target="_blank">' . \vtlib\Functions::textLength($value, $this->get('field')->get('maxlengthtext')) . '</a>';
		}
		return $value;
	}
}
