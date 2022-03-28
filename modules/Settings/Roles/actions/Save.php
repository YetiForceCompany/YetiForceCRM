<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Settings_Roles_Save_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
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
		$parentRoleId = $request->getByType('parent_roleid', 2);
		if ($recordModel && !empty($parentRoleId)) {
			$parentRole = Settings_Roles_Record_Model::getInstanceById($parentRoleId);
			$recordModel->set('changeowner', $request->get('changeowner'))
				->set('searchunpriv', $request->get('searchunpriv'))
				->set('listrelatedrecord', $request->getInteger('listRelatedRecord'))
				->set('previewrelatedrecord', $request->getInteger('previewRelatedRecord'))
				->set('editrelatedrecord', $request->get('editRelatedRecord'))
				->set('permissionsrelatedfield', $request->get('permissionsRelatedField'))
				->set('globalsearchadv', $request->get('globalSearchAdvanced'))
				->set('assignedmultiowner', $request->getInteger('assignedmultiowner'))
				->set('clendarallorecords', $request->getInteger('clendarallorecords'))
				->set('company', $request->getInteger('company'))
				->set('auto_assign', $request->get('auto_assign'));
			if (!empty($allowassignedrecordsto)) {
				$recordModel->set('allowassignedrecordsto', $allowassignedrecordsto);
			} // set the value of assigned records to
			if ($parentRole && !empty($roleName) && !empty($roleProfiles)) {
				$recordModel->set('rolename', $roleName);
				$recordModel->set('profileIds', $roleProfiles);
				$parentRole->addChildRole($recordModel);
			}
			//After role updation recreating user privilege files
			if ($roleProfiles) {
				foreach ($roleProfiles as $profileId) {
					$profileRecordModel = Settings_Profiles_Record_Model::getInstanceById($profileId);
					$profileRecordModel->recalculate();
				}
			}
		}
		header("location: {$moduleModel->getDefaultUrl()}");
	}
}
