<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_Save_Action extends Vtiger_Action_Controller
{

	protected $record = false;

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->get('record');

		if (!empty($record)) {
			$recordModel = $this->record ? $this->record : Vtiger_Record_Model::getInstanceById($record, $moduleName);
			if (!$recordModel->isEditable()) {
				throw new \Exception\NoPermittedToRecord('LBL_PERMISSION_DENIED');
			}
		} else {
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			if (!$recordModel->isCreateable()) {
				throw new \Exception\NoPermittedToRecord('LBL_PERMISSION_DENIED');
			}
		}
	}

	public function preProcess(Vtiger_Request $request)
	{
		parent::preProcess($request);
		if (Vtiger_Session::has('baseUserId') && !empty(Vtiger_Session::get('baseUserId'))) {
			$baseUserId = Vtiger_Session::get('baseUserId');
			$user = new Users();
			$currentUser = $user->retrieveCurrentUserInfoFromFile($baseUserId);
			vglobal('current_user', $currentUser);
		}
	}

	public function preProcessAjax(Vtiger_Request $request)
	{
		parent::preProcessAjax($request);
		if (Vtiger_Session::has('baseUserId') && !empty(Vtiger_Session::get('baseUserId'))) {
			$baseUserId = Vtiger_Session::get('baseUserId');
			$user = new Users();
			$currentUser = $user->retrieveCurrentUserInfoFromFile($baseUserId);
			vglobal('current_user', $currentUser);
		}
	}

	public function process(Vtiger_Request $request)
	{
		$recordModel = $this->saveRecord($request);
		if ($request->get('relationOperation')) {
			$parentModuleName = $request->get('sourceModule');
			$parentRecordId = $request->get('sourceRecord');
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentRecordId, $parentModuleName);
			$loadUrl = $parentRecordModel->getDetailViewUrl();
		} else if ($request->get('returnToList')) {
			$loadUrl = $recordModel->getModule()->getListViewUrl();
		} else {
			$loadUrl = $recordModel->getDetailViewUrl();
		}
		header("Location: $loadUrl");
	}

	/**
	 * Function to save record
	 * @param <Vtiger_Request> $request - values of the record
	 * @return <RecordModel> - record Model of saved record
	 */
	public function saveRecord($request)
	{
		$recordModel = $this->getRecordModelFromRequest($request);
		$recordModel->save();
		if ($request->get('relationOperation')) {
			$parentModuleName = $request->get('sourceModule');
			$parentModuleModel = Vtiger_Module_Model::getInstance($parentModuleName);
			$parentRecordId = $request->get('sourceRecord');
			$relatedModule = $recordModel->getModule();
			$relatedRecordId = $recordModel->getId();

			$relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModule);
			if ($relationModel)
				$relationModel->addRelation($parentRecordId, $relatedRecordId);
		}
		if ($request->get('imgDeleted')) {
			$imageIds = $request->get('imageid');
			foreach ($imageIds as $imageId) {
				$status = $recordModel->deleteImage($imageId);
			}
		}
		return $recordModel;
	}

	/**
	 * Function to get the record model based on the request parameters
	 * @param Vtiger_Request $request
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	protected function getRecordModelFromRequest(Vtiger_Request $request)
	{

		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		if (!empty($recordId)) {
			$recordModel = $this->record ? $this->record : Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			$recordModel->set('id', $recordId);
			$recordModel->set('mode', 'edit');
		} else {
			$recordModel = $this->record ? $this->record : Vtiger_Record_Model::getCleanInstance($moduleName);
			$recordModel->set('mode', '');
		}

		$fieldModelList = $moduleModel->getFields();
		foreach ($fieldModelList as $fieldName => $fieldModel) {
			if (!$fieldModel->isEditEnabled()) {
				continue;
			}
			if ($request->has($fieldName) && $fieldModel->get('uitype') == 300) {
				$fieldValue = $request->getForHtml($fieldName, null);
			} else if ($request->has($fieldName)) {
				$fieldValue = $request->get($fieldName, null);
			} else if (in_array($fieldModel->getDisplayType(), [3, 5])) {
				$fieldValue = $recordModel->get($fieldName);
			} else {
				$fieldValue = $fieldModel->getDefaultFieldValue();
			}
			$fieldDataType = $fieldModel->getFieldDataType();
			if ($fieldDataType == 'time') {
				$fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
			}
			if ($fieldValue !== null) {
				if (!is_array($fieldValue)) {
					$fieldValue = trim($fieldValue);
				}
				$recordModel->set($fieldName, $fieldValue);
			} else
				$recordModel->set($fieldName, null);
		}
		return $recordModel;
	}

	public function validateRequest(Vtiger_Request $request)
	{
		return $request->validateWriteAccess();
	}
}
