<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_Email_UIType extends Vtiger_Base_UIType
{

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return string - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/Email.tpl';
	}

	public function getDisplayValue($value, $recordId = false, $recordInstance = false, $rawText = false)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$internalMailer = $currentUser->get('internal_mailer');
		if ($value && !$rawText) {
			$moduleName = $this->get('field')->get('block')->module->name;
			$fieldName = $this->get('field')->get('name');
			$rawValue = $value;
			$value = vtlib\Functions::textLength($value);
			if ($internalMailer == 1 && Users_Privileges_Model::isPermitted('OSSMail')) {
				$url = OSSMail_Module_Model::getComposeUrl($moduleName, $recordId, 'Detail', 'new');
				$mailConfig = OSSMail_Module_Model::getComposeParameters();
				$value = "<a class=\"cursorPointer sendMailBtn\" data-url=\"$url\" data-module=\"$moduleName\" data-record=\"$recordId\" data-to=\"$rawValue\" data-popup=" . $mailConfig['popup'] . " title=" . vtranslate('LBL_SEND_EMAIL') . ">$value</a>";
			} else {
				if ($moduleName == 'Users' && $fieldName == 'user_name') {
					$value = "<a class='cursorPointer' href='mailto:" . $rawValue . "'>" . $value . "</a>";
				} else {
					$value = "<a class='emailField cursorPointer'  href='mailto:" . $rawValue . "'>" . $value . "</a>";
				}
			}
		}
		return $value;
	}
}
