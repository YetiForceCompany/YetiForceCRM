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

class Calendar_Reminders_View extends Vtiger_IndexAjax_View
{

	public function process(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		if ('true' == $request->get('type_remainder')) {
			$recordModels = Calendar_Module_Model::getCalendarReminder(true);
		} else {
			$recordModels = Calendar_Module_Model::getCalendarReminder();
		}
		$colorList = [];
		foreach ($recordModels as $record) {
			$record->updateReminderStatus(2);
			$colorList[$record->getId()] = Settings_DataAccess_Module_Model::executeColorListHandlers($moduleName, $record->getId(), $record);
		}
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleName);
		$permissionToSendEmail = $permission && AppConfig::main('isActiveSendingMails') && Users_Privileges_Model::isPermitted('OSSMail');
		$viewer->assign('COLOR_LIST', array_filter($colorList));
		$viewer->assign('PERMISSION_TO_SENDE_MAIL', $permissionToSendEmail);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('RECORDS', $recordModels);
		$viewer->view('Reminders.tpl', $moduleName);
	}
}
