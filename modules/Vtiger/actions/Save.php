<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_Save_Action extends \App\Controller\Action
{

	/**
	 * Record model instance
	 * @var Vtiger_Record_Model
	 */
	protected $record = false;

	/**
	 * Function to check permission
	 * @param \App\Request $request
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(\App\Request $request)
	{
		$moduleName = $request->getModule();
		if (!$request->isEmpty('record', true)) {
			$recordId = $request->getInteger('record');
			if (!\App\Privilege::isPermitted($moduleName, 'DetailView', $recordId)) {
				throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
			$this->record = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			if (!$this->record->isEditable()) {
				throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		} else {
			$this->record = Vtiger_Record_Model::getCleanInstance($moduleName);
			if (!$this->record->isCreateable()) {
				throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		}
		if ($request->getBoolean('relationOperation') && !\App\Privilege::isPermitted($request->getByType('sourceModule', 2), 'DetailView', $request->getInteger('sourceRecord'))) {
			throw new \App\Exceptions\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	public function preProcess(\App\Request $request, $display = true)
	{
		parent::preProcess($request);
		if (App\Session::has('baseUserId') && !empty(App\Session::get('baseUserId'))) {
			$baseUserId = App\Session::get('baseUserId');
			$user = new Users();
			$currentUser = $user->retrieveCurrentUserInfoFromFile($baseUserId);
			vglobal('current_user', $currentUser);
			App\User::setCurrentUserId($baseUserId);
		}
	}

	public function preProcessAjax(\App\Request $request)
	{
		parent::preProcessAjax($request);
		if (App\Session::has('baseUserId') && !empty(App\Session::get('baseUserId'))) {
			$baseUserId = App\Session::get('baseUserId');
			$user = new Users();
			$currentUser = $user->retrieveCurrentUserInfoFromFile($baseUserId);
			vglobal('current_user', $currentUser);
			App\User::setCurrentUserId($baseUserId);
		}
	}

	public function process(\App\Request $request)
	{
		$recordModel = $this->saveRecord($request);
		if ($request->getBoolean('relationOperation')) {
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('sourceRecord'), $request->getByType('sourceModule', 2));
			$loadUrl = $parentRecordModel->getDetailViewUrl();
		} else if ($request->getBoolean('returnToList')) {
			$loadUrl = $recordModel->getModule()->getListViewUrl();
		} else {
			$loadUrl = $recordModel->getDetailViewUrl();
		}
		header("Location: $loadUrl");
	}

	/**
	 * Function to save record
	 * @param \App\Request $request - values of the record
	 * @return Vtiger_Record_Model - record Model of saved record
	 */
	public function saveRecord(\App\Request $request)
	{
		$recordModel = $this->getRecordModelFromRequest($request);
		$recordModel->save();
		if ($request->getBoolean('relationOperation')) {
			$parentModuleModel = Vtiger_Module_Model::getInstance($request->getByType('sourceModule', 2));
			$relatedModule = $recordModel->getModule();
			$relatedRecordId = $recordModel->getId();
			$relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModule);
			if ($relationModel) {
				$relationModel->addRelation($request->getInteger('sourceRecord'), $relatedRecordId);
			}
		}
		if ($request->getBoolean('imgDeleted')) {
			$imageIds = $request->getArray('imageid');
			foreach ($imageIds as $imageId) {
				$recordModel->deleteImage((int) $imageId);
			}
		}
		return $recordModel;
	}

	/**
	 * Function to get the record model based on the request parameters
	 * @param \App\Request $request
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	protected function getRecordModelFromRequest(\App\Request $request)
	{
		if (empty($this->record)) {
			$this->record = $request->isEmpty('record', true) ? Vtiger_Record_Model::getCleanInstance($request->getModule()) : Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $request->getModule());
		}
		$fieldModelList = $this->record->getModule()->getFields();
		foreach ($fieldModelList as $fieldName => $fieldModel) {
			if (!$fieldModel->isWritable()) {
				continue;
			}
			if ($request->has($fieldName)) {
				$fieldModel->getUITypeModel()->setValueFromRequest($request, $this->record);
			}
		}
		return $this->record;
	}

	public function validateRequest(\App\Request $request)
	{
		return $request->validateWriteAccess();
	}
}
