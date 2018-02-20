<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */
Vtiger_Loader::includeOnce('~modules/Reports/Reports.php');

class Vtiger_Report_Model extends Reports
{
	public static function getInstance($reportId = '')
	{
		$self = new self();

		return $self->reports($reportId);
	}

	public function reports($reportId = '')
	{
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userId = $currentUser->getId();

		$this->initListOfModules();

		if ($reportId != '') {
			// Lookup information in cache first
			$cachedInfo = VTCacheUtils::lookupReportInfo($userId, $reportId);
			$subOrdinateUsers = VTCacheUtils::lookupReportSubordinateUsers($reportId);

			if ($cachedInfo === false) {
				$ssql = 'SELECT vtiger_reportmodules.*, vtiger_report.* FROM vtiger_report
							INNER JOIN vtiger_reportmodules ON vtiger_report.reportid = vtiger_reportmodules.reportmodulesid
							WHERE vtiger_report.reportid = ?';
				$params = [$reportId];
				require 'user_privileges/user_privileges_' . $userId . '.php';

				$userGroupsList = App\PrivilegeUtil::getAllGroupsByUser($userId);
				if (!empty($userGroupsList) && $currentUser->isAdminUser() === false) {
					$userGroupsQuery = ' (shareid IN (' . $db->generateQuestionMarks($userGroupsList) . ") && setype='groups') OR";
					array_push($params, $userGroupsList);
				}

				$nonAdminQuery = " vtiger_report.reportid IN (SELECT reportid from vtiger_reportsharing
									WHERE $userGroupsQuery (shareid=? && setype='users'))";
				if ($currentUser->isAdminUser() === false) {
					$ssql .= " && (($nonAdminQuery)
								OR vtiger_report.sharingtype = 'Public'
								OR vtiger_report.owner = ? || vtiger_report.owner IN
									(SELECT vtiger_user2role.userid FROM vtiger_user2role
									INNER JOIN vtiger_users ON vtiger_users.id = vtiger_user2role.userid
									INNER JOIN vtiger_role ON vtiger_role.roleid = vtiger_user2role.roleid
									WHERE vtiger_role.parentrole LIKE '$current_user_parent_role_seq::%')
								)";
					array_push($params, $userId, $userId);
				}

				$result = $db->pquery($ssql, $params);

				if ($result && $db->numRows($result)) {
					$reportModulesRow = $db->fetchArray($result);

					// Update information in cache now
					VTCacheUtils::updateReportInfo(
						$userId, $reportId, $reportModulesRow['primarymodule'], $reportModulesRow['secondarymodules'], $reportModulesRow['reporttype'], $reportModulesRow['reportname'], $reportModulesRow['description'], $reportModulesRow['folderid'], $reportModulesRow['owner']
					);
				}

				$subOrdinateUsers = [];

				$subResult = $db->pquery("SELECT userid FROM vtiger_user2role
									INNER JOIN vtiger_users ON vtiger_users.id = vtiger_user2role.userid
									INNER JOIN vtiger_role ON vtiger_role.roleid = vtiger_user2role.roleid
									WHERE vtiger_role.parentrole LIKE '$current_user_parent_role_seq::%'", []);

				$numOfSubRows = $db->numRows($subResult);

				for ($i = 0; $i < $numOfSubRows; ++$i) {
					$subOrdinateUsers[] = $db->queryResult($subResult, $i, 'userid');
				}

				// Update subordinate user information for re-use
				VTCacheUtils::updateReportSubordinateUsers($reportId, $subOrdinateUsers);

				// Re-look at cache to maintain code-consistency below
				$cachedInfo = VTCacheUtils::lookupReportInfo($userId, $reportId);
			}

			if ($cachedInfo) {
				$this->primodule = $cachedInfo['primarymodule'];
				$this->secmodule = $cachedInfo['secondarymodules'];
				$this->reporttype = $cachedInfo['reporttype'];
				$this->reportname = \App\Purifier::decodeHtml($cachedInfo['reportname']);
				$this->reportdescription = \App\Purifier::decodeHtml($cachedInfo['description']);
				$this->folderid = $cachedInfo['folderid'];
				if ($currentUser->isAdminUser() === true || in_array($cachedInfo['owner'], $subOrdinateUsers) || $cachedInfo['owner'] == $userId) {
					$this->is_editable = true;
				} else {
					$this->is_editable = false;
				}
			}
		}

		return $this;
	}

	public function isEditable()
	{
		return $this->is_editable;
	}

	public function getModulesList()
	{
		foreach ($this->module_list as $key => $value) {
			if (\App\Privilege::isPermitted($key, 'index')) {
				$modules[$key] = \App\Language::translate($key, $key);
			}
		}
		asort($modules);

		return $modules;
	}
}
