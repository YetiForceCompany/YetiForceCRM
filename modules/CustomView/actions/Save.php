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

class CustomView_Save_Action extends Vtiger_Action_Controller
{

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		if (!App\Privilege::isPermitted($request->getByType('source_module', 1), 'CreateCustomFilter')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Main process action
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$moduleModel = Vtiger_Module_Model::getInstance($request->getByType('source_module', 1));
		$customViewModel = $this->getCVModelFromRequest($request);
		$response = new Vtiger_Response();

		if (!$customViewModel->checkDuplicate()) {
			$customViewModel->save();
			$cvId = $customViewModel->getId();
			\App\Cache::delete('CustomView_Record_ModelgetInstanceById', $cvId);
			$response->setResult(array('id' => $cvId, 'listviewurl' => $moduleModel->getListViewUrl() . '&viewname=' . $cvId));
		} else {
			$response->setError(\App\Language::translate('LBL_CUSTOM_VIEW_NAME_DUPLICATES_EXIST', $request->getModule()));
		}

		$response->emit();
	}

	/**
	 * Function to get the custom view model based on the request parameters
	 * @param \App\Request $request
	 * @return CustomView_Record_Model or Module specific Record Model instance
	 */
	private function getCVModelFromRequest(\App\Request $request)
	{
		$cvId = $request->get('record');

		if (!empty($cvId)) {
			$customViewModel = CustomView_Record_Model::getInstanceById($cvId);
		} else {
			$customViewModel = CustomView_Record_Model::getCleanInstance();
			$customViewModel->setModule($request->getByType('source_module', 1));
		}
		$setmetrics = empty($request->get('setmetrics')) ? 0 : $request->get('setmetrics');
		$customViewData = array(
			'cvid' => $cvId,
			'viewname' => $request->get('viewname'),
			'setdefault' => $request->get('setdefault'),
			'setmetrics' => $setmetrics,
			'status' => $request->get('status'),
			'featured' => $request->get('featured'),
			'color' => $request->get('color'),
			'description' => $request->get('description')
		);
		$selectedColumnsList = $request->get('columnslist');
		if (empty($selectedColumnsList)) {
			$moduleModel = Vtiger_Module_Model::getInstance($request->getByType('source_module', 1));
			$cvIdDefault = $moduleModel->getAllFilterCvidForModule();
			if ($cvIdDefault === false) {
				$cvId = App\CustomView::getInstance($request->getByType('source_module', 1))->getDefaultCvId();
			}
			$defaultCustomViewModel = CustomView_Record_Model::getInstanceById($cvIdDefault);
			$selectedColumnsList = $defaultCustomViewModel->getSelectedFields();
		}
		$customViewData['columnslist'] = $selectedColumnsList;
		$stdFilterList = $request->get('stdfilterlist');
		if (!empty($stdFilterList)) {
			$customViewData['stdfilterlist'] = $stdFilterList;
		}
		$advFilterList = $request->get('advfilterlist');
		if (!empty($advFilterList)) {
			$customViewData['advfilterlist'] = $advFilterList;
		}

		return $customViewModel->setData($customViewData);
	}

	public function validateRequest(\App\Request $request)
	{
		$request->validateWriteAccess();
	}
}
