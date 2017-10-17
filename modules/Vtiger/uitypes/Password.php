<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_Password_UIType extends Vtiger_Base_UIType
{

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, $isUserFormat = false)
	{
		if ($this->validate || empty($value)) {
			return;
		}
		if ($isUserFormat) {
			$res = Settings_Password_Record_Model::checkPassword($value);
			if ($res !== false) {
				throw new \App\Exceptions\SaveRecord('ERR_INCORRECT_VALUE_WHILE_SAVING_RECORD', 406);
			}
		}
		$this->validate = true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTemplateName()
	{
		return 'uitypes/Password.tpl';
	}
}
