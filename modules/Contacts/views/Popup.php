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

class Contacts_Popup_View extends Vtiger_Popup_View
{
	/*
	 * Function to initialize the required data in smarty to display the List View Contents
	 */

	public function initializeListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer)
	{
		$moduleName = $this->getModule($request);
		$sourceModule = $request->get('src_module');
		$sourceRecord = $request->get('src_record');
		$relParentModule = 'Accounts';
		//list of records is narrowed to contacts related to help desks account, only in Help Desk Contacts relation view
		if ($moduleName == 'Contacts' && $sourceModule == 'HelpDesk' && isRecordExists($sourceRecord) && strpos($_SERVER['QUERY_STRING'], 'module=Contacts&src_module=HelpDesk') === 0) {
			$request->set('related_parent_module', $relParentModule);
			$helpDeskRecord = Vtiger_Record_Model::getInstanceById($sourceRecord, 'HelpDesk');
			$request->set('related_parent_id', $helpDeskRecord->get('parent_id'));
			$viewer->assign('SWITCH', true);
			$viewer->assign('POPUP_SWITCH_ON_TEXT', vtranslate('SINGLE_' . $relParentModule, $relParentModule));
		}
		if ($moduleName == 'Contacts' && $sourceModule == 'SSalesProcesses' && isRecordExists($sourceRecord) && strpos($_SERVER['QUERY_STRING'], 'module=Contacts&src_module=SSalesProcesses') === 0) {
			$request->set('related_parent_module', $relParentModule);
			$moduleRecord = Vtiger_Record_Model::getInstanceById($sourceRecord, 'SSalesProcesses');
			$request->set('related_parent_id', $moduleRecord->get('related_to'));
			$viewer->assign('SWITCH', true);
			$viewer->assign('POPUP_SWITCH_ON_TEXT', vtranslate('SINGLE_' . $relParentModule, $relParentModule));
		}
		if ($moduleName == 'Contacts' && $sourceModule == 'Project' && isRecordExists($sourceRecord) && strpos($_SERVER['QUERY_STRING'], 'module=Contacts&src_module=Project') === 0) {
			$request->set('related_parent_module', $relParentModule);
			$moduleRecord = Vtiger_Record_Model::getInstanceById($sourceRecord, 'Project');
			$request->set('related_parent_id', $moduleRecord->get('linktoaccountscontacts'));
			$viewer->assign('SWITCH', true);
			$viewer->assign('POPUP_SWITCH_ON_TEXT', vtranslate('SINGLE_' . $relParentModule, $relParentModule));
		}

		parent::initializeListViewContents($request, $viewer);
	}
}
