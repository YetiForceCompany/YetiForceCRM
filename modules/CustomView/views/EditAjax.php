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

class CustomView_EditAjax_View extends Vtiger_IndexAjax_View
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(\App\Request $request)
	{
		if (\App\User::getCurrentUserModel()->isAdmin()) {
			return;
		}
		if (!$request->getBoolean('duplicate') && !$request->isEmpty('record') && !CustomView_Record_Model::getInstanceById($request->getInteger('record'))->isEditable()) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$sourceModuleName = $request->getByType('source_module', 2);
		$moduleName = $request->getModule();
		$record = $request->getInteger('record');
		if (is_numeric($sourceModuleName)) {
			$sourceModuleName = \App\Module::getModuleName($sourceModuleName);
		}
		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModuleName);
		$recordStructureModulesField = [];
		foreach ($sourceModuleModel->getFieldsByReference() as $referenceField) {
			foreach ($referenceField->getReferenceList() as $relatedModuleName) {
				$recordStructureModulesField[$relatedModuleName][$referenceField->getFieldName()] = Vtiger_RecordStructure_Model::getInstanceForModule(Vtiger_Module_Model::getInstance($relatedModuleName))->getStructure();
			}
		}
		if (!empty($record)) {
			$customViewModel = CustomView_Record_Model::getInstanceById($record);
			$viewer->assign('MODE', 'edit');
		} else {
			$customViewModel = new CustomView_Record_Model();
			$customViewModel->setModule($sourceModuleName);
			$viewer->assign('MODE', '');
		}
		$viewer->assign('ADVANCE_CRITERIA', $customViewModel->getConditions());
		$viewer->assign('DUPLICATE_FIELDS', $customViewModel->getDuplicateFields());
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('RECORD_STRUCTURE_RELATED_MODULES', $recordStructureModulesField);
		$viewer->assign('RECORD_STRUCTURE', Vtiger_RecordStructure_Model::getInstanceForModule($sourceModuleModel)->getStructure());
		$viewer->assign('CUSTOMVIEW_MODEL', $customViewModel);
		if (!$request->getBoolean('duplicate')) {
			$viewer->assign('RECORD_ID', $record);
		}
		$viewer->assign('QUALIFIED_MODULE', $sourceModuleName);
		$viewer->assign('SOURCE_MODULE', $sourceModuleName);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		if ($customViewModel->get('viewname') === 'All') {
			$viewer->assign('CV_PRIVATE_VALUE', App\CustomView::CV_STATUS_DEFAULT);
		} else {
			$viewer->assign('CV_PRIVATE_VALUE', App\CustomView::CV_STATUS_PRIVATE);
		}
		$viewer->assign('CV_PENDING_VALUE', App\CustomView::CV_STATUS_PENDING);
		$viewer->assign('CV_PUBLIC_VALUE', App\CustomView::CV_STATUS_PUBLIC);
		$viewer->assign('MODULE_MODEL', $sourceModuleModel);
		$viewer->view('EditView.tpl', $moduleName);
	}
}
