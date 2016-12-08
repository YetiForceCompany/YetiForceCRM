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

class Settings_Roles_Save_Action extends Vtiger_Action_Controller
{

	/**
	 * Checking permission
	 * @param Vtiger_Request $request
	 * @throws \Exception\AppException
	 */
	public function checkPermission(Vtiger_Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if (!$currentUser->isAdminUser()) {
			throw new \Exception\AppException('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Process
	 * @param Vtiger_Request $request
	 */
	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$recordId = $request->get('record');
		$roleName = $request->get('rolename');
		$allowassignedrecordsto = $request->get('allowassignedrecordsto');

		$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
		if (!empty($recordId)) {
			$recordModel = Settings_Roles_Record_Model::getInstanceById($recordId);
		} else {
			$recordModel = new Settings_Roles_Record_Model();
		}

		$roleProfiles = $request->get('profiles');
		$parentRoleId = $request->get('parent_roleid');
		if ($recordModel && !empty($parentRoleId)) {
			$parentRole = Settings_Roles_Record_Model::getInstanceById($parentRoleId);
			$recordModel->set('change_owner', $request->get('change_owner'))
				->set('searchunpriv', $request->get('searchunpriv'))
				->set('listrelatedrecord', $request->get('listRelatedRecord'))
				->set('previewrelatedrecord', $request->get('previewRelatedRecord'))
				->set('editrelatedrecord', $request->get('editRelatedRecord'))
				->set('permissionsrelatedfield', $request->get('permissionsRelatedField'))
				->set('globalsearchadv', $request->get('globalSearchAdvanced'))
				->set('assignedmultiowner', $request->get('assignedmultiowner'))
				->set('clendarallorecords', $request->get('clendarallorecords'))
				->set('auto_assign', $request->get('auto_assign'));
			if (!empty($allowassignedrecordsto))
				$recordModel->set('allowassignedrecordsto', $allowassignedrecordsto); // set the value of assigned records to
			if ($parentRole && !empty($roleName) && !empty($roleProfiles)) {
				$recordModel->set('rolename', $roleName);
				$recordModel->set('profileIds', $roleProfiles);
				$parentRole->addChildRole($recordModel);
			}

			//After role updation recreating user privilege files
			if ($roleProfiles) {
				foreach ($roleProfiles as $profileId) {
					$profileRecordModel = Settings_Profiles_Record_Model::getInstanceById($profileId);
					$profileRecordModel->recalculate(array($recordId));
				}
			}
		}

		$redirectUrl = $moduleModel->getDefaultUrl();
		header("Location: $redirectUrl");
	}

	public function validateRequest(Vtiger_Request $request)
	{
		$request->validateWriteAccess();
	}
}
