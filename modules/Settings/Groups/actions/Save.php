<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_Groups_Save_Action extends Settings_Vtiger_Save_Action
{

	public function process(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$recordId = $request->get('record');

		$moduleModel = Settings_Vtiger_Module_Model::getInstance($qualifiedModuleName);
		$prevValues = [];
		if (!empty($recordId)) {
			$recordModel = Settings_Groups_Record_Model::getInstance($recordId);
			$members = $recordModel->getMembers();
			$membersToDipslay = [];
			foreach ($members as $typeMembers) {
				foreach ($typeMembers as $member) {
					$membersToDipslay[] = $member->get('id');
				}
			}
			$recordModel->set('group_members', $membersToDipslay);
			$recordModel->set('modules', $recordModel->getModules());
			$prevValues = $recordModel->getDisplayData();
		} else {
			$recordModel = new Settings_Groups_Record_Model();
		}
		if ($recordModel) {
			$recordModel->set('groupname', decode_html($request->get('groupname')));
			$recordModel->set('description', $request->get('description'));
			$recordModel->set('group_members', $request->get('members'));
			$recordModel->set('modules', $request->get('modules'));
			$recordModel->save();
			$postValues = $recordModel->getDisplayData();
			Settings_Vtiger_Tracker_Model::addDetail($prevValues, $postValues);
		}

		$redirectUrl = $recordModel->getDetailViewUrl();
		header("Location: $redirectUrl");
	}

	public function validateRequest(Vtiger_Request $request)
	{
		$request->validateWriteAccess();
	}
}
