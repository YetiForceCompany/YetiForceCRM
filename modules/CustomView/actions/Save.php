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

class CustomView_Save_Action extends \App\Controller\Action
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		if ($request->has('record') && !CustomView_Record_Model::getInstanceById($request->getInteger('record'))->isEditable()) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
		if (!App\Privilege::isPermitted($request->getByType('source_module', 2), 'CreateCustomFilter')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleModel = Vtiger_Module_Model::getInstance($request->getByType('source_module', 2));
		$customViewModel = $this->getCVModelFromRequest($request);
		$response = new Vtiger_Response();
		if (!$customViewModel->checkDuplicate()) {
			$customViewModel->save();
			$cvId = $customViewModel->getId();
			$url = $moduleModel->getListViewUrl() . '&viewname=' . $cvId;
			if (!$request->isEmpty('mid', 'Alnum')) {
				$url .= '&mid=' . $request->getInteger('mid');
			}
			$response->setResult(['success' => true, 'id' => $cvId, 'listviewurl' => $url]);
		} else {
			$response->setResult([
				'success' => false,
				'message' => \App\Language::translate('LBL_CUSTOM_VIEW_NAME_DUPLICATES_EXIST', $request->getModule(false)),
			]);
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
	private function getCVModelFromRequest(App\Request $request)
	{
		$cvId = $request->getInteger('record');
		if (!empty($cvId)) {
			$customViewModel = CustomView_Record_Model::getInstanceById($cvId);
		} else {
			$customViewModel = CustomView_Record_Model::getCleanInstance();
			$customViewModel->setModule($request->getByType('source_module', \App\Purifier::ALNUM));
		}
		$customViewData = [
			'cvid' => $cvId,
			'viewname' => $request->getByType('viewname', 'Text'),
			'setdefault' => $request->getInteger('setdefault'),
			'setmetrics' => $request->isEmpty('setmetrics') ? 0 : $request->getInteger('setmetrics'),
			'status' => $request->getInteger('status', \App\CustomView::CV_STATUS_PRIVATE),
			'featured' => $request->getInteger('featured', 0),
			'color' => !$request->isEmpty('color') ? $request->getByType('color', 'Color') : '',
			'description' => $request->getForHtml('description'),
		];
		$selectedColumnsList = $request->getArray('columnslist', 'Text');
		if (empty($selectedColumnsList)) {
			$cvIdDefault = App\CustomView::getInstance($request->getByType('source_module', \App\Purifier::ALNUM))->getDefaultCvId();
			$defaultCustomViewModel = CustomView_Record_Model::getInstanceById($cvIdDefault);
			$selectedColumnsList = array_keys($defaultCustomViewModel->getSelectedFields());
		}
		$customViewData['columnslist'] = $selectedColumnsList;
		$customFieldNames = $request->getArray('customFieldNames', 'Text');
		array_walk($customFieldNames, function (&$customLabel) {
			$customLabel = \App\Purifier::decodeHtml(trim($customLabel));
		});
		$customViewData['customFieldNames'] = $customFieldNames;
		$advFilterList = $request->getArray('advfilterlist', 'Text');
		if (!empty($advFilterList)) {
			$customViewData['advfilterlist'] = $advFilterList;
		}
		$duplicateFields = $request->getMultiDimensionArray('duplicatefields', [
			[
				'fieldid' => 'Integer',
				'ignore' => 'Bool',
			],
		]);
		if (!empty($duplicateFields)) {
			$customViewData['duplicatefields'] = $duplicateFields;
		}
		$advancedConditions = $request->getArray('advanced_conditions', 'Text');
		if (!empty($advancedConditions['relationId']) || !empty($advancedConditions['relationColumns'])) {
			\App\Condition::validAdvancedConditions($advancedConditions);
			$customViewData['advanced_conditions'] = \App\Json::encode($advancedConditions);
		}
		return $customViewModel->setData($customViewData);
	}
}
