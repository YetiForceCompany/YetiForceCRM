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

	public function process(Vtiger_Request $request)
	{
		$moduleModel = Vtiger_Module_Model::getInstance($request->get('source_module'));
		$customViewModel = $this->getCVModelFromRequest($request);
		$response = new Vtiger_Response();

		if (!$customViewModel->checkDuplicate()) {
			$customViewModel->save();
			$cvId = $customViewModel->getId();
			$response->setResult(array('id' => $cvId, 'listviewurl' => $moduleModel->getListViewUrl() . '&viewname=' . $cvId));
		} else {
			$response->setError(vtranslate('LBL_CUSTOM_VIEW_NAME_DUPLICATES_EXIST', $moduleName));
		}

		$response->emit();
	}

	/**
	 * Function to get the custom view model based on the request parameters
	 * @param Vtiger_Request $request
	 * @return CustomView_Record_Model or Module specific Record Model instance
	 */
	private function getCVModelFromRequest(Vtiger_Request $request)
	{
		$cvId = $request->get('record');

		if (!empty($cvId)) {
			$customViewModel = CustomView_Record_Model::getInstanceById($cvId);
		} else {
			$customViewModel = CustomView_Record_Model::getCleanInstance();
			$customViewModel->setModule($request->get('source_module'));
		}

		$customViewData = array(
			'cvid' => $cvId,
			'viewname' => $request->get('viewname'),
			'setdefault' => $request->get('setdefault'),
			'setmetrics' => $request->get('setmetrics'),
			'status' => $request->get('status'),
			'featured' => $request->get('featured'),
			'color' => $request->get('color'),
			'description' => $request->get('description')
		);
		$selectedColumnsList = $request->get('columnslist');
		if (empty($selectedColumnsList)) {
			$moduleModel = Vtiger_Module_Model::getInstance($request->get('source_module'));
			$cvIdDefault = $moduleModel->getAllFilterCvidForModule();
			if ($cvIdDefault === false) {
				$cvIdDefault = CustomView::getDefaultCvId($request->get('source_module'));
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

	public function validateRequest(Vtiger_Request $request)
	{
		$request->validateWriteAccess();
	}
}
