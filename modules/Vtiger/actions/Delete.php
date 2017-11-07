<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_Delete_Action extends Vtiger_Action_Controller
{

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		if ($request->isEmpty('record', true)) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!\App\Privilege::isPermitted($moduleName, 'Delete', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $moduleName)->privilegeToDelete()) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * Main process
	 * @param \App\Request $request
	 * @return \Vtiger_Response
	 */
	public function process(\App\Request $request)
	{
		$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
		$recordModel->changeState('Deleted');
		$listViewUrl = $recordModel->getModule()->getListViewUrl();
		if ($request->getBoolean('ajaxDelete')) {
			$response = new Vtiger_Response();
			$response->setResult($listViewUrl);
			return $response;
		} else {
			header("Location: $listViewUrl");
		}
	}

	public function validateRequest(\App\Request $request)
	{
		$request->validateWriteAccess();
	}
}
