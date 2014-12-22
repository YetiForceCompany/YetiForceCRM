<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Documents_MoveDocuments_Action extends Vtiger_Mass_Action {

	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();

		if(!Users_Privileges_Model::isPermitted($moduleName, 'EditView')) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$documentIdsList = $this->getRecordsListFromRequest($request);
		$folderId = $request->get('folderid');

		if (!empty ($documentIdsList)) {
			foreach ($documentIdsList as $documentId) {
				$documentModel = Vtiger_Record_Model::getInstanceById($documentId, $moduleName);
				if (Users_Privileges_Model::isPermitted($moduleName, 'EditView', $documentId)) {
					$documentModel->set('folderid', $folderId);
					$documentModel->set('mode', 'edit');
					$documentModel->save();
				} else {
					$documentsMoveDenied[] = $documentModel->getName();
				}
			}
		}
		if (empty ($documentsMoveDenied)) {
			$result = array('success'=>true, 'message'=>vtranslate('LBL_DOCUMENTS_MOVED_SUCCESSFULLY', $moduleName));
		} else {
			$result = array('success'=>false, 'message'=>vtranslate('LBL_DENIED_DOCUMENTS', $moduleName), 'LBL_RECORDS_LIST'=>$documentsMoveDenied);
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}