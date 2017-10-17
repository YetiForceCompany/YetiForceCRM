<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Vtiger_Theme_UIType extends Vtiger_Base_UIType
{

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if ($this->validate || empty($value)) {
			return;
		}
		$allSkins = Vtiger_Theme::getAllSkins();
		if (!isset($allSkins[$value])) {
			throw new \App\Exceptions\SaveRecord('ERR_INCORRECT_VALUE_WHILE_SAVING_RECORD', 406);
		}
		$this->validate = true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayValue($value, $record = false, $recordInstance = false, $rawText = false)
	{
		$allSkins = Vtiger_Theme::getAllSkins();
		$skinColor = $allSkins[$value];
		$value = ucfirst($value);
		return "<div style='width:99%; background-color:$skinColor;' title='$value'>&nbsp;</div>";
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTemplateName()
	{
		return 'uitypes/Theme.tpl';
	}
}
