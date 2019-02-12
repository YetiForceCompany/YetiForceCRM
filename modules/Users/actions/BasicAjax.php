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

class Users_BasicAjax_Action extends Vtiger_BasicAjax_Action
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if (!$currentUser->isAdminUser()) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$searchValue = $request->getByType('search_value', 'Text');
		$searchModule = $request->getByType('search_module');
		$parentRecordId = $request->getInteger('parent_id');
		$parentModuleName = $request->getByType('parent_module');

		$searchModuleModel = Users_Module_Model::getInstance($searchModule);
		$records = $searchModuleModel->searchRecord($searchValue, $parentRecordId, $parentModuleName);
		$result = [];
		if (is_array($records)) {
			foreach ($records as $recordModels) {
				foreach ($recordModels as $recordModel) {
					$result[] = [
						'label' => App\Purifier::decodeHtml($recordModel->getName()),
						'value' => App\Purifier::decodeHtml($recordModel->getName()),
						'id' => $recordModel->getId(),
					];
				}
			}
		}
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}
