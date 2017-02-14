<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

Class Vtiger_Edit_View extends Vtiger_Index_View
{

	protected $record = false;

	public function __construct()
	{
		parent::__construct();
	}

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$record = $request->get('record');

		if (!empty($record)) {
			$recordModel = $this->record ? $this->record : Vtiger_Record_Model::getInstanceById($record, $moduleName);
			$isPermited = $recordModel->isEditable() || ($request->getBoolean('isDuplicate') === true && $recordModel->isCreateable() && $recordModel->isViewable());
		} else {
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			$isPermited = $recordModel->isCreateable();
		}
		if (!$isPermited) {
			throw new \Exception\NoPermittedToRecord('LBL_NO_PERMISSIONS_FOR_THE_RECORD');
		}
	}

	public function getBreadcrumbTitle(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		if ($request->has('isDuplicate')) {
			$pageTitle = vtranslate('LBL_VIEW_DUPLICATE', $moduleName);
		} elseif ($request->has('record')) {
			$pageTitle = vtranslate('LBL_VIEW_EDIT', $moduleName);
		} else {
			$pageTitle = vtranslate('LBL_VIEW_CREATE', $moduleName);
		}
		return $pageTitle;
	}

	public function process(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$record = $request->get('record');
		if (!empty($record) && $request->getBoolean('isDuplicate') === true) {
			$viewer->assign('MODE', '');
			$viewer->assign('RECORD_ID', '');
			$recordModel = $this->getDuplicate($record, $moduleName);
		} else if (!empty($record)) {
			$recordModel = $this->record ? $this->record : Vtiger_Record_Model::getInstanceById($record, $moduleName);
			$viewer->assign('MODE', 'edit');
			$viewer->assign('RECORD_ID', $record);
		} else {
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			$referenceId = $request->get('reference_id');
			if ($referenceId) {
				$parentRecordModel = Vtiger_Record_Model::getInstanceById($referenceId);
				$recordModel->setRecordFieldValues($parentRecordModel);
			}
			$viewer->assign('MODE', '');
			$viewer->assign('RECORD_ID', '');
		}
		if (!$this->record) {
			$this->record = $recordModel;
		}

		$editModel = Vtiger_EditView_Model::getInstance($moduleName, $record);
		$editViewLinkParams = ['MODULE' => $moduleName, 'RECORD' => $record];
		$detailViewLinks = $editModel->getEditViewLinks($editViewLinkParams);
		$viewer->assign('EDITVIEW_LINKS', $detailViewLinks);

		$moduleModel = $recordModel->getModule();
		$fieldList = $moduleModel->getFields();
		$requestFieldList = array_intersect_key($request->getAll(), $fieldList);

		foreach ($requestFieldList as $fieldName => $fieldValue) {
			$fieldModel = $fieldList[$fieldName];
			$specialField = false;
			// We collate date and time part together in the EditView UI handling 
			// so a bit of special treatment is required if we come from QuickCreate 
			if ($moduleName == 'Calendar' && empty($record) && $fieldName == 'time_start' && !empty($fieldValue)) {
				$specialField = true;
				// Convert the incoming user-picked time to GMT time 
				// which will get re-translated based on user-time zone on EditForm 
				$fieldValue = DateTimeField::convertToDBTimeZone($fieldValue)->format("H:i");
			}

			if ($moduleName == 'Calendar' && empty($record) && $fieldName == 'date_start' && !empty($fieldValue)) {
				$startTime = Vtiger_Time_UIType::getTimeValueWithSeconds($requestFieldList['time_start']);
				$startDateTime = Vtiger_Datetime_UIType::getDBDateTimeValue($fieldValue . " " . $startTime);
				list($startDate, $startTime) = explode(' ', $startDateTime);
				$fieldValue = Vtiger_Date_UIType::getDisplayDateValue($startDate);
			}
			if ($fieldModel->isEditable() || $specialField) {
				$recordModel->set($fieldName, $fieldModel->getDBValue($fieldValue));
			}
		}
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
		$recordStructure = $recordStructureInstance->getStructure();
		$picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName);

		$isRelationOperation = $request->get('relationOperation');
		//if it is relation edit
		$viewer->assign('IS_RELATION_OPERATION', $isRelationOperation);
		if ($isRelationOperation) {
			$sourceModule = $request->get('sourceModule');
			$sourceRecord = $request->get('sourceRecord');

			$viewer->assign('SOURCE_MODULE', $sourceModule);
			$viewer->assign('SOURCE_RECORD', $sourceRecord);
			$sourceRelatedField = $moduleModel->getValuesFromSource($request);
			foreach ($recordStructure as &$block) {
				foreach ($sourceRelatedField as $field => &$value) {
					if (isset($block[$field])) {
						$fieldvalue = $block[$field]->get('fieldvalue');
						if (empty($fieldvalue)) {
							$block[$field]->set('fieldvalue', $value);
						}
					}
				}
			}
		}
		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE', \App\Json::encode($picklistDependencyDatasource));
		$viewer->assign('MAPPING_RELATED_FIELD', \App\Json::encode(\App\ModuleHierarchy::getRelationFieldByHierarchy($moduleName)));
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('RECORD_STRUCTURE', $recordStructure);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('MODULE_TYPE', $moduleModel->getModuleType());
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('APIADDRESS', Settings_ApiAddress_Module_Model::getInstance('Settings:ApiAddress')->getConfig());
		$viewer->assign('APIADDRESS_ACTIVE', Settings_ApiAddress_Module_Model::isActive());
		$viewer->assign('MAX_UPLOAD_LIMIT_MB', Vtiger_Util_Helper::getMaxUploadSize());
		$viewer->assign('MAX_UPLOAD_LIMIT', vglobal('upload_maxsize'));
		$viewer->view('EditView.tpl', $moduleName);
	}

	public function getDuplicate($record, $moduleName)
	{
		$recordModel = $this->record ? $this->record : Vtiger_Record_Model::getInstanceById($record, $moduleName);
		$recordModel->set('id', '');
		//While Duplicating record, If the related record is deleted then we are removing related record info in record model
		$mandatoryFieldModels = $recordModel->getModule()->getMandatoryFieldModels();
		foreach ($mandatoryFieldModels as $fieldModel) {
			if ($fieldModel->isReferenceField()) {
				$fieldName = $fieldModel->get('name');
				if (!\App\Record::isExists($recordModel->get($fieldName))) {
					$recordModel->set($fieldName, '');
				}
			}
		}
		return $recordModel;
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(Vtiger_Request $request)
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
