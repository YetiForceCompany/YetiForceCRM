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

class ModComments_SaveAjax_Action extends Vtiger_SaveAjax_Action
{

	public function checkPermission(Vtiger_Request $request)
	{
		$record = $request->get('record');
		//Do not allow ajax edit of existing comments
		if ($record) {
			throw new \Exception\AppException('LBL_PERMISSION_DENIED');
		}
		if(!App\Privilege::isPermitted($request->getModule(), 'CreateView')){
			throw new \Exception\AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request)
	{
		$request->set('assigned_user_id', App\User::getCurrentUserId());
		$recordModel = $this->saveRecord($request);
		$fieldModelList = $recordModel->getModule()->getFields();
		$result = [];
		foreach ($fieldModelList as $fieldName => $fieldModel) {
			$fieldValue = $recordModel->get($fieldName);
			$result[$fieldName] = array('value' => $fieldValue, 'display_value' => $fieldModel->getDisplayValue($fieldValue));
		}
		$result['id'] = $recordModel->getId();

		$result['_recordLabel'] = $recordModel->getName();
		$result['_recordId'] = $recordModel->getId();

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}
}
