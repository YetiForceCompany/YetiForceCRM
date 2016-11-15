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

Class CustomView_EditAjax_View extends Vtiger_IndexAjax_View
{

	public function process(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->get('source_module');
		$module = $request->getModule();
		$record = $request->get('record');
		$duplicate = $request->get('duplicate');

		if (is_numeric($moduleName)) {
			$moduleName = vtlib\Functions::getModuleName($moduleName);
		}
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);

		if (!empty($record)) {
			$customViewModel = CustomView_Record_Model::getInstanceById($record);
			$viewer->assign('MODE', 'edit');
		} else {
			$customViewModel = new CustomView_Record_Model();
			$customViewModel->setModule($moduleName);
			$viewer->assign('MODE', '');
		}

		$viewer->assign('ADVANCE_CRITERIA', $customViewModel->transformToNewAdvancedFilter());
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('DATE_FILTERS', Vtiger_AdvancedFilter_Helper::getDateFilter($module));

		if ($moduleName == 'Calendar') {
			$advanceFilterOpsByFieldType = Calendar_Field_Model::getAdvancedFilterOpsByFieldType();
		} else {
			$advanceFilterOpsByFieldType = Vtiger_Field_Model::getAdvancedFilterOpsByFieldType();
		}
		$viewer->assign('ADVANCED_FILTER_OPTIONS', \App\CustomView::ADVANCED_FILTER_OPTIONS);
		$viewer->assign('ADVANCED_FILTER_OPTIONS_BY_TYPE', $advanceFilterOpsByFieldType);
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$recordStructure = $recordStructureInstance->getStructure();
		// for Inventory module we should now allow item details block
		if (in_array($moduleName, getInventoryModules())) {
			$itemsBlock = "LBL_ITEM_DETAILS";
			unset($recordStructure[$itemsBlock]);
		}
		$viewer->assign('RECORD_STRUCTURE', $recordStructure);
		// Added to show event module custom fields
		if ($moduleName == 'Calendar') {
			$relatedModuleName = 'Events';
			$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModuleName);
			$relatedRecordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($relatedModuleModel);
			$eventBlocksFields = $relatedRecordStructureInstance->getStructure();
			$viewer->assign('EVENT_RECORD_STRUCTURE_MODEL', $relatedRecordStructureInstance);
			$viewer->assign('EVENT_RECORD_STRUCTURE', $eventBlocksFields);
		}
		$viewer->assign('CUSTOMVIEW_MODEL', $customViewModel);
		if ($duplicate != '1') {
			$viewer->assign('RECORD_ID', $record);
		}
		$viewer->assign('MODULE', $module);
		$viewer->assign('SOURCE_MODULE', $moduleName);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		if ($customViewModel->get('viewname') == 'All') {
			$viewer->assign('CV_PRIVATE_VALUE', App\CustomView::CV_STATUS_DEFAULT);
		} else {
			$viewer->assign('CV_PRIVATE_VALUE', App\CustomView::CV_STATUS_PRIVATE);
		}
		$viewer->assign('CV_PENDING_VALUE', App\CustomView::CV_STATUS_PENDING);
		$viewer->assign('CV_PUBLIC_VALUE', App\CustomView::CV_STATUS_PUBLIC);
		$viewer->assign('MODULE_MODEL', $moduleModel);

		echo $viewer->view('EditView.tpl', $module, true);
	}
}
