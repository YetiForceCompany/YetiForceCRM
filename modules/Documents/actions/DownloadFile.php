<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Documents_DownloadFile_Action extends \App\Controller\Action
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		if ($request->isEmpty('record')) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if (!\App\Privilege::isPermitted($request->getModule(), 'DetailView', $request->getInteger('record'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();

		$documentRecordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $moduleName);
		//Download the file
		$documentRecordModel->downloadFile();
		//Update the Download Count
		$documentRecordModel->updateDownloadCount();
	}
}
