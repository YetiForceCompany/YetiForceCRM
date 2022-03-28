<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Documents_MoveDocuments_Action extends Vtiger_Mass_Action
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if (!\App\Privilege::isPermitted($request->getModule(), 'EditView')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$documentIdsList = $this->getRecordsListFromRequest($request);
		if (!empty($documentIdsList)) {
			foreach ($documentIdsList as $documentId) {
				$recordModel = Vtiger_Record_Model::getInstanceById($documentId, $moduleName);
				$fieldModel = $recordModel->getModule()->getFieldByName('folderid');
				if ($fieldModel && $fieldModel->isEditable()) {
					$fieldModel->getUITypeModel()->setValueFromRequest($request, $recordModel);
					$recordModel->save();
				} else {
					$documentsMoveDenied[] = $recordModel->getName();
				}
			}
		}
		if (empty($documentsMoveDenied)) {
			$result = ['success' => true, 'message' => \App\Language::translate('LBL_DOCUMENTS_MOVED_SUCCESSFULLY', $moduleName)];
		} else {
			$result = ['success' => false, 'message' => \App\Language::translate('LBL_DENIED_DOCUMENTS', $moduleName), 'LBL_RECORDS_LIST' => $documentsMoveDenied];
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
