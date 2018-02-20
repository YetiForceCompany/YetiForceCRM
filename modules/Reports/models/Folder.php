<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Reports_Folder_Model extends \App\Base
{
	/**
	 * Function to get the id of the folder.
	 *
	 * @return <Number>
	 */
	public function getId()
	{
		return $this->get('folderid');
	}

	/**
	 * Function to set the if for the folder.
	 *
	 * @param <Number>
	 */
	public function setId($value)
	{
		$this->set('folderid', $value);
	}

	/**
	 * Function to get the name of the folder.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->get('foldername');
	}

	/**
	 * Function returns the instance of Folder model.
	 *
	 * @return <Reports_Folder_Model>
	 */
	public static function getInstance()
	{
		return new self();
	}

	/**
	 * Function saves the folder.
	 */
	public function save()
	{
		$db = PearDatabase::getInstance();

		$folderId = $this->getId();
		if (!empty($folderId)) {
			$db->pquery('UPDATE vtiger_reportfolder SET foldername = ?, description = ? WHERE folderid = ?', [$this->getName(), $this->getDescription(), $folderId]);
		} else {
			$result = $db->pquery('SELECT MAX(folderid) AS folderid FROM vtiger_reportfolder', []);
			$folderId = (int) ($db->queryResult($result, 0, 'folderid')) + 1;

			$db->pquery('INSERT INTO vtiger_reportfolder(folderid, foldername, description, state) VALUES(?, ?, ?, ?)', [$folderId, $this->getName(), $this->getDescription(), 'CUSTOMIZED']);
			$this->set('folderid', $folderId);
		}
	}

	/**
	 * Function deletes the folder.
	 */
	public function delete()
	{
		$db = PearDatabase::getInstance();
		$db->pquery('DELETE FROM vtiger_reportfolder WHERE folderid = ?', [$this->getId()]);
	}

	/**
	 * Function returns Report Models for the folder.
	 *
	 * @param <Vtiger_Paging_Model> $pagingModel
	 *
	 * @return <Reports_Record_Model>
	 */
	public function getReports($pagingModel)
	{
		$paramsList = [
			'startIndex' => $pagingModel->getStartIndex(),
			'pageLimit' => $pagingModel->getPageLimit(),
			'orderBy' => $this->get('orderby'),
			'sortBy' => $this->get('sortby'), ];

		$reportClassInstance = Vtiger_Module_Model::getClassInstance('Reports');

		$fldrId = $this->getId();
		if ($fldrId == 'All') {
			$fldrId = false;
			$paramsList = ['startIndex' => $pagingModel->getStartIndex(),
				'pageLimit' => $pagingModel->getPageLimit(),
				'orderBy' => $this->get('orderby'),
				'sortBy' => $this->get('sortby'),
			];
		}

		$reportsList = $reportClassInstance->sgetRptsforFldr($fldrId, $paramsList);
		if (!$fldrId) {
			foreach ($reportsList as $reportId => $reports) {
				$reportsCount += count($reports);
			}
		} else {
			$reportsCount = count($reportsList);
		}

		$pageLimit = $pagingModel->getPageLimit();
		if ($reportsCount > $pageLimit) {
			if (!$fldrId) {
				$lastKey = end(array_keys($reportsList));
				array_pop($reportsList[$lastKey]);
			} else {
				array_pop($reportsList);
			}
			$pagingModel->set('nextPageExists', true);
		} else {
			$pagingModel->set('nextPageExists', false);
		}

		$reportModuleModel = Vtiger_Module_Model::getInstance('Reports');

		if ($fldrId === false) {
			return $this->getAllReportModels($reportsList, $reportModuleModel);
		} else {
			$reportModels = [];
			$countReportsList = count($reportsList);
			for ($i = 0; $i < $countReportsList; ++$i) {
				$reportModel = new Reports_Record_Model();

				$reportModel->setData($reportsList[$i])->setModuleFromInstance($reportModuleModel);
				$reportModels[] = $reportModel;
				unset($reportModel);
			}

			return $reportModels;
		}
	}

	/**
	 * Function to get the description of the folder.
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return $this->get('description');
	}

	/**
	 * Function to get the url for edit folder from list view of the module.
	 *
	 * @return string - url
	 */
	public function getEditUrl()
	{
		return 'index.php?module=Reports&view=EditFolder&folderid=' . $this->getId();
	}

	/**
	 * Function to get the url for delete folder from list view of the module.
	 *
	 * @return string - url
	 */
	public function getDeleteUrl()
	{
		return 'index.php?module=Reports&action=Folder&mode=delete&folderid=' . $this->getId();
	}

	/**
	 * Function returns the instance of Folder model.
	 *
	 * @param FolderId
	 *
	 * @return <Reports_Folder_Model>
	 */
	public static function getInstanceById($folderId)
	{
		$folderModel = Vtiger_Cache::get('reportsFolder', $folderId);
		if (!$folderModel) {
			$db = PearDatabase::getInstance();
			$folderModel = self::getInstance();

			$result = $db->pquery('SELECT * FROM vtiger_reportfolder WHERE folderid = ?', [$folderId]);

			if ($db->numRows($result) > 0) {
				$values = $db->queryResultRowData($result, 0);
				$folderModel->setData($values);
			}
			Vtiger_Cache::set('reportsFolder', $folderId, $folderModel);
		}

		return $folderModel;
	}

	/**
	 * Function returns the instance of Folder model.
	 *
	 * @return <Reports_Folder_Model>
	 */
	public static function getAll()
	{
		$db = PearDatabase::getInstance();
		$folders = Vtiger_Cache::get('reports', 'folders');
		if (!$folders) {
			$folders = [];
			$result = $db->pquery('SELECT * FROM vtiger_reportfolder ORDER BY foldername ASC', []);
			$noOfFolders = $db->numRows($result);
			if ($noOfFolders > 0) {
				for ($i = 0; $i < $noOfFolders; ++$i) {
					$folderModel = self::getInstance();
					$values = $db->queryResultRowData($result, $i);
					$folders[$values['folderid']] = $folderModel->setData($values);
					Vtiger_Cache::set('reportsFolder', $values['folderid'], $folderModel);
				}
			}
			Vtiger_Cache::set('reports', 'folders', $folders);
		}

		return $folders;
	}

	/**
	 * Function returns duplicate record status of the module.
	 *
	 * @return true if duplicate records exists else false
	 */
	public function checkDuplicate()
	{
		$db = PearDatabase::getInstance();

		$query = 'SELECT 1 FROM vtiger_reportfolder WHERE foldername = ?';
		$params = [$this->getName()];

		$folderId = $this->getId();
		if ($folderId) {
			$query .= ' && folderid != ?';
			array_push($params, $folderId);
		}

		$result = $db->pquery($query, $params);

		if ($db->numRows($result) > 0) {
			return true;
		}

		return false;
	}

	/**
	 * Function returns whether reports are exist or not in this folder.
	 *
	 * @return true if exists else false
	 */
	public function hasReports()
	{
		$db = PearDatabase::getInstance();

		$result = $db->pquery('SELECT 1 FROM vtiger_report WHERE folderid = ?', [$this->getId()]);

		if ($db->numRows($result) > 0) {
			return true;
		}

		return false;
	}

	/**
	 * Function returns whether folder is Default or not.
	 *
	 * @return true if it is read only else false
	 */
	public function isDefault()
	{
		if ($this->get('state') == 'SAVED') {
			return true;
		}

		return false;
	}

	/**
	 * Function to get info array while saving a folder.
	 *
	 * @return array info array
	 */
	public function getInfoArray()
	{
		return ['folderId' => $this->getId(),
			'folderName' => $this->getName(),
			'editURL' => $this->getEditUrl(),
			'deleteURL' => $this->getDeleteUrl(),
			'isEditable' => $this->isEditable(),
			'isDeletable' => $this->isDeletable(), ];
	}

	/**
	 * Function to check whether folder is editable or not.
	 *
	 * @return <boolean>
	 */
	public function isEditable()
	{
		if ($this->isDefault()) {
			return false;
		}

		return true;
	}

	/**
	 * Function to get check whether folder is deletable or not.
	 *
	 * @return <boolean>
	 */
	public function isDeletable()
	{
		if ($this->isDefault()) {
			return false;
		}

		return true;
	}

	/**
	 * Function to calculate number of reports in this folder.
	 *
	 * @return <Integer>
	 */
	public function getReportsCount()
	{
		$db = PearDatabase::getInstance();
		$params = [];
		$query = 'SELECT reportmodulesid, primarymodule from vtiger_reportmodules';
		$result = $db->pquery($query, []);
		$noOfRows = $db->numRows($result);
		$allowedReportIds = [];
		for ($i = 0; $i < $noOfRows; ++$i) {
			$primaryModule = $db->queryResult($result, $i, 'primarymodule');
			$reportid = $db->queryResult($result, $i, 'reportmodulesid');
			if (\App\Privilege::isPermitted($primaryModule, 'index')) {
				$allowedReportIds[] = $reportid;
			}
		}
		if (!empty($allowedReportIds)) {
			$sql = sprintf('SELECT count(*) AS count FROM vtiger_report
					INNER JOIN vtiger_reportfolder ON vtiger_reportfolder.folderid = vtiger_report.folderid &&
					vtiger_report.reportid in (%s)', implode(',', $allowedReportIds));
			$fldrId = $this->getId();
			if ($fldrId == 'All') {
				$fldrId = false;
			}

			// If information is required only for specific report folder?
			if ($fldrId !== false) {
				$sql .= ' WHERE vtiger_reportfolder.folderid=?';
				array_push($params, $fldrId);
			}
			$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
			if (!$currentUserModel->isAdminUser()) {
				$currentUserId = $currentUserModel->getId();

				$groupId = implode(',', $currentUserModel->get('groups'));
				if ($groupId) {
					$groupQuery = "(SELECT reportid from vtiger_reportsharing WHERE shareid IN ($groupId) && setype = 'groups') || ";
				}

				$sql .= " && (vtiger_report.reportid IN (SELECT reportid from vtiger_reportsharing WHERE $groupQuery shareid = ? && setype = 'users')
							OR vtiger_report.sharingtype = 'Public'
							OR vtiger_report.owner = ?
							OR vtiger_report.owner IN (SELECT vtiger_user2role.userid FROM vtiger_user2role
														INNER JOIN vtiger_users ON vtiger_users.id = vtiger_user2role.userid
														INNER JOIN vtiger_role ON vtiger_role.roleid = vtiger_user2role.roleid
														WHERE vtiger_role.parentrole LIKE ?))";

				$parentRoleSeq = $currentUserModel->get('parent_role_seq') . '::%';
				array_push($params, $currentUserId, $currentUserId, $parentRoleSeq);
			}
			$result = $db->pquery($sql, $params);

			return $db->queryResult($result, 0, 'count');
		}

		return 0;
	}

	/**
	 * Function to get all Report Record Models.
	 *
	 * @param <Array> $allReportsList
	 * @param Vtiger_Module_Model - Reports Module Model
	 *
	 * @return <Array> Reports Record Models
	 */
	public function getAllReportModels($allReportsList, $reportModuleModel)
	{
		$allReportModels = [];
		$folders = self::getAll();
		foreach ($allReportsList as $key => $reportsList) {
			$countReportsList = count($reportsList);
			for ($i = 0; $i < $countReportsList; ++$i) {
				$reportModel = new Reports_Record_Model();
				$reportModel->setData($reportsList[$i])->setModuleFromInstance($reportModuleModel);
				$reportModel->set('foldername', $folders[$key]->getName());
				$allReportModels[] = $reportModel;
				unset($reportModel);
			}
		}

		return $allReportModels;
	}

	/**
	 * Function which provides the records for the current view.
	 *
	 * @param bool $skipRecords - List of the RecordIds to be skipped
	 *
	 * @return int[] List of RecordsIds
	 */
	public function getRecordIds($skipRecords = false)
	{
		$folderId = $this->getId();
		$query = (new App\Db\Query())->select(['vtiger_report.reportid'])
			->from('vtiger_report')
			->innerJoin('vtiger_reportfolder', 'vtiger_reportfolder.folderid = vtiger_report.folderid')
			->innerJoin('vtiger_reportmodules', 'vtiger_reportmodules.reportmodulesid = vtiger_report.reportid');
		if ($folderId !== 'All') {
			$query->where(['vtiger_reportfolder.folderid' => $folderId]);
		}
		if ($skipRecords && !empty($skipRecords) && is_array($skipRecords) && count($skipRecords) > 0) {
			$query->andWhere(['not in', 'vtiger_report.reportid', $skipRecords]);
		}

		return $query->column();
	}
}
