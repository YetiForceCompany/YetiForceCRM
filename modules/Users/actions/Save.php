<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Users_Save_Action extends Vtiger_Save_Action
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->get('record');
		$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
		$currentUserModel = Users_Record_Model::getCurrentUserModel();

		// Check for operation access.
		$allowed = Users_Privileges_Model::isPermitted($moduleName, 'Save', $record);

		if ($allowed) {
			// Deny access if not administrator or account-owner or self
			if (!$currentUserModel->isAdminUser()) {
				if (empty($record)) {
					$allowed = false;
				} else if ($currentUserModel->get('id') !== $recordModel->getId()) {
					$allowed = false;
				}
			}
		}

		if (!$allowed) {
			throw new \Exception\AppException('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Function to get the record model based on the request parameters
	 * @param Vtiger_Request $request
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	protected function getRecordModelFromRequest(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!empty($recordId)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			$modelData = $recordModel->getData();
			$recordModel->set('id', $recordId);
			$sharedType = $request->get('sharedtype');
			if (!empty($sharedType))
				$recordModel->set('calendarsharedtype', $request->get('sharedtype'));
		} else {
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			$modelData = $recordModel->getData();
		}
		unset($modelData['mode']);
		foreach ($modelData as $fieldName => $value) {
			if (!$request->has($fieldName)) {
				continue;
			}
			$fieldValue = $request->get($fieldName, null);
			if ($fieldName === 'is_admin') {
				if (!$currentUserModel->isAdminUser() && (!$fieldValue)) {
					$fieldValue = 'off';
				} else if ($currentUserModel->isAdminUser() && ($fieldValue || $fieldValue === 'on')) {
					$fieldValue = 'on';
					$recordModel->set('is_owner', 1);
				} else {
					$fieldValue = 'off';
					$recordModel->set('is_owner', 0);
				}
			}
			if ($fieldValue !== null) {
				if (!is_array($fieldValue)) {
					$fieldValue = trim($fieldValue);
				}
				$recordModel->set($fieldName, $fieldValue);
			}
		}
		$homePageComponents = $recordModel->getHomePageComponents();
		$selectedHomePageComponents = $request->get('homepage_components', array());
		foreach ($homePageComponents as $key => $value) {
			if (in_array($key, $selectedHomePageComponents)) {
				$request->setGlobal($key, $key);
			} else {
				$request->setGlobal($key, '');
			}
		}

		return $recordModel;
	}

	public function process(Vtiger_Request $request)
	{
		$result = Vtiger_Util_Helper::transformUploadedFiles($_FILES, true);
		$_FILES = $result['imagename'];

		$moduleModel = Vtiger_Module_Model::getInstance('Users');
		if (!$moduleModel->checkMailExist($request->get('email1'), $request->get('record'))) {
			$recordModel = $this->saveRecord($request);

			$settingsModuleModel = Settings_Users_Module_Model::getInstance();
			$settingsModuleModel->refreshSwitchUsers();

			$sharedIds = $request->get('sharedusers');
			$sharedType = $request->get('calendarsharedtype');
			$currentUserModel = Users_Record_Model::getCurrentUserModel();
			$calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
			$accessibleUsers = \App\Fields\Owner::getInstance('Calendar', $currentUserModel)->getAccessibleUsersForModule();

			if ($sharedType == 'private') {
				$calendarModuleModel->deleteSharedUsers($currentUserModel->id);
			} else if ($sharedType == 'public') {
				$allUsers = $currentUserModel->getAll(true);
				$accessibleUsers = array();
				foreach ($allUsers as $id => $userModel) {
					$accessibleUsers[$id] = $id;
				}
				$calendarModuleModel->deleteSharedUsers($currentUserModel->id);
				$calendarModuleModel->insertSharedUsers($currentUserModel->id, array_keys($accessibleUsers));
			} else {
				if (!empty($sharedIds)) {
					$calendarModuleModel->deleteSharedUsers($currentUserModel->id);
					$calendarModuleModel->insertSharedUsers($currentUserModel->id, $sharedIds);
				} else {
					$calendarModuleModel->deleteSharedUsers($currentUserModel->id);
				}
			}

			if ($request->get('relationOperation')) {
				$parentRecordModel = Vtiger_Record_Model::getInstanceById($request->get('sourceRecord'), $request->get('sourceModule'));
				$loadUrl = $parentRecordModel->getDetailViewUrl();
			} else if ($request->get('isPreference')) {
				$loadUrl = $recordModel->getPreferenceDetailViewUrl();
			} else {
				$loadUrl = $recordModel->getDetailViewUrl();
			}
		} else {
			App\Log::error('USER_MAIL_EXIST');
			header('Location: index.php?module=Users&parent=Settings&view=Edit');
			return false;
		}
		header("Location: $loadUrl");
	}
}
