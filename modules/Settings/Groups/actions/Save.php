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
	public function process(\App\Request $request)
	{
		$prevValues = [];
		if (!$request->isEmpty('record')) {
			$recordModel = Settings_Groups_Record_Model::getInstance($request->getInteger('record'));
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
			$recordModel->set('groupname', $request->getByType('groupname', 'Text'));
			$recordModel->set('description', $request->getByType('description', 'Text'));
			$recordModel->set('group_members', $request->getArray('members', 'Text'));
			$recordModel->set('modules', $request->getArray('modules', 'Integer'));
			$recordModel->save();
			$postValues = $recordModel->getDisplayData();
			Settings_Vtiger_Tracker_Model::addDetail($prevValues, $postValues);
		}

		$redirectUrl = $recordModel->getDetailViewUrl();
		header("location: $redirectUrl");
	}

	public function validateRequest(\App\Request $request)
	{
		$request->validateWriteAccess();
	}
}
