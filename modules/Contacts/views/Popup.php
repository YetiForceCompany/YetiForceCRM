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
	/**
	 * {@inheritdoc}
	 */
	public function initializeListViewContents(\App\Request $request, Vtiger_Viewer $viewer)
	{
		$moduleName = $this->getModule($request);
		$sourceModule = $request->getByType('src_module', 2);
		$relParentModule = 'Accounts';
		if (!$request->isEmpty('src_record') && $moduleName === 'Contacts' && \App\Record::isExists($request->getInteger('src_record'))) {
			$sourceRecord = $request->getInteger('src_record');
			//list of records is narrowed to contacts related to help desks account, only in Help Desk Contacts relation view
			if ($sourceModule === 'HelpDesk' && strpos($_SERVER['QUERY_STRING'], 'module=Contacts&src_module=HelpDesk') === 0) {
				$helpDeskRecord = Vtiger_Record_Model::getInstanceById($sourceRecord, 'HelpDesk');
				$relId = $helpDeskRecord->get('parent_id');
				if (\App\Record::getType($relId) === $relParentModule) {
					$request->set('related_parent_module', $relParentModule);
					$request->set('related_parent_id', $relId);
					$request->set('showSwitch', 1);
				}
			}
			if ($sourceModule === 'SSalesProcesses' && strpos($_SERVER['QUERY_STRING'], 'module=Contacts&src_module=SSalesProcesses') === 0) {
				$moduleRecord = Vtiger_Record_Model::getInstanceById($sourceRecord, 'SSalesProcesses');
				$relId = $moduleRecord->get('related_to');
				if (\App\Record::getType($relId) === $relParentModule) {
					$request->set('related_parent_module', $relParentModule);
					$request->set('related_parent_id', $relId);
					$request->set('showSwitch', 1);
				}
			}
			if ($sourceModule === 'Project' && strpos($_SERVER['QUERY_STRING'], 'module=Contacts&src_module=Project') === 0) {
				$moduleRecord = Vtiger_Record_Model::getInstanceById($sourceRecord, 'Project');
				$relId = $moduleRecord->get('linktoaccountscontacts');
				if (\App\Record::getType($relId) === $relParentModule) {
					$request->set('related_parent_module', $relParentModule);
					$request->set('related_parent_id', $relId);
					$request->set('showSwitch', 1);
				}
			}
		}
		parent::initializeListViewContents($request, $viewer);
	}
}
