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

class CustomView_Save_Action extends \App\Controller\Action
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(\App\Request $request)
	{
		if (\App\User::getCurrentUserModel()->isAdmin()) {
			return;
		}
		if ($request->has('record') && !CustomView_Record_Model::getInstanceById($request->getInteger('record'))->isEditable()) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (!App\Privilege::isPermitted($request->getByType('source_module', 2), 'CreateCustomFilter')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$moduleModel = Vtiger_Module_Model::getInstance($request->getByType('source_module', 2));
		$customViewModel = $this->getCVModelFromRequest($request);
		$response = new Vtiger_Response();

		if (!$customViewModel->checkDuplicate()) {
			$customViewModel->save();
			$cvId = $customViewModel->getId();
			\App\Cache::delete('CustomView_Record_ModelgetInstanceById', $cvId);
			$response->setResult(['id' => $cvId, 'listviewurl' => $moduleModel->getListViewUrl() . '&viewname=' . $cvId]);
		} else {
			$response->setError(\App\Language::translate('LBL_CUSTOM_VIEW_NAME_DUPLICATES_EXIST', $request->getModule()));
		}

		$response->emit();
	}

	/**
	 * Function to get the custom view model based on the request parameters.
	 *
	 * @param \App\Request $request
	 *
	 * @return CustomView_Record_Model or Module specific Record Model instance
	 */
	private function getCVModelFromRequest(\App\Request $request)
	{
		$cvId = $request->getInteger('record');

		if (!empty($cvId)) {
			$customViewModel = CustomView_Record_Model::getInstanceById($cvId);
		} else {
			$customViewModel = CustomView_Record_Model::getCleanInstance();
			$customViewModel->setModule($request->getByType('source_module', 2));
		}
		$customViewData = [
			'cvid' => $cvId,
			'viewname' => $request->get('viewname'),
			'setdefault' => $request->getInteger('setdefault'),
			'setmetrics' => $request->isEmpty('setmetrics') ? 0 : $request->getInteger('setmetrics'),
			'status' => $request->getInteger('status', 0),
			'featured' => $request->getInteger('featured', 0),
			'color' => $request->get('color'),
			'description' => $request->get('description'),
		];
		$selectedColumnsList = $request->get('columnslist');
		if (empty($selectedColumnsList)) {
			$moduleModel = Vtiger_Module_Model::getInstance($request->getByType('source_module', 2));
			$cvIdDefault = $moduleModel->getAllFilterCvidForModule();
			if ($cvIdDefault === false) {
				$cvId = App\CustomView::getInstance($request->getByType('source_module', 2))->getDefaultCvId();
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
}
