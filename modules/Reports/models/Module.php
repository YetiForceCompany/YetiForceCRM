<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Reports_Module_Model extends Vtiger_Module_Model {

	/**
	 * Function deletes report
	 * @param Reports_Record_Model $reportModel
	 */
	function deleteRecord($reportModel) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$subOrdinateUsers = $currentUser->getSubordinateUsers();

		$subOrdinates = array();
		foreach($subOrdinateUsers as $id=>$name) {
			$subOrdinates[] = $id;
		}

		$owner = $reportModel->get('owner');

		if($currentUser->isAdminUser() || in_array($owner, $subOrdinates) || $owner == $currentUser->getId()) {
			$reportId = $reportModel->getId();
			$db = PearDatabase::getInstance();

			$db->pquery('DELETE FROM vtiger_selectquery WHERE queryid = ?', array($reportId));

			$db->pquery('DELETE FROM vtiger_report WHERE reportid = ?', array($reportId));

			$db->pquery('DELETE FROM vtiger_schedulereports WHERE reportid = ?', array($reportId));

            $db->pquery('DELETE FROM vtiger_reporttype WHERE reportid = ?', array($reportId));

			$result = $db->pquery('SELECT * FROM vtiger_homereportchart WHERE reportid = ?',array($reportId));
			$numOfRows = $db->num_rows($result);
			for ($i = 0; $i < $numOfRows; $i++) {
				$homePageChartIdsList[] = $adb->query_result($result, $i, 'stuffid');
			}
			if ($homePageChartIdsList) {
				$deleteQuery = 'DELETE FROM vtiger_homestuff WHERE stuffid IN (' . implode(",", $homePageChartIdsList) . ')';
				$db->pquery($deleteQuery, array());
			}
			return true;
		}
		return false;
	}

	/**
	 * Function returns quick links for the module
	 * @return <Array of Vtiger_Link_Model>
	 */
	function getSideBarLinks($linkParams = '') {
		$quickLinks = array(
			array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_REPORTS',
				'linkurl' => $this->getListViewUrl(),
				'linkicon' => '',
			),
		);
		foreach($quickLinks as $quickLink) {
			$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
		}

		$quickWidgets = array(
			array(
				'linktype' => 'SIDEBARWIDGET',
				'linklabel' => 'LBL_RECENTLY_MODIFIED',
				'linkurl' => 'module='.$this->get('name').'&view=IndexAjax&mode=showActiveRecords',
				'linkicon' => ''
			),
		);
		foreach($quickWidgets as $quickWidget) {
			$links['SIDEBARWIDGET'][] = Vtiger_Link_Model::getInstanceFromValues($quickWidget);
		}

		return $links;
	}

	/**
	 * Function returns the recent created reports
	 * @param <Number> $limit
	 * @return <Array of Reports_Record_Model>
	 */
	function getRecentRecords($limit = 10) {
		$db = PearDatabase::getInstance();

		$result = $db->pquery('SELECT * FROM vtiger_report ORDER BY reportid DESC LIMIT ?', array($limit));
		$rows = $db->num_rows($result);

		$recentRecords = array();
		for($i=0; $i<$rows; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			$recentRecords[$row['reportid']] = $this->getRecordFromArray($row);
		}
		return $recentRecords;
	}

	/**
	 * Function returns the report folders
	 * @return <Array of Reports_Folder_Model>
	 */
	function getFolders() {
		return Reports_Folder_Model::getAll();
	}

	/**
	 * Function to get the url for add folder from list view of the module
	 * @return <string> - url
	 */
	function getAddFolderUrl() {
		return 'index.php?module='.$this->get('name').'&view=EditFolder';
	}
    
    /**
     * Function to check if the extension module is permitted for utility action
     * @return <boolean> true
     */
    public function isUtilityActionEnabled() {
        return true;
    }
}