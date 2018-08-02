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

class Calendar_Edit_View extends Vtiger_Edit_View
{
	use \App\Controller\ExposeMethod;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('events');
		$this->exposeMethod('calendar');
	}

	/**
	 * Process request.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$mode = $request->getMode();
		if ($request->has('record')) {
			$recordModel = $this->record ? $this->record : Vtiger_Record_Model::getInstanceById($request->getInteger('record'));
			$mode = strtolower($recordModel->getType());
		}
		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);

			return;
		}
		$this->calendar($request, 'Calendar');
	}

	public function events(\App\Request $request)
	{
		$moduleName = 'Events';
		$viewer = $this->getViewer($request);
		if ($request->has('record') && $request->getBoolean('isDuplicate') === true) {
			$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $moduleName);
			$viewer->assign('MODE', 'duplicate');
		} elseif ($request->has('record')) {
			$recordModel = Vtiger_Record_Model::getInstanceById($request->getInteger('record'), $moduleName);
			$viewer->assign('MODE', 'edit');
			$viewer->assign('RECORD_ID', $request->getInteger('record'));
		} else {
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			$viewer->assign('MODE', '');
		}
		$eventModule = Vtiger_Module_Model::getInstance($moduleName);
		$recordModel->setModuleFromInstance($eventModule);

		$moduleModel = $recordModel->getModule();
		$fieldList = $moduleModel->getFields();
		$requestFieldList = array_intersect($request->getKeys(), array_keys($fieldList));
		foreach ($requestFieldList as $fieldName) {
			$fieldModel = $fieldList[$fieldName];
			if ($fieldModel->isWritable()) {
				$fieldModel->getUITypeModel()->setValueFromRequest($request, $recordModel);
			}
		}
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
		$recordStructure = $recordStructureInstance->getStructure();
		$userChangedEndDateTime = $request->get('userChangedEndDateTime');
		$isRelationOperation = $request->getBoolean('relationOperation');
		//if it is relation edit
		$viewer->assign('IS_RELATION_OPERATION', $isRelationOperation);
		if ($isRelationOperation) {
			$sourceModule = $request->getByType('sourceModule', 2);
			$sourceRecord = $request->getInteger('sourceRecord');

			$viewer->assign('SOURCE_MODULE', $sourceModule);
			$viewer->assign('SOURCE_RECORD', $sourceRecord);
			$sourceRelatedField = $moduleModel->getValuesFromSource($request, $moduleName);
			foreach ($recordStructure as &$block) {
				foreach ($sourceRelatedField as $field => $value) {
					if (isset($block[$field])) {
						$fieldvalue = $block[$field]->get('fieldvalue');
						if (empty($fieldvalue)) {
							$block[$field]->set('fieldvalue', $value);
						}
					}
				}
			}
		}
		$viewer->assign('USER_CHANGED_END_DATE_TIME', $userChangedEndDateTime);
		$viewer->assign('TOMORROWDATE', App\Fields\Date::formatToDisplay(date('Y-m-d', time() + 86400)));
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('RECORD_STRUCTURE', $recordStructure);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE', \App\Json::encode(\App\Fields\Picklist::getPicklistDependencyDatasource($moduleName)));
		$viewer->assign('MAPPING_RELATED_FIELD', \App\Json::encode(\App\ModuleHierarchy::getRelationFieldByHierarchy($moduleName)));
		$viewer->assign('INVITIES_SELECTED', $recordModel->getInvities());
		$viewer->view('EditView.tpl', $moduleName);
	}

	/**
	 * Calendar.
	 *
	 * @param \App\Request $request
	 */
	public function calendar(\App\Request $request)
	{
		parent::process($request);
	}
}
