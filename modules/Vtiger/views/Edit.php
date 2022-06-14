<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class Vtiger_Edit_View extends Vtiger_Index_View
{
	/**
	 * Record model instance.
	 *
	 * @var Vtiger_Record_Model
	 */
	protected $record;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$moduleName = $request->getModule();
		if ($request->has('record')) {
			$this->record = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $moduleName);
			$isPermitted = $this->record->isEditable() || (true === $request->getBoolean('isDuplicate') && $this->record->getModule()->isPermitted('DuplicateRecord') && $this->record->isCreateable() && $this->record->isViewable());
		} else {
			$this->record = Vtiger_Record_Model::getCleanInstance($moduleName);
			$isPermitted = $this->record->isCreateable();
		}
		if (!$isPermitted) {
			throw new \App\Exceptions\NoPermittedToRecord('ERR_NO_PERMISSIONS_FOR_THE_RECORD', 406);
		}
	}

	/**
	 * Get breadcrumb title.
	 *
	 * @param \App\Request $request
	 *
	 * @return string
	 */
	public function getBreadcrumbTitle(App\Request $request)
	{
		$moduleName = $request->getModule();
		if ($request->has('isDuplicate')) {
			$pageTitle = App\Language::translate('LBL_VIEW_DUPLICATE', $moduleName);
		} elseif ($request->has('record')) {
			$pageTitle = App\Language::translate('LBL_VIEW_EDIT', $moduleName);
		} else {
			$pageTitle = App\Language::translate('LBL_VIEW_CREATE', $moduleName);
		}
		return $pageTitle;
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$recordId = $request->getInteger('record');
		$mode = '';
		$viewer->assign('RECORD_ID', '');
		if (!empty($recordId) && true === $request->getBoolean('isDuplicate')) {
			$mode = 'duplicate';
			$this->getDuplicate();
		} elseif (!empty($recordId)) {
			$mode = 'edit';
			$viewer->assign('RECORD_ID', $recordId);
		} elseif (!$request->isEmpty('recordConverter')) {
			$convertInstance = \App\RecordConverter::getInstanceById($request->getInteger('recordConverter'), $request->getByType('sourceModule', 2));
			$this->record = $convertInstance->processToEdit($request->getInteger('sourceRecord'), $moduleName);
			$viewer->assign('RECORD_CONVERTER', $convertInstance->getId());
			$viewer->assign('SOURCE_RECORD', $request->getInteger('sourceRecord'));
		} else {
			$referenceId = $request->getInteger('reference_id');
			if ($referenceId) {
				$parentRecordModel = Vtiger_Record_Model::getInstanceById($referenceId);
				$this->record->setRecordFieldValues($parentRecordModel);
			}
		}
		$editModel = Vtiger_EditView_Model::getInstance($moduleName, $recordId);
		$viewLinks = $editModel->getEditViewLinks(['MODULE' => $moduleName, 'RECORD' => $recordId]);

		$moduleModel = $this->record->getModule();
		$fieldList = $moduleModel->getFields();
		foreach (array_intersect($request->getKeys(), array_keys($fieldList)) as $fieldName) {
			$fieldModel = $fieldList[$fieldName];
			if ($fieldModel->isWritable()) {
				$fieldModel->getUITypeModel()->setValueFromRequest($request, $this->record);
			}
		}
		if ($moduleModel->isInventory() && !$request->isEmpty('inventory')) {
			$this->record->initInventoryDataFromRequest($request);
		}
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($this->record, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
		$recordStructure = $recordStructureInstance->getStructure();
		$isRelationOperation = $request->getBoolean('relationOperation');
		//if it is relation edit
		$viewer->assign('IS_RELATION_OPERATION', $isRelationOperation);
		if ($isRelationOperation) {
			$viewer->assign('SOURCE_MODULE', $request->getByType('sourceModule', 2));
			$viewer->assign('SOURCE_RECORD', $request->getInteger('sourceRecord'));
			$sourceRelatedField = $moduleModel->getValuesFromSource($request);
			foreach ($recordStructure as $block) {
				foreach ($sourceRelatedField as $field => $value) {
					if (isset($block[$field]) && '' !== $value) {
						$fieldModel = $block[$field];
						if ($fieldModel->isEditable() && ('' === $fieldModel->get('fieldvalue') || null === $fieldModel->get('fieldvalue'))) {
							$fieldModel->set('fieldvalue', $value);
						}
					}
				}
			}
		}

		$eventHandler = new App\EventHandler();
		$eventHandler->setRecordModel($this->record);
		$eventHandler->setModuleName($moduleName);
		$eventHandler->setParams([
			'mode' => ucfirst($mode),
			'viewLinks' => $viewLinks,
			'viewInstance' => $this,
		]);
		$eventHandler->trigger('EditViewBefore');
		['viewLinks' => $viewLinks] = $eventHandler->getParams();

		if ($editViewLayout = ((1 === $moduleModel->getModuleType() || (\in_array($moduleName, \App\Config::performance('MODULES_SPLITTED_EDIT_VIEW_LAYOUT', [])))) && \App\Config::performance('INVENTORY_EDIT_VIEW_LAYOUT'))) {
			$recordStructureRight = [];
			foreach ($moduleModel->getFieldsByType('text') as $field) {
				if (isset($recordStructure[$field->getBlockName()][$field->getName()])) {
					$recordStructureRight[$field->getBlockName()] = $recordStructure[$field->getBlockName()];
					unset($recordStructure[$field->getBlockName()]);
				}
			}
			$viewer->assign('RECORD_STRUCTURE_RIGHT', $recordStructureRight);
		}

		$viewer->assign('MODE', $mode);
		$viewer->assign('EDITVIEW_LINKS', $viewLinks);
		$viewer->assign('EDIT_VIEW_LAYOUT', $editViewLayout);
		$viewer->assign('ADDRESS_BLOCK_LABELS', ['LBL_ADDRESS_INFORMATION', 'LBL_ADDRESS_MAILING_INFORMATION', 'LBL_ADDRESS_DELIVERY_INFORMATION', 'LBL_ADDRESS_BILLING', 'LBL_ADDRESS_SHIPPING']);
		$viewer->assign('RECORD_STRUCTURE', $recordStructure);
		$viewer->assign('MAPPING_RELATED_FIELD', \App\Json::encode(\App\ModuleHierarchy::getRelationFieldByHierarchy($moduleName)));
		$viewer->assign('LIST_FILTER_FIELDS', \App\Json::encode(\App\ModuleHierarchy::getFieldsForListFilter($moduleName)));
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('MODULE_TYPE', $moduleModel->getModuleType());
		$viewer->assign('RECORD', $this->record);
		$viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('RECORD_ACTIVITY_NOTIFIER', $recordId && \App\Config::performance('recordActivityNotifier', false) && $moduleModel->isTrackingEnabled() && $moduleModel->isPermitted('RecordActivityNotifier'));
		$viewer->view('EditView.tpl', $moduleName);
	}

	/**
	 * Set duplicate data.
	 */
	public function getDuplicate()
	{
		$fromRecord = $this->record->getId();
		$this->record->set('id', '');
		foreach ($this->record->getModule()->getFields() as $fieldModel) {
			if ((!$fieldModel->isDuplicable() && !$this->record->isEmpty($fieldModel->getName()))
				|| ($fieldModel->isReferenceField() && ($value = $this->record->get($fieldModel->getName())) && !\App\Record::isExists($value))
			) {
				$this->record->set($fieldModel->getName(), '');
			}
		}

		$eventHandler = new App\EventHandler();
		$eventHandler->setRecordModel($this->record);
		$eventHandler->setModuleName($this->record->getModuleName());
		$eventHandler->setParams([
			'fromRecord' => $fromRecord,
			'viewInstance' => $this,
		]);
		$eventHandler->trigger('EditViewDuplicate');
	}

	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(App\Request $request)
	{
		$parentScript = parent::getFooterScripts($request);

		$moduleName = $request->getModule();
		if (Vtiger_Module_Model::getInstance($moduleName)->isInventory()) {
			$fileNames = [
				'modules.Vtiger.resources.Inventory',
				'modules.' . $moduleName . '.resources.Inventory',
			];
			$scriptInstances = $this->checkAndConvertJsScripts($fileNames);
			$parentScript = array_merge($parentScript, $scriptInstances);
		}
		return $parentScript;
	}
}
