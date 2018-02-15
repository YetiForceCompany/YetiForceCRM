<?php

/**
 * Popup View Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class OSSPasswords_Popup_View extends Vtiger_Popup_View
{
	/*
	 * Function to initialize the required data in smarty to display the List View Contents
	 * @param \App\Request $request
	 * @param Vtiger_Viewer $viewer
	 */

	public function initializeListViewContents(\App\Request $request, Vtiger_Viewer $viewer)
	{
		$moduleName = $this->getModule($request);
		$sourceModule = $request->getByType('src_module', 2);
		$sourceRecord = $request->getInteger('src_record');

		$showFilter = (in_array($sourceModule, ['HelpDesk', 'Project', 'SSalesProcesses']) && $moduleName == 'OSSPasswords') ? true : false;
		//list of records is narrowed to contacts related to help desks account, only in Help Desk Contacts relation view
		if ($showFilter && \App\Record::isExists($sourceRecord) && strpos($_SERVER['QUERY_STRING'], "module=$moduleName&src_module=$sourceModule") === 0) {
			$filterField = ['HelpDesk' => 'parent_id', 'Project' => 'linktoaccountscontacts', 'OSSPasswords' => 'related_to'];
			$relParentModule = 'Accounts';
			$record = Vtiger_Record_Model::getInstanceById($sourceRecord, $sourceModule);
			$relId = $record->get($filterField[$sourceModule]);
			if (\App\Record::getType($relId) === $relParentModule) {
				$request->set('related_parent_module', $relParentModule);
				$request->set('related_parent_id', $relId);
				$request->set('showSwitch', 1);
			}
		}
		parent::initializeListViewContents($request, $viewer);
		if (array_key_exists('password', $this->listViewHeaders)) {
			foreach ($this->listViewEntries as $recordId => &$recordInstance) {
				$recordInstance->set('password', str_repeat('*', 10));
			}
			$viewer->assign('LISTVIEW_ENTRIES', $this->listViewEntries);
		}
	}
}
