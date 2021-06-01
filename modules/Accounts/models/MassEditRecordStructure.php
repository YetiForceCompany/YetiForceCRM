<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/**
 * Mass Edit Record Structure Model.
 */
class Accounts_MassEditRecordStructure_Model extends Vtiger_MassEditRecordStructure_Model
{
	/**
	 * Function that return Field Restricted are not.
	 *
	 * 	@params Field Model
	 *  @returns boolean true or false
	 *
	 * @param mixed $fieldModel
	 */
	public function isFieldRestricted($fieldModel)
	{
		$restricted = parent::isFieldRestricted($fieldModel);
		if ($restricted && 'accountname' == $fieldModel->getName()) {
			return false;
		}
		return $restricted;
	}
}
