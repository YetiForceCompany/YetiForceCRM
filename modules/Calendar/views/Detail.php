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

class Calendar_Detail_View extends Vtiger_Detail_View
{

	public function preProcess(Vtiger_Request $request, $display = true)
	{
		parent::preProcess($request, false);

		$recordId = $request->get('record');
		$moduleName = $request->getModule();
		if (!empty($recordId)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
			$activityType = $recordModel->getType();
			if ($activityType == 'Events')
				$moduleName = 'Events';
		}
		if (!$this->record) {
			$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		}
		$recordModel = $this->record->getRecord();
		$recordStrucure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
		$summaryInfo = array();
		// Take first block information as summary information
		$stucturedValues = $recordStrucure->getStructure();
		foreach ($stucturedValues as $blockLabel => $fieldList) {
			$summaryInfo[$blockLabel] = $fieldList;
			break;
		}

		$detailViewLinkParams = array('MODULE' => $moduleName, 'RECORD' => $recordId);
		$detailViewLinks = $this->record->getDetailViewLinks($detailViewLinkParams);
		$navigationInfo = false; //ListViewSession::getListViewNavigation($recordId);

		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('NAVIGATION', $navigationInfo);
		$viewer->assign('NO_PAGINATION', true);
		//Intially make the prev and next records as null
		$prevRecordId = null;
		$nextRecordId = null;
		$found = false;
		if ($navigationInfo) {
			foreach ($navigationInfo as $page => $pageInfo) {
				foreach ($pageInfo as $index => $record) {
					//If record found then next record in the interation
					//will be next record
					if ($found) {
						$nextRecordId = $record;
						break;
					}
					if ($record == $recordId) {
						$found = true;
					}
					//If record not found then we are assiging previousRecordId
					//assuming next record will get matched
					if (!$found) {
						$prevRecordId = $record;
					}
				}
				//if record is found and next record is not calculated we need to perform iteration
				if ($found && !empty($nextRecordId)) {
					break;
				}
			}
		}

		$moduleModel = $this->record->getModule();
		if (!empty($prevRecordId)) {
			$viewer->assign('PREVIOUS_RECORD_URL', $moduleModel->getDetailViewUrl($prevRecordId));
		}
		if (!empty($nextRecordId)) {
			$viewer->assign('NEXT_RECORD_URL', $moduleModel->getDetailViewUrl($nextRecordId));
		}

		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);

		$viewer->assign('IS_EDITABLE', $this->record->getRecord()->isEditable($moduleName));
		$viewer->assign('IS_DELETABLE', $this->record->getRecord()->isDeletable($moduleName));

		$linkParams = array('MODULE' => $moduleName, 'ACTION' => $request->get('view'));
		$linkModels = $this->record->getSideBarLinks($linkParams);

		$viewer->assign('QUICK_LINKS', $linkModels);
		$viewer->assign('NO_SUMMARY', true);

		if ($display) {
			$this->preProcessDisplay($request);
		}
	}

	/**
	 * Function shows the entire detail for the record
	 * @param Vtiger_Request $request
	 * @return <type>
	 */
	public function showModuleDetailView(Vtiger_Request $request)
	{
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		if (!empty($recordId)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
			$activityType = $recordModel->getType();
			if ($activityType == 'Events')
				$moduleName = 'Events';
		}

		$detailViewModel = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		$recordModel = $detailViewModel->getRecord();
		$recordStrucure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
		$structuredValues = $recordStrucure->getStructure();
		$moduleModel = $recordModel->getModule();

		$viewer = $this->getViewer($request);
		$viewer->assign('VIEW', $request->get('view'));
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('RECORD_STRUCTURE', $structuredValues);
		$viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStrucure);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));

		if ($moduleName == 'Events') {
			$viewer->assign('INVITIES_SELECTED', $recordModel->getInvities());
		}

		return $viewer->view('DetailViewFullContents.tpl', $moduleName, true);
	}

	/**
	 * Function shows basic detail for the record
	 * @param <type> $request
	 */
	public function showModuleBasicView(Vtiger_Request $request)
	{
		return $this->showModuleDetailView($request);
	}
}
