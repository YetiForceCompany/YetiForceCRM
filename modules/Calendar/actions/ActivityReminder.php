<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_ActivityReminder_Action extends Vtiger_Action_Controller{

	function __construct() {
		$this->exposeMethod('postpone');
	}

	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());

		if(!$permission) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}

	}

	function postpone(Vtiger_Request $request) {
		$recordId = $request->get('record');
		$time = $request->get('time');
		$module = $request->getModule();
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $module);
		$recordModel->updateReminderPostpone($time);
	}
}
