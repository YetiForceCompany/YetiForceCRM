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
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		if (!$request->isEmpty('record', true)) {
			$record = $request->getInteger('record');
			$this->record = Vtiger_Record_Model::getInstanceById($record, $moduleName);
			$currentUserModel = Users_Record_Model::getCurrentUserModel();

			$allowed = \App\Privilege::isPermitted($moduleName, 'Save', $record);
			if ($allowed && !$currentUserModel->isAdminUser() && AppConfig::security('SHOW_MY_PREFERENCES') && ((int) $currentUserModel->get('id') !== $this->record->getId())) {
				$allowed = false;
			}
			if (!$allowed) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		} else {
			$this->record = Vtiger_Record_Model::getCleanInstance($moduleName);
			if (!$this->record->isCreateable()) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getRecordModelFromRequest(\App\Request $request)
	{
		$recordModel = parent::getRecordModelFromRequest($request);
		if ($recordModel->isNew()) {
			$recordModel->set('user_name', $request->get('user_name', null));
			$recordModel->set('user_password', $request->getRaw('user_password', null));
			$recordModel->set('confirm_password', '');
		}
		return $recordModel;
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		if ($_FILES) {
			$result = \App\Fields\File::transform($_FILES, true);
			$_FILES = $result['imagename'];
			if (!empty($_FILES[0]['name'])) {
				$request->set('imagename', $_FILES[0]['name']);
			}
		}
		$moduleName = $request->getModule();
		$message = '';
		if (Users_Module_Model::checkMailExist($request->get('email1'), (int) $request->get('record'))) {
			$message = \App\Language::translate('LBL_USER_MAIL_EXIST', $moduleName);
		}
		if (($request->isEmpty('record', true) || $this->record->get('user_name') !== $request->get('user_name')) && $checkUserName = Users_Module_Model::checkUserName($request->get('user_name'), $request->getInteger('record'))) {
			$message = $checkUserName;
		}
		if ($request->isEmpty('record', true) && !$request->isEmpty('user_password', true)) {
			$checkPassword = Settings_Password_Record_Model::checkPassword($request->getRaw('user_password'));
			if ($checkPassword) {
				$message = $checkPassword;
			}
		}
		if ($message) {
			App\Log::error($message);
			header('location: index.php?module=Users&parent=Settings&view=Edit');

			return false;
		}
		$recordModel = $this->saveRecord($request);
		$settingsModuleModel = Settings_Users_Module_Model::getInstance();
		$settingsModuleModel->refreshSwitchUsers();
		if ($request->getBoolean('relationOperation')) {
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('sourceRecord'), $request->getByType('sourceModule', 2));
			$loadUrl = $parentRecordModel->getDetailViewUrl();
		} elseif ($request->getBoolean('isPreference')) {
			$loadUrl = $recordModel->getPreferenceDetailViewUrl();
		} else {
			$loadUrl = $recordModel->getDetailViewUrl();
		}
		header("location: $loadUrl");
	}
}
