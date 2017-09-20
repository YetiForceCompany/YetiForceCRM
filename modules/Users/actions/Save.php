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

class Users_Save_Action extends Vtiger_Save_Action
{

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		if (!$request->isEmpty('record', true)) {
			$record = $request->getInteger('record');
			$this->record = $recordModel = $this->record ? $this->record : Vtiger_Record_Model::getInstanceById($record, $moduleName);
			$currentUserModel = Users_Record_Model::getCurrentUserModel();

			$allowed = \App\Privilege::isPermitted($moduleName, 'Save', $record);
			if ($allowed && !$currentUserModel->isAdminUser() && AppConfig::security('SHOW_MY_PREFERENCES') && ((int) $currentUserModel->get('id') !== $recordModel->getId())) {
				$allowed = false;
			}
			if (!$allowed) {
				throw new \App\Exceptions\NoPermittedToRecord('LBL_PERMISSION_DENIED');
			}
		} else {
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			if (!$recordModel->isCreateable()) {
				throw new \App\Exceptions\NoPermittedToRecord('LBL_PERMISSION_DENIED');
			}
		}
	}

	/**
	 * Function to get the record model based on the request parameters
	 * @param \App\Request $request
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	protected function getRecordModelFromRequest(\App\Request $request)
	{
		$recordModel = parent::getRecordModelFromRequest($request);
		if ($recordModel->isNew()) {
			$recordModel->set('user_name', $request->get('user_name', null));
			$recordModel->set('user_password', $request->getRaw('user_password', null));
			$recordModel->set('confirm_password', $request->getRaw('confirm_password', null));
		}
		return $recordModel;
	}

	/**
	 * Process
	 * @param \App\Request $request
	 * @return boolean
	 */
	public function process(\App\Request $request)
	{
		$result = Vtiger_Util_Helper::transformUploadedFiles($_FILES, true);
		$_FILES = $result['imagename'];
		if (!empty($_FILES[0]['name'])) {
			$request->set('imagename', $_FILES[0]['name']);
		}
		$moduleName = $request->getModule();
		$message = '';
		if (Users_Module_Model::checkMailExist($request->get('email1'), (int) $request->get('record'))) {
			$message = \App\Language::translate('LBL_USER_MAIL_EXIST', $moduleName);
		}
		if ($request->isEmpty('record', true) || $this->record->get('user_name') !== $request->get('user_name')) {
			if ($checkUserName = Users_Module_Model::checkUserName($request->get('user_name'), (int) $request->get('record'))) {
				$message = $checkUserName;
			}
		}
		if ($request->isEmpty('record', true)) {
			if (!$request->isEmpty('user_password', true)) {
				$checkPassword = Settings_Password_Record_Model::checkPassword($request->get('user_password'));
				if ($checkPassword) {
					$message = $checkPassword;
				}
			}
		}
		if ($message) {
			App\Log::error($message);
			header('Location: index.php?module=Users&parent=Settings&view=Edit');
			return false;
		}
		$recordModel = $this->saveRecord($request);
		$settingsModuleModel = Settings_Users_Module_Model::getInstance();
		$settingsModuleModel->refreshSwitchUsers();

		$sharedIds = $request->get('sharedusers');
		$sharedType = $request->get('calendarsharedtype');
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
		$accessibleUsers = \App\Fields\Owner::getInstance('Calendar', $currentUserModel)->getAccessibleUsersForModule();

		if ($sharedType == 'private') {
			$calendarModuleModel->deleteSharedUsers($currentUserModel->getId());
		} else if ($sharedType == 'public') {
			$allUsers = $currentUserModel->getAll(true);
			$accessibleUsers = [];
			foreach ($allUsers as $id => $userModel) {
				$accessibleUsers[$id] = $id;
			}
			$calendarModuleModel->deleteSharedUsers($currentUserModel->getId());
			$calendarModuleModel->insertSharedUsers($currentUserModel->getId(), array_keys($accessibleUsers));
		} else {
			if (!empty($sharedIds)) {
				$calendarModuleModel->deleteSharedUsers($currentUserModel->getId());
				$calendarModuleModel->insertSharedUsers($currentUserModel->getId(), $sharedIds);
			} else {
				$calendarModuleModel->deleteSharedUsers($currentUserModel->getId());
			}
		}
		if ($request->get('relationOperation')) {
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($request->get('sourceRecord'), $request->getByType('sourceModule', 1));
			$loadUrl = $parentRecordModel->getDetailViewUrl();
		} else if ($request->get('isPreference')) {
			$loadUrl = $recordModel->getPreferenceDetailViewUrl();
		} else {
			$loadUrl = $recordModel->getDetailViewUrl();
		}
		header("Location: $loadUrl");
	}
}
