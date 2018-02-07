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

class Reports_Folder_Action extends \App\Controller\Action
{

	use \App\Controller\ExposeMethod;

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('save');
		$this->exposeMethod('delete');
	}

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		if (!Users_Privileges_Model::getCurrentUserPrivilegesModel()->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Function that saves/updates the Folder
	 * @param \App\Request $request
	 */
	public function save(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$folderModel = Reports_Folder_Model::getInstance();
		$folderId = $request->get('folderid');

		if (!empty($folderId)) {
			$folderModel->set('folderid', (int) $folderId);
		}

		$folderModel->set('foldername', $request->get('foldername'));
		$folderModel->set('description', $request->get('description'));

		if ($folderModel->checkDuplicate()) {
			throw new \App\Exceptions\AppException('LBL_DUPLICATES_EXIST');
		}

		$folderModel->save();
		$result = ['success' => true, 'message' => \App\Language::translate('LBL_FOLDER_SAVED', $moduleName), 'info' => $folderModel->getInfoArray()];

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Function that deletes the Folder
	 * @param \App\Request $request
	 */
	public function delete(\App\Request $request)
	{
		$folderId = $request->getInteger('folderid');
		$moduleName = $request->getModule();

		if ($folderId) {
			$folderModel = Reports_Folder_Model::getInstanceById($folderId);

			if ($folderModel->isDefault()) {
				$message = \App\Language::translate('LBL_FOLDER_CAN_NOT_BE_DELETED', $moduleName);
			} else {
				if ($folderModel->hasReports()) {
					$message = \App\Language::translate('LBL_FOLDER_NOT_EMPTY', $moduleName);
				}
			}
			if ($message) {
				$result = ['success' => false, 'message' => $message];
			} else {
				$folderModel->delete();
				$result = ['success' => true, 'message' => \App\Language::translate('LBL_FOLDER_DELETED', $moduleName)];
			}

			$response = new Vtiger_Response();
			$response->setResult($result);
			$response->emit();
		}
	}

	public function validateRequest(\App\Request $request)
	{
		$request->validateWriteAccess();
	}
}
