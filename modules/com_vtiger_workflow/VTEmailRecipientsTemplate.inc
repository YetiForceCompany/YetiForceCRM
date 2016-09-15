<?php
/* +*******************************************************************************
 *  The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ******************************************************************************* */

require_once 'modules/com_vtiger_workflow/VTSimpleTemplate.inc';

/**
 * Description of VTEmailRecipientsTemplate
 *
 * @author MAK
 */
class VTEmailRecipientsTemplate extends VTSimpleTemplate
{

	private static $permissionToSend = [
		'Accounts' => 'emailoptout',
		'Contacts' => 'emailoptout',
		'Users' => 'emailoptout',
		'Leads' => 'noapprovalemails'
	];

	public function __construct($templateString)
	{
		parent::__construct($templateString);
	}

	protected function useValue($data, $fieldname, $moduleName)
	{
		if (array_key_exists($moduleName, self::$permissionToSend)) {
			$checkFieldName = self::$permissionToSend[$moduleName];
			$tabId = \includes\Modules::getModuleId($moduleName);
			$fieldInfo = VTCacheUtils::lookupFieldInfo($tabId, $checkFieldName);
			$isActive = is_array($fieldInfo) ? !($fieldInfo['presence'] == 1) : true;
			return ($isActive && array_key_exists($checkFieldName, $data)) ? (bool) $data[$checkFieldName] : true;
		}
		return true;
	}
}
