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

class Vtiger_Save_Action extends \App\Controller\Action
{
	use \App\Controller\ExposeMethod;
	/**
	 * Record model instance.
	 *
	 * @var Vtiger_Record_Model
	 */
	protected $record;

	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('preSaveValidation');
		$this->exposeMethod('recordChanger');
	}

	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedToRecord
	 */
	public function checkPermission(App\Request $request)
	{
		$moduleName = $request->getModule();
		if ($request->isEmpty('record', true)) {
			$this->record = Vtiger_Record_Model::getCleanInstance($moduleName);
			if (!$this->record->isCreateable()) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		} else {
			$recordId = $request->getInteger('record');
			if (!\App\Privilege::isPermitted($moduleName, 'DetailView', $recordId)) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
			$this->record = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			if ('recordChanger' !== $request->getMode() && !$this->record->isEditable()) {
				throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
			}
		}
		if ($request->getBoolean('_isDuplicateRecord') && !\App\Privilege::isPermitted($moduleName, 'DetailView', $request->getInteger('_duplicateRecord'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if ($request->has('recordConverter') && !\App\RecordConverter::getInstanceById($request->getInteger('recordConverter'))->isPermitted($request->getInteger('sourceRecord'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		if ($request->getBoolean('relationOperation') && !\App\Privilege::isPermitted($request->getByType('sourceModule', 2), 'DetailView', $request->getInteger('sourceRecord'))) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		if ($mode = $request->getMode()) {
			$this->invokeExposedMethod($mode, $request);
		} else {
			$this->saveRecord($request);
			if ($request->getBoolean('relationOperation')) {
				$loadUrl = Vtiger_Record_Model::getInstanceById($request->getInteger('sourceRecord'), $request->getByType('sourceModule', 2))->getDetailViewUrl();
			} elseif ($request->getBoolean('returnToList')) {
				$loadUrl = $this->record->getModule()->getListViewUrl();
			} else {
				$this->record->clearPrivilegesCache();
				if ($this->record->isViewable()) {
					$loadUrl = $this->record->getDetailViewUrl();
				} else {
					$loadUrl = $this->record->getModule()->getDefaultUrl();
				}
			}
			header("location: $loadUrl");
		}
	}

	/** {@inheritdoc} */
	public function saveRecord(App\Request $request)
	{
		$this->getRecordModelFromRequest($request);
		$eventHandler = $this->record->getEventHandler();
		$skipHandlers = $request->getArray('skipHandlers', \App\Purifier::ALNUM, [], \App\Purifier::INTEGER);
		foreach ($eventHandler->getHandlers(\App\EventHandler::EDIT_VIEW_PRE_SAVE) as $handler) {
			$handlerId = $handler['eventhandler_id'];
			$response = $eventHandler->triggerHandler($handler);
			if (!($response['result'] ?? null) && (!isset($response['hash'], $skipHandlers[$handlerId]) || $skipHandlers[$handlerId] !== $response['hash'])) {
				throw new \App\Exceptions\NoPermittedToRecord($response['message'], 406);
			}
		}
		if (!$request->isEmpty('fromView') && 'MassQuickCreate' === $request->getByType('fromView')) {
			$this->multiSave($request);
		} else {
			$this->record->save();
			if ($request->has('recordConverter')) {
				$converter = \App\RecordConverter::getInstanceById($request->getInteger('recordConverter'))->set('sourceRecord', $request->getInteger('sourceRecord'));
				$eventHandler->setParams(['converter' => $converter]);
				$eventHandler->trigger(\App\EventHandler::RECORD_CONVERTER_AFTER_SAVE);
			}
		}
		if ($request->getBoolean('relationOperation')) {
			$relationId = $request->isEmpty('relationId') ? false : $request->getInteger('relationId');
			if ($relationModel = Vtiger_Relation_Model::getInstance(Vtiger_Module_Model::getInstance($request->getByType('sourceModule', 2)), $this->record->getModule(), $relationId)) {
				$relationModel->addRelation($request->getInteger('sourceRecord'), $this->record->getId());
			}
		}
	}

	/**
	 * Function to get the record model based on the request parameters.
	 *
	 * @param \App\Request $request
	 *
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	protected function getRecordModelFromRequest(App\Request $request)
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
		if ($request->has('inventory') && $this->record->getModule()->isInventory()) {
			$this->record->initInventoryDataFromRequest($request);
		}
		$fromView = $request->has('fromView') ? $request->getByType('fromView') : ($request->isEmpty('record', true) ? 'Create' : 'Edit');
		$fieldsDependency = \App\FieldsDependency::getByRecordModel($fromView, $this->record);
		if ($fields = array_merge($fieldsDependency['hide']['frontend'], $fieldsDependency['hide']['backend'])) {
			foreach ($fields as $fieldName) {
				$this->record->revertPreviousValue($fieldName);
			}
		}
		return $this->record;
	}

	/**
	 * Validation before saving.
	 *
	 * @param App\Request $request
	 */
	public function preSaveValidation(App\Request $request)
	{
		$this->getRecordModelFromRequest($request);
		$eventHandler = $this->record->getEventHandler();
		$result = [];
		$skipHandlers = $request->getArray('skipHandlers', \App\Purifier::ALNUM, [], \App\Purifier::INTEGER);
		foreach ($eventHandler->getHandlers(\App\EventHandler::EDIT_VIEW_PRE_SAVE) as $handler) {
			$handlerId = $handler['eventhandler_id'];
			$response = $eventHandler->triggerHandler($handler);
			if (!($response['result'] ?? null) && (!isset($response['hash'], $skipHandlers[$handlerId]) || $skipHandlers[$handlerId] !== $response['hash'])) {
				$result[$handlerId] = $response;
			}
		}
		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Quick change of record value.
	 *
	 * @param App\Request $request
	 */
	public function recordChanger(App\Request $request)
	{
		$this->getRecordModelFromRequest($request);
		$id = $request->getInteger('id');
		$field = App\Field::getQuickChangerFields($this->record->getModule()->getId())[$id] ?? false;
		if (!$field || !App\Field::checkQuickChangerConditions($field, $this->record)) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
		$fields = $this->record->getModule()->getFields();
		foreach ($field['values'] as $fieldName => $value) {
			if (isset($fields[$fieldName]) && $fields[$fieldName]->isEditable()) {
				$this->record->set($fieldName, $value);
			}
		}
		$this->record->save();
		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult(true);
		$response->emit();
	}

	/**
	 * Multiple record save mode.
	 *
	 * @param App\Request $request
	 *
	 * @return void
	 */
	protected function multiSave(App\Request $request): void
	{
		$moduleName = $request->getByType('module', 'Alnum');
		$multiSaveField = $request->getByType('multiSaveField', 'Alnum');
		$sourceModule = $request->getByType('sourceModule', 'Alnum');
		$sourceView = $request->getByType('sourceView');
		if ('ListView' === $sourceView) {
			$request->set('module', $sourceModule);
			$ids = Vtiger_Mass_Action::getRecordsListFromRequest($request);
			$request->set('module', $moduleName);
		} elseif ('RelatedListView' === $sourceView) {
			$request->set('module', $request->getByType('relatedModule', 'Alnum'));
			$request->set('relatedModule', $request->getByType('sourceModule', 'Alnum'));
			$request->set('record', $request->getByType('relatedRecord', 'Alnum'));
			$ids = Vtiger_RelationAjax_Action::getRecordsListFromRequest($request);
			$request->set('module', $moduleName);
		}
		foreach ($ids as $id) {
			$recordModel = \Vtiger_Record_Model::getCleanInstance($this->record->getModuleName());
			$recordModel->setData($this->record->getData());
			$recordModel->ext = $this->record->ext;
			$recordModel->set($multiSaveField, $id);
			$recordModel->save();
		}
	}
}
