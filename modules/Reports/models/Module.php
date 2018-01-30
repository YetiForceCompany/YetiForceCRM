<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Reports_Module_Model extends Vtiger_Module_Model
{

	/**
	 * Function deletes report
	 * @param Reports_Record_Model $reportModel
	 */
	public function deleteRecord($reportModel)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$subOrdinateUsers = $currentUser->getSubordinateUsers();

		$subOrdinates = [];
		foreach ($subOrdinateUsers as $id => $name) {
			$subOrdinates[] = $id;
		}

		$owner = $reportModel->get('owner');

		if ($currentUser->isAdminUser() || in_array($owner, $subOrdinates) || $owner == $currentUser->getId()) {
			$reportId = $reportModel->getId();
			$db = PearDatabase::getInstance();
			$db->pquery('DELETE FROM vtiger_selectquery WHERE queryid = ?', [$reportId]);
			$db->pquery('DELETE FROM vtiger_report WHERE reportid = ?', [$reportId]);
			$db->pquery('DELETE FROM vtiger_schedulereports WHERE reportid = ?', [$reportId]);
			$db->pquery('DELETE FROM vtiger_reporttype WHERE reportid = ?', [$reportId]);
			return true;
		}
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSideBarLinks($linkParams = '')
	{
		$links = [];
		$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_REPORTS',
				'linkurl' => $this->getListViewUrl(),
				'linkicon' => 'glyphicon glyphicon-list',
		]);
		return $links;
	}

	/**
	 * Function returns the report folders
	 * @return <Array of Reports_Folder_Model>
	 */
	public function getFolders()
	{
		return Reports_Folder_Model::getAll();
	}

	/**
	 * Function to get the url for add folder from list view of the module
	 * @return string - url
	 */
	public function getAddFolderUrl()
	{
		return 'index.php?module=' . $this->get('name') . '&view=EditFolder';
	}

	/**
	 * Function to check if the extension module is permitted for utility action
	 * @return <boolean> true
	 */
	public function isUtilityActionEnabled()
	{
		return true;
	}
}
