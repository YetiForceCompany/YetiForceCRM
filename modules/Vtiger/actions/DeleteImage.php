<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_DeleteImage_Action extends \App\Controller\Action
{
	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->getInteger('id');
		if (!(\App\Privilege::isPermitted($moduleName, 'EditView', $record) && \App\Privilege::isPermitted($moduleName, 'Delete', $record))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	public function process(\App\Request $request)
	{
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');
		$imageId = $request->getInteger('imageid');

		$response = new Vtiger_Response();
		if ($recordId) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleModel);
			$status = $recordModel->deleteImage($imageId);
			if ($status) {
				$response->setResult([\App\Language::translate('LBL_IMAGE_DELETED_SUCCESSFULLY', $moduleName)]);
			}
		} else {
			$response->setError(\App\Language::translate('LBL_IMAGE_NOT_DELETED', $moduleName));
		}

		$response->emit();
	}
}
