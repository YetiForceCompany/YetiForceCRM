<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Campaigns_RelatedList_View extends Vtiger_RelatedList_View {
	function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$relatedModuleName = $request->get('relatedModule');
		$parentId = $request->get('record');
		$label = $request->get('tab_label');
		$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $label);
		$relationModel = $relationListView->getRelationModel();

		$viewer = $this->getViewer($request);
		if (array_key_exists($relatedModuleName, $relationModel->getEmailEnabledModulesInfoForDetailView())) {
			$viewer->assign('CUSTOM_VIEWS', CustomView_Record_Model::getAllByGroup($relatedModuleName));
			$viewer->assign('STATUS_VALUES', $relationModel->getCampaignRelationStatusValues());
			$viewer->assign('SELECTED_IDS', $request->get('selectedIds'));
			$viewer->assign('EXCLUDED_IDS', $request->get('excludedIds'));
		}
		return parent::process($request);
	}
}