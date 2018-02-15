<?php
/* * *******************************************************************************
 * * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * ****************************************************************************** */
require_once 'include/database/PearDatabase.php';
require_once 'include/utils/CommonUtils.php';
require_once 'include/fields/DateTimeField.php';
require_once 'include/fields/DateTimeRange.php';
require_once 'include/fields/CurrencyField.php';
require_once 'include/CRMEntity.php';
include_once 'modules/Vtiger/CRMEntity.php';
require_once 'include/runtime/Cache.php';
require_once 'modules/Vtiger/helpers/Util.php';
require_once 'modules/PickList/DependentPickListUtils.php';
require_once 'modules/Users/Users.php';
require_once 'include/Webservices/Utils.php';
require_once 'modules/Reports/ReportUtils.php';

class Reports extends CRMEntity
{
	/**
	 * This class has the informations for Reports and inherits class CRMEntity and
	 * has the variables required to generate,save,restore vtiger_reports
	 * and also the required functions for the same
	 * Contributor(s): ______________________________________..
	 */
	public $srptfldridjs;
	public $column_fields = [];
	public $sort_fields = [];
	public $sort_values = [];
	public $id;
	public $mode;
	public $mcount;
	public $startdate;
	public $enddate;
	public $ascdescorder;
	public $stdselectedfilter;
	public $stdselectedcolumn;
	public $primodule;
	public $secmodule;
	public $columnssummary;
	public $is_editable;
	public $reporttype;
	public $reportname;
	public $reportdescription;
	public $folderid;
	public $module_blocks;
	public $pri_module_columnslist;
	public $sec_module_columnslist;
	public $advft_criteria;
	public $adv_rel_fields = [];
	public $module_list = [];
	public static $oldRelatedModules = [
		'Accounts' => ['Contacts', 'Products'],
		'Contacts' => ['Accounts'],
		'Calendar' => ['Leads', 'Accounts', 'Contacts'],
		'Products' => ['Accounts', 'Contacts'],
		'HelpDesk' => ['Products'],
		'Campaigns' => ['Products'],
	];

	/** Function to set primodule,secmodule,reporttype,reportname,reportdescription,folderid for given vtiger_reportid
	 *  This function accepts the vtiger_reportid as argument
	 *  It sets primodule,secmodule,reporttype,reportname,reportdescription,folderid for the given vtiger_reportid.
	 */
	public function __construct($reportid = '')
	{
		$adb = PearDatabase::getInstance();

		$this->initListOfModules();
		if ($reportid != '') {
			// Lookup information in cache first
			$cachedInfo = VTCacheUtils::lookupReportInfo(\App\User::getCurrentUserId(), $reportid);
			$subordinate_users = VTCacheUtils::lookupReportSubordinateUsers($reportid);

			if ($cachedInfo === false) {
				$ssql = 'select vtiger_reportmodules.*,vtiger_report.* from vtiger_report inner join vtiger_reportmodules on vtiger_report.reportid = vtiger_reportmodules.reportmodulesid';
				$ssql .= ' where vtiger_report.reportid = ?';
				$params = [$reportid];
				require 'user_privileges/user_privileges_' . \App\User::getCurrentUserId() . '.php';
				$userGroups = App\PrivilegeUtil::getAllGroupsByUser(\App\User::getCurrentUserId());
				if (!empty($userGroups) && $is_admin === false) {
					$user_group_query = ' (shareid IN (' . $adb->generateQuestionMarks($userGroups) . ") AND setype='groups') OR";
					array_push($params, $userGroups);
				}

				$non_admin_query = " vtiger_report.reportid IN (SELECT reportid from vtiger_reportsharing WHERE $user_group_query (shareid=? AND setype='users'))";
				if ($is_admin === false) {
					$ssql .= ' and ( (' . $non_admin_query . ") or vtiger_report.sharingtype='Public' or vtiger_report.owner = ? or vtiger_report.owner in(select vtiger_user2role.userid from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like '" . $current_user_parent_role_seq . "::%'))";
					array_push($params, \App\User::getCurrentUserId());
					array_push($params, \App\User::getCurrentUserId());
				}
				$query = $adb->pquery('select userid from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like ?', ["$current_user_parent_role_seq::%"]);
				$subordinate_users = [];
				while (($value = $adb->getSingleValue($query)) !== false) {
					$subordinate_users[] = $value;
				}

				// Update subordinate user information for re-use
				VTCacheUtils::updateReportSubordinateUsers($reportid, $subordinate_users);

				$result = $adb->pquery($ssql, $params);
				if ($result && $adb->numRows($result)) {
					$reportmodulesrow = $adb->fetchArray($result);

					// Update information in cache now
					VTCacheUtils::updateReportInfo(
						\App\User::getCurrentUserId(), $reportid, $reportmodulesrow['primarymodule'], $reportmodulesrow['secondarymodules'], $reportmodulesrow['reporttype'], $reportmodulesrow['reportname'], $reportmodulesrow['description'], $reportmodulesrow['folderid'], $reportmodulesrow['owner']
					);
				}

				// Re-look at cache to maintain code-consistency below
				$cachedInfo = VTCacheUtils::lookupReportInfo(\App\User::getCurrentUserId(), $reportid);
			}

			if ($cachedInfo) {
				$this->primodule = $cachedInfo['primarymodule'];
				$this->secmodule = $cachedInfo['secondarymodules'];
				$this->reporttype = $cachedInfo['reporttype'];
				$this->reportname = App\Purifier::decodeHtml($cachedInfo['reportname']);
				$this->reportdescription = App\Purifier::decodeHtml($cachedInfo['description']);
				$this->folderid = $cachedInfo['folderid'];
				if ($is_admin === true || in_array($cachedInfo['owner'], $subordinate_users) || $cachedInfo['owner'] == \App\User::getCurrentUserId()) {
					$this->is_editable = 'true';
				} else {
					$this->is_editable = 'false';
				}
			} else {
				throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
			}
		}
	}

	// Update the module list for listing columns for report creation.
	public function updateModuleList($module)
	{
		$adb = PearDatabase::getInstance();
		if (!isset($module)) {
			return;
		}

		$tabid = \App\Module::getModuleId($module);
		if ($module == 'Calendar') {
			$tabid = [9, 16];
		}
		$sql = sprintf('SELECT blockid, blocklabel FROM vtiger_blocks WHERE tabid IN (%s)', $adb->generateQuestionMarks($tabid));
		$res = $adb->pquery($sql, [$tabid]);
		$noOfRows = $adb->numRows($res);
		if ($noOfRows <= 0) {
			return;
		}
		for ($index = 0; $index < $noOfRows; ++$index) {
			$blockid = $adb->queryResult($res, $index, 'blockid');
			if (in_array($blockid, $this->module_list[$module])) {
				continue;
			}
			$blocklabel = $adb->queryResult($res, $index, 'blocklabel');
			$this->module_list[$module][$blocklabel] = $blockid;
		}
	}

	// Initializes the module list for listing columns for report creation.
	public function initListOfModules()
	{
		$oldRelatedModules = static::$oldRelatedModules;

		$adb = PearDatabase::getInstance();
		$restricted_modules = ['Events'];
		$restricted_blocks = ['LBL_COMMENTS', 'LBL_COMMENT_INFORMATION'];

		$this->module_id = [];
		$this->module_list = [];

		// Prefetch module info to check active or not and also get list of tabs
		$modulerows = vtlib\Functions::getAllModules(false, true);

		$cachedInfo = VTCacheUtils::lookupReportListOfModuleInfos();

		if ($cachedInfo !== false) {
			$this->module_list = $cachedInfo['module_list'];
			$this->related_modules = $cachedInfo['related_modules'];
		} else {
			if ($modulerows) {
				foreach ($modulerows as $resultrow) {
					if ($resultrow['presence'] == '1') {
						continue;
					}   // skip disabled modules
					if ($resultrow['isentitytype'] != '1') {
						continue;
					}  // skip extension modules
					if (in_array($resultrow['name'], $restricted_modules)) { // skip restricted modules
						continue;
					}
					if ($resultrow['name'] != 'Calendar') {
						$this->module_id[$resultrow['tabid']] = $resultrow['name'];
					} else {
						$this->module_id[9] = $resultrow['name'];
						$this->module_id[16] = $resultrow['name'];
					}
					$this->module_list[$resultrow['name']] = [];
				}

				$moduleids = array_keys($this->module_id);
				$query = sprintf('SELECT blockid, blocklabel, tabid FROM vtiger_blocks WHERE tabid IN (%s)', $adb->generateQuestionMarks($moduleids));
				$reportblocks = $adb->pquery($query, [$moduleids]);
				$prev_block_label = '';
				if ($adb->numRows($reportblocks)) {
					while ($resultrow = $adb->fetchArray($reportblocks)) {
						$blockid = $resultrow['blockid'];
						$blocklabel = $resultrow['blocklabel'];
						$module = $this->module_id[$resultrow['tabid']];

						if (in_array($blocklabel, $restricted_blocks) ||
							in_array($blockid, $this->module_list[$module]) ||
							isset($this->module_list[$module][\App\Language::translate($blocklabel, $module)])
						) {
							continue;
						}

						if (!empty($blocklabel)) {
							if ($module == 'Calendar' && $blocklabel == 'LBL_CUSTOM_INFORMATION') {
								$this->module_list[$module][$blockid] = \App\Language::translate($blocklabel, $module);
							} else {
								$this->module_list[$module][$blockid] = \App\Language::translate($blocklabel, $module);
							}
							$prev_block_label = $blocklabel;
						} else {
							$this->module_list[$module][$blockid] = \App\Language::translate($prev_block_label, $module);
						}
					}
				}
				$query = sprintf("SELECT vtiger_tab.name, vtiger_relatedlists.tabid FROM vtiger_tab
					INNER JOIN vtiger_relatedlists on vtiger_tab.tabid=vtiger_relatedlists.related_tabid
					WHERE vtiger_tab.isentitytype=1
					AND vtiger_tab.name NOT IN(%s)
					AND vtiger_tab.presence = 0 AND vtiger_relatedlists.label!='Activity History'
					UNION
					SELECT relmodule, vtiger_tab.tabid FROM vtiger_fieldmodulerel
					INNER JOIN vtiger_tab on vtiger_tab.name = vtiger_fieldmodulerel.module
					WHERE vtiger_tab.isentitytype = 1
					AND vtiger_tab.name NOT IN(%s)
					AND vtiger_tab.presence = 0", $adb->generateQuestionMarks($restricted_modules), $adb->generateQuestionMarks($restricted_modules));
				$relatedmodules = $adb->pquery($query, [$restricted_modules, $restricted_modules]);
				if ($adb->numRows($relatedmodules)) {
					while ($resultrow = $adb->fetchArray($relatedmodules)) {
						$module = $this->module_id[$resultrow['tabid']];

						if (!isset($this->related_modules[$module])) {
							$this->related_modules[$module] = [];
						}

						if ($module != $resultrow['name']) {
							$this->related_modules[$module][] = $resultrow['name'];
						}

						// To achieve Backward Compatability with Report relations
						if (isset($oldRelatedModules[$module])) {
							$rel_mod = [];
							foreach ($oldRelatedModules[$module] as $key => $name) {
								if (\App\Module::isModuleActive($name) && \App\Privilege::isPermitted($name, 'index', '')) {
									$rel_mod[] = $name;
								}
							}
							if (!empty($rel_mod)) {
								$this->related_modules[$module] = array_merge($this->related_modules[$module], $rel_mod);
								$this->related_modules[$module] = array_unique($this->related_modules[$module]);
							}
						}
					}
				}
				// Put the information in cache for re-use
				VTCacheUtils::updateReportListOfModuleInfos($this->module_list, $this->related_modules);
			}
		}
	}

	// END

	/** Function to get all Reports when in list view
	 *  This function accepts the folderid,paramslist
	 *  This Generates the Reports under each Reports module
	 *  This Returns a HTML sring.
	 */
	public function sgetAllRpt($fldrId, $paramsList)
	{
		$adb = PearDatabase::getInstance();

		$returndata = [];
		$sql = 'select vtiger_report.*, vtiger_reportmodules.*, vtiger_reportfolder.folderid from vtiger_report inner join vtiger_reportfolder on vtiger_reportfolder.folderid = vtiger_report.folderid';
		$sql .= ' inner join vtiger_reportmodules on vtiger_reportmodules.reportmodulesid = vtiger_report.reportid';
		if ($paramsList) {
			$startIndex = $paramsList['startIndex'];
			$pageLimit = $paramsList['pageLimit'];
			$orderBy = $paramsList['orderBy'];
			$sortBy = $paramsList['sortBy'];
			if ($orderBy) {
				$sql .= " ORDER BY $orderBy $sortBy";
			}
			$sql .= ' LIMIT ' . ($pageLimit + 1) . ' OFFSET ' . $startIndex;
		}
		$result = $adb->pquery($sql, $params);
		$report = $adb->fetchArray($result);
		if (count($report) > 0) {
			do {
				$report_details = [];
				$report_details['customizable'] = $report['customizable'];
				$report_details['reportid'] = $report['reportid'];
				$report_details['primarymodule'] = $report['primarymodule'];
				$report_details['secondarymodules'] = $report['secondarymodules'];
				$report_details['state'] = $report['state'];
				$report_details['description'] = $report['description'];
				$report_details['reportname'] = $report['reportname'];
				$report_details['sharingtype'] = $report['sharingtype'];
				$report_details['folderid'] = $report['folderid'];
				if ($is_admin === true) {
					$report_details['editable'] = 'true';
				} else {
					$report_details['editable'] = 'false';
				}

				if (\App\Privilege::isPermitted($report['primarymodule'], 'index')) {
					$returndata[] = $report_details;
				}
			} while ($report = $adb->fetchArray($result));
		}
		\App\Log::trace('Reports :: ListView->Successfully returned vtiger_report details HTML');

		return $returndata;
	}

	/** Function to get the Reports inside each modules
	 *  This function accepts the folderid
	 *  This Generates the Reports under each Reports module
	 *  This Returns a HTML sring.
	 */
	public function sgetRptsforFldr($rpt_fldr_id, $paramsList = false)
	{
		$adb = PearDatabase::getInstance();
		$currentUser = Users_Privileges_Model::getCurrentUserModel();
		$returndata = [];
		$sql = 'select vtiger_report.*, vtiger_reportmodules.*, vtiger_reportfolder.folderid from vtiger_report inner join vtiger_reportfolder on vtiger_reportfolder.folderid = vtiger_report.folderid';
		$sql .= ' inner join vtiger_reportmodules on vtiger_reportmodules.reportmodulesid = vtiger_report.reportid';

		$params = [];
		// If information is required only for specific report folder?
		if ($rpt_fldr_id !== false) {
			$sql .= ' where vtiger_reportfolder.folderid=?';
			$params[] = $rpt_fldr_id;
		}
		require 'user_privileges/user_privileges_' . $currentUser->getId() . '.php';
		$userGroups = App\PrivilegeUtil::getAllGroupsByUser($currentUser->getId());
		if (!empty($userGroups) && $is_admin === false) {
			$user_group_query = ' (shareid IN (' . $adb->generateQuestionMarks($userGroups) . ") AND setype='groups') OR";
			array_push($params, $userGroups);
		}

		$non_admin_query = " vtiger_report.reportid IN (SELECT reportid from vtiger_reportsharing WHERE $user_group_query (shareid=? AND setype='users'))";
		if ($is_admin === false) {
			$sql .= ' and ( (' . $non_admin_query . ") or vtiger_report.sharingtype='Public' or vtiger_report.owner = ? or vtiger_report.owner in(select vtiger_user2role.userid from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like '" . $current_user_parent_role_seq . "::%'))";
			array_push($params, $currentUser->getId());
			array_push($params, $currentUser->getId());
		}
		if ($paramsList) {
			$startIndex = $paramsList['startIndex'];
			$pageLimit = $paramsList['pageLimit'];
			$orderBy = $paramsList['orderBy'];
			$sortBy = $paramsList['sortBy'];
			if ($orderBy) {
				$sql .= " ORDER BY $orderBy $sortBy";
			}
			$sql .= ' LIMIT ' . ($pageLimit + 1) . ' OFFSET ' . $startIndex;
		}
		$query = $adb->pquery('select userid from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like ?', [$current_user_parent_role_seq . '::%']);
		$subordinate_users = [];
		while (($value = $adb->getSingleValue($query)) !== false) {
			$subordinate_users[] = $value;
		}
		$result = $adb->pquery($sql, $params);

		$report = $adb->fetchArray($result);
		$numRows = $adb->getRowCount($result);
		if ($numRows) {
			do {
				$report_details = [];
				$report_details['customizable'] = $report['customizable'];
				$report_details['reportid'] = $report['reportid'];
				$report_details['primarymodule'] = $report['primarymodule'];
				$report_details['secondarymodules'] = $report['secondarymodules'];
				$report_details['state'] = $report['state'];
				$report_details['description'] = $report['description'];
				$report_details['reportname'] = $report['reportname'];
				$report_details['reporttype'] = $report['reporttype'];
				$report_details['sharingtype'] = $report['sharingtype'];
				if ($is_admin === true || in_array($report['owner'], $subordinate_users) || $report['owner'] == $currentUser->getId()) {
					$report_details['editable'] = 'true';
				} else {
					$report_details['editable'] = 'false';
				}

				if (\App\Privilege::isPermitted($report['primarymodule'], 'index')) {
					$returndata[$report['folderid']][] = $report_details;
				}
			} while ($report = $adb->fetchArray($result));
		}

		if ($rpt_fldr_id !== false) {
			$returndata = $returndata[$rpt_fldr_id];
		}

		\App\Log::trace('Reports :: ListView->Successfully returned vtiger_report details HTML');

		return $returndata;
	}

	/** Function to get the array of ids
	 *  This function forms the array for the ExpandCollapse
	 *  Javascript
	 *  It returns the array of ids
	 *  Array('1RptFldr','2RptFldr',........,'9RptFldr','10RptFldr').
	 */
	public function sgetJsRptFldr()
	{
		$srptfldr_js = 'var ReportListArray=new Array(' . $this->srptfldridjs . ')
			setExpandCollapse()';

		return $srptfldr_js;
	}

	/** Function to set the Primary module vtiger_fields for the given Report
	 *  This function sets the primary module columns for the given Report
	 *  It accepts the Primary module as the argument and set the vtiger_fields of the module
	 *  to the varialbe pri_module_columnslist and returns true if sucess.
	 */
	public function getPriModuleColumnsList($module)
	{
		$allColumnsListByBlocks = &$this->getColumnsListbyBlock($module, array_keys($this->module_list[$module]), true);
		foreach ($this->module_list[$module] as $key => $value) {
			$temp = $allColumnsListByBlocks[$key];

			if (!empty($ret_module_list[$module][$value])) {
				if (!empty($temp)) {
					$ret_module_list[$module][$value] = array_merge($ret_module_list[$module][$value], $temp);
				}
			} else {
				$ret_module_list[$module][$value] = $temp;
			}
		}
		$this->pri_module_columnslist = $ret_module_list;

		return true;
	}

	/** Function to set the Secondary module fields for the given Report
	 *  This function sets the secondary module columns for the given module
	 *  It accepts the module as the argument and set the vtiger_fields of the module
	 *  to the varialbe sec_module_columnslist and returns true if sucess.
	 */
	public function getSecModuleColumnsList($module)
	{
		if ($module != '') {
			$secmodule = explode(':', $module);
			$countSecModule = count($secmodule);
			for ($i = 0; $i < $countSecModule; ++$i) {
				if ($this->module_list[$secmodule[$i]]) {
					$this->sec_module_columnslist[$secmodule[$i]] = $this->getModuleFieldList(
						$secmodule[$i]);
					if ($this->module_list[$secmodule[$i]] == 'Calendar') {
						if ($this->module_list['Events']) {
							$this->sec_module_columnslist['Events'] = $this->getModuleFieldList(
								'Events');
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * @param string $module
	 * @param type   $blockIdList
	 * @param array  $currentFieldList
	 *
	 * @return array
	 */
	public function getBlockFieldList($module, $blockIdList, $currentFieldList, $allColumnsListByBlocks)
	{
		$temp = $allColumnsListByBlocks[$blockIdList];
		if (!empty($currentFieldList)) {
			if (!empty($temp)) {
				$currentFieldList = array_merge($currentFieldList, $temp);
			}
		} else {
			$currentFieldList = $temp;
		}

		return $currentFieldList;
	}

	public function getModuleFieldList($module)
	{
		$allColumnsListByBlocks = &$this->getColumnsListbyBlock($module, array_keys($this->module_list[$module]), true);
		foreach ($this->module_list[$module] as $key => $value) {
			$ret_module_list[$module][$value] = $this->getBlockFieldList(
				$module, $key, $ret_module_list[$module][$value], $allColumnsListByBlocks);
		}

		return $ret_module_list[$module];
	}

	/** Function to get vtiger_fields for the given module and block
	 *  This function gets the vtiger_fields for the given module
	 *  It accepts the module and the block as arguments and
	 *  returns the array column lists
	 *  Array module_columnlist[ vtiger_fieldtablename:fieldcolname:module_fieldlabel1:fieldname:fieldtypeofdata]=fieldlabel.
	 */
	public function getColumnsListbyBlock($module, $block, $group_res_by_block = false)
	{
		$adb = PearDatabase::getInstance();
		$currentUser = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		if (is_string($block)) {
			$block = explode(',', $block);
		}
		$skipTalbes = ['vtiger_attachments'];

		$tabid = \App\Module::getModuleId($module);
		if ($module == 'Calendar') {
			$tabid = ['9', '16'];
		}
		$params = [$tabid, $block];

		$profileList = $currentUser->getProfiles();
		$sql = sprintf('select * from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid where vtiger_field.tabid in (%s)  and vtiger_field.block in (%s) and vtiger_field.displaytype in (1,2,3,10) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)', $adb->generateQuestionMarks($tabid), $adb->generateQuestionMarks($block));
		if ($profileList !== false && count($profileList) > 0) {
			$sql .= ' and vtiger_profile2field.profileid in (' . $adb->generateQuestionMarks($profileList) . ')';
			array_push($params, $profileList);
		}
		$sql .= ' and tablename NOT IN (' . $adb->generateQuestionMarks($skipTalbes) . ') ';

		//fix for Ticket #4016
		if ($module == 'Calendar') {
			$sql .= ' group by vtiger_field.fieldlabel order by sequence';
		} else {
			$sql .= ' group by vtiger_field.fieldid order by sequence';
		}
		array_push($params, $skipTalbes);

		$result = $adb->pquery($sql, $params);
		$noofrows = $adb->numRows($result);
		for ($i = 0; $i < $noofrows; ++$i) {
			$fieldtablename = $adb->queryResult($result, $i, 'tablename');
			$fieldcolname = $adb->queryResult($result, $i, 'columnname');
			$fieldname = $adb->queryResult($result, $i, 'fieldname');
			$fieldtype = $adb->queryResult($result, $i, 'typeofdata');
			$uitype = $adb->queryResult($result, $i, 'uitype');
			$fieldtype = explode('~', $fieldtype);
			$fieldtypeofdata = $fieldtype[0];
			$blockid = $adb->queryResult($result, $i, 'block');

			//Here we Changing the displaytype of the field. So that its criteria will be displayed correctly in Reports Advance Filter.
			$fieldtypeofdata = \vtlib\Functions::transformFieldTypeOfData($fieldtablename, $fieldcolname, $fieldtypeofdata);

			if ($uitype == 68 || $uitype == 59) {
				$fieldtypeofdata = 'V';
			}
			if ($fieldtablename == 'vtiger_crmentity') {
				$fieldtablename = $fieldtablename . $module;
			}
			if ($fieldname == 'assigned_user_id') {
				$fieldtablename = 'vtiger_users' . $module;
				$fieldcolname = 'user_name';
			}
			if ($fieldname == 'assigned_user_id1') {
				$fieldtablename = 'vtiger_usersRel1';
				$fieldcolname = 'user_name';
			}
			$fieldlabel = $adb->queryResult($result, $i, 'fieldlabel');
			$fieldlabel1 = str_replace(' ', '__', $fieldlabel);
			$optionvalue = $fieldtablename . ':' . $fieldcolname . ':' . $module . '__' . $fieldlabel1 . ':' . $fieldname . ':' . $fieldtypeofdata;

			$adv_rel_field_tod_value = '$' . $module . '#' . $fieldname . '$' . '::' . \App\Language::translate($module, $module) . ' ' . \App\Language::translate($fieldlabel, $module);
			if (!is_array($this->adv_rel_fields[$fieldtypeofdata]) ||
				!in_array($adv_rel_field_tod_value, $this->adv_rel_fields[$fieldtypeofdata])) {
				$this->adv_rel_fields[$fieldtypeofdata][] = $adv_rel_field_tod_value;
			}
			//added to escape attachments fields in Reports as we have multiple attachments
			if ($module == 'HelpDesk' && $fieldname == 'filename') {
				continue;
			}

			if (is_string($block) || $group_res_by_block === false) {
				$module_columnlist[$optionvalue] = $fieldlabel;
			} else {
				$module_columnlist[$blockid][$optionvalue] = $fieldlabel;
			}
		}

		return $module_columnlist;
	}

	/** Function to set the standard filter vtiger_fields for the given vtiger_report
	 *  This function gets the standard filter vtiger_fields for the given vtiger_report
	 *  and set the values to the corresponding variables
	 *  It accepts the repordid as argument.
	 */
	public function getSelectedStandardCriteria($reportid)
	{
		$adb = PearDatabase::getInstance();
		$sSQL = 'select vtiger_reportdatefilter.* from vtiger_reportdatefilter inner join vtiger_report on vtiger_report.reportid = vtiger_reportdatefilter.datefilterid where vtiger_report.reportid=?';
		$result = $adb->pquery($sSQL, [$reportid]);
		$selectedstdfilter = $adb->fetchArray($result);

		$this->stdselectedcolumn = $selectedstdfilter['datecolumnname'];
		$this->stdselectedfilter = $selectedstdfilter['datefilter'];

		if ($selectedstdfilter['datefilter'] == 'custom') {
			if ($selectedstdfilter['startdate'] != '0000-00-00') {
				$startDateTime = new DateTimeField($selectedstdfilter['startdate'] . ' ' . date('H:i:s'));
				$this->startdate = $startDateTime->getDisplayDate();
			}
			if ($selectedstdfilter['enddate'] != '0000-00-00') {
				$endDateTime = new DateTimeField($selectedstdfilter['enddate'] . ' ' . date('H:i:s'));
				$this->enddate = $endDateTime->getDisplayDate();
			}
		}
	}

	public function getEscapedColumns($selectedfields)
	{
		$fieldname = $selectedfields[3];
		if ($fieldname == 'parent_id') {
			if ($this->primarymodule == 'HelpDesk' && $selectedfields[0] == 'vtiger_crmentityRelHelpDesk') {
				$querycolumn = "case vtiger_crmentityRelHelpDesk.setype when 'Accounts' then vtiger_accountRelHelpDesk.accountname when 'Contacts' then vtiger_contactdetailsRelHelpDesk.lastname End" . " '" . $selectedfields[2] . "', vtiger_crmentityRelHelpDesk.setype 'Entity_type'";

				return $querycolumn;
			}
			if ($this->primarymodule == 'Products' || $this->secondarymodule == 'Products') {
				$querycolumn = "case vtiger_crmentityRelProducts.setype when 'Accounts' then vtiger_accountRelProducts.accountname when 'Leads' then vtiger_leaddetailsRelProducts.lastname End" . " '" . $selectedfields[2] . "', vtiger_crmentityRelProducts.setype 'Entity_type'";
			}
			if ($this->primarymodule == 'Calendar' || $this->secondarymodule == 'Calendar') {
				$querycolumn = "case vtiger_crmentityRelCalendar.setype when 'Accounts' then vtiger_accountRelCalendar.accountname when 'Leads' then vtiger_leaddetailsRelCalendar.lastname End" . " '" . $selectedfields[2] . "', vtiger_crmentityRelCalendar.setype 'Entity_type'";
			}
		}

		return $querycolumn;
	}

	public function getaccesfield($module)
	{
		$currentUser = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$adb = PearDatabase::getInstance();
		$access_fields = [];

		$profileList = $currentUser->getProfiles();
		$query = 'select vtiger_field.fieldname from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid where';
		$params = [];
		if ($module == 'Calendar') {
			$query .= ' vtiger_field.tabid in (9,16) and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)';
			if (count($profileList) > 0) {
				$query .= ' and vtiger_profile2field.profileid in (' . $adb->generateQuestionMarks($profileList) . ')';
				array_push($params, $profileList);
			}
			$query .= ' group by vtiger_field.fieldid order by block,sequence';
		} else {
			array_push($params, $this->primodule, $this->secmodule);
			$query .= ' vtiger_field.tabid in (select tabid from vtiger_tab where vtiger_tab.name in (?,?)) and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)';
			if (count($profileList) > 0) {
				$query .= ' and vtiger_profile2field.profileid in (' . $adb->generateQuestionMarks($profileList) . ')';
				array_push($params, $profileList);
			}
			$query .= ' group by vtiger_field.fieldid order by block,sequence';
		}
		$result = $adb->pquery($query, $params);

		while ($collistrow = $adb->fetchArray($result)) {
			$access_fields[] = $collistrow['fieldname'];
		}

		return $access_fields;
	}

	/** Function to set the order of grouping and to find the columns responsible
	 *  to the grouping
	 *  This function accepts the vtiger_reportid as variable,sets the variable ascdescorder[] to the sort order and
	 *  returns the array array_list which has the column responsible for the grouping
	 *  Array array_list[0]=columnname.
	 */
	public function getSelctedSortingColumns($reportid)
	{
		$adb = PearDatabase::getInstance();

		$sreportsortsql = 'select vtiger_reportsortcol.* from vtiger_report';
		$sreportsortsql .= ' inner join vtiger_reportsortcol on vtiger_report.reportid = vtiger_reportsortcol.reportid';
		$sreportsortsql .= ' where vtiger_report.reportid =? order by vtiger_reportsortcol.sortcolid';

		$result = $adb->pquery($sreportsortsql, [$reportid]);
		$noofrows = $adb->numRows($result);

		for ($i = 0; $i < $noofrows; ++$i) {
			$fieldcolname = $adb->queryResult($result, $i, 'columnname');
			$sort_values = $adb->queryResult($result, $i, 'sortorder');
			$this->ascdescorder[] = $sort_values;
			$array_list[] = $fieldcolname;
		}

		\App\Log::trace('Reports :: Successfully returned getSelctedSortingColumns');

		return $array_list;
	}

	/** Function to get the selected columns list for a selected vtiger_report
	 *  This function accepts the vtiger_reportid as the argument and get the selected columns
	 *  for the given vtiger_reportid and it forms a combo lists and returns
	 *  HTML of the combo values.
	 */
	public function getSelectedColumnsList($reportid)
	{
		$adb = PearDatabase::getInstance();
		$ssql = 'select vtiger_selectcolumn.* from vtiger_report inner join vtiger_selectquery on vtiger_selectquery.queryid = vtiger_report.queryid';
		$ssql .= ' left join vtiger_selectcolumn on vtiger_selectcolumn.queryid = vtiger_selectquery.queryid';
		$ssql .= ' where vtiger_report.reportid = ?';
		$ssql .= ' order by vtiger_selectcolumn.columnindex';
		$result = $adb->pquery($ssql, [$reportid]);
		$permitted_fields = [];

		$selected_mod = explode(':', $this->secmodule);
		array_push($selected_mod, $this->primodule);
		while ($columnslistrow = $adb->fetchArray($result)) {
			$fieldname = '';
			$fieldcolname = $columnslistrow['columnname'];

			$selmod_field_disabled = true;
			foreach ($selected_mod as $smod) {
				if ((stripos($fieldcolname, ':' . $smod . '__') > -1) && \App\Module::isModuleActive($smod)) {
					$selmod_field_disabled = false;
					break;
				}
			}
			if ($selmod_field_disabled === false) {
				list($tablename, $colname, $module_field, $fieldname, $single) = explode(':', $fieldcolname);
				require 'user_privileges/user_privileges_' . \App\User::getCurrentUserId() . '.php';
				list($module) = explode('__', $module_field);
				if (count($permitted_fields) == 0 && $is_admin === false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1) {
					$permitted_fields = $this->getaccesfield($module);
				}
				$fieldlabel = trim(str_replace($module, ' ', $module_field));
				$mod_arr = explode('__', $fieldlabel);
				$mod = ($mod_arr[0] == '') ? $module : $mod_arr[0];
				$fieldlabel = trim(str_replace('__', ' ', $fieldlabel));
				//modified code to support i18n issue
				$mod_lbl = \App\Language::translate($mod, $module); //module
				$fld_lbl = \App\Language::translate($fieldlabel, $module); //fieldlabel
				$fieldlabel = $mod_lbl . ' ' . $fld_lbl;
				if (!\App\Field::getFieldPermission($mod, $fieldname) && $colname !== 'crmid') {
					$shtml .= "<option permission='no' value=\"" . $fieldcolname . "\" disabled = 'true'>" . $fieldlabel . '</option>';
				} else {
					$shtml .= "<option permission='yes' value=\"" . $fieldcolname . '">' . $fieldlabel . '</option>';
				}
			}
			//end
		}
		\App\Log::trace('ReportRun :: Successfully returned getQueryColumnsList' . $reportid);

		return $shtml;
	}

	public function getAdvancedFilterList($reportid)
	{
		$adb = PearDatabase::getInstance();
		$advft_criteria = [];
		$sql = 'SELECT * FROM vtiger_relcriteria_grouping WHERE queryid = ? ORDER BY groupid';
		$groupsresult = $adb->pquery($sql, [$reportid]);

		$i = 1;
		$j = 0;
		while ($relcriteriagroup = $adb->fetchArray($groupsresult)) {
			$groupId = $relcriteriagroup['groupid'];
			$groupCondition = $relcriteriagroup['group_condition'];

			$ssql = 'select vtiger_relcriteria.* from vtiger_report
						inner join vtiger_relcriteria on vtiger_relcriteria.queryid = vtiger_report.queryid
						left join vtiger_relcriteria_grouping on vtiger_relcriteria.queryid = vtiger_relcriteria_grouping.queryid
								and vtiger_relcriteria.groupid = vtiger_relcriteria_grouping.groupid';
			$ssql .= ' where vtiger_report.reportid = ? && vtiger_relcriteria.groupid = ? order by vtiger_relcriteria.columnindex';

			$result = $adb->pquery($ssql, [$reportid, $groupId]);
			$noOfColumns = $adb->numRows($result);
			if ($noOfColumns <= 0) {
				continue;
			}

			while ($relcriteriarow = $adb->fetchArray($result)) {
				$criteria = [];
				$criteria['columnname'] = $relcriteriarow['columnname'];
				$criteria['comparator'] = $relcriteriarow['comparator'];
				$advfilterval = $relcriteriarow['value'];
				$col = explode(':', $relcriteriarow['columnname']);

				$moduleFieldLabel = $col[2];

				list($module, $fieldLabel) = explode('__', $moduleFieldLabel, 2);
				$fieldInfo = ReportUtils::getFieldByReportLabel($module, $fieldLabel);
				$fieldType = null;
				if (!empty($fieldInfo)) {
					$fieldModel = Vtiger_Field_Model::getInstanceFromFieldId($fieldInfo['fieldid']);
					$fieldType = $fieldModel->getFieldDataType();
				}
				if ($fieldType === 'currency') {
					if ($fieldModel->getUIType() == '71') {
						$advfilterval = CurrencyField::convertToUserFormat($advfilterval, null);
					} elseif ($fieldModel->getUIType() == '72') {
						$advfilterval = CurrencyField::convertToUserFormat($advfilterval, null, true);
					}
				}

				$temp_val = explode(',', $relcriteriarow['value']);
				if ($col[4] == 'D' || ($col[4] == 'T' && $col[1] != 'time_start' && $col[1] != 'time_end') || ($col[4] == 'DT')) {
					$val = [];
					$countTempVal = count($temp_val);
					for ($x = 0; $x < $countTempVal; ++$x) {
						if ($col[4] == 'D') {
							$date = new DateTimeField(trim($temp_val[$x]));
							$val[$x] = $date->getDisplayDate();
						} elseif ($col[4] == 'DT') {
							$date = new DateTimeField(trim($temp_val[$x]));
							$val[$x] = $date->getDisplayDateTimeValue();
						} else {
							$date = new DateTimeField(trim($temp_val[$x]));
							$val[$x] = $date->getDisplayTime();
						}
					}
					$advfilterval = implode(',', $val);
				}

				//In vtiger6 report filter conditions, if the value has "(double quotes) then it is failed.
				$criteria['value'] = \App\Purifier::encodeHtml(App\Purifier::decodeHtml($advfilterval));
				$criteria['column_condition'] = $relcriteriarow['column_condition'];

				$advft_criteria[$relcriteriarow['groupid']]['columns'][$j] = $criteria;
				$advft_criteria[$relcriteriarow['groupid']]['condition'] = $groupCondition;
				++$j;
			}
			++$i;
		}
		// Clear the condition (and/or) for last group, if any.
		if (!empty($advft_criteria[$i - 1]['condition'])) {
			$advft_criteria[$i - 1]['condition'] = '';
		}
		$this->advft_criteria = $advft_criteria;
		\App\Log::trace('Reports :: Successfully returned getAdvancedFilterList');

		return true;
	}

	//<<<<<<<<advanced filter>>>>>>>>>>>>>>

	/** Function to get the list of vtiger_report folders when Save and run  the vtiger_report
	 *  This function gets the vtiger_report folders from database and form
	 *  a combo values of the folders and return
	 *  HTML of the combo values.
	 */
	public function sgetRptFldrSaveReport()
	{
		$adb = PearDatabase::getInstance();

		$sql = 'select * from vtiger_reportfolder order by folderid';
		$result = $adb->pquery($sql, []);
		$reportfldrow = $adb->fetchArray($result);
		do {
			$shtml .= "<option value='" . $reportfldrow['folderid'] . "'>" . $reportfldrow['foldername'] . '</option>';
		} while ($reportfldrow = $adb->fetchArray($result));

		\App\Log::trace('Reports :: Successfully returned sgetRptFldrSaveReport');

		return $shtml;
	}

	/** Function to get the column to total vtiger_fields in Reports
	 *  This function gets columns to total vtiger_field
	 *  and generated the html for that vtiger_fields
	 *  It returns the HTML of the vtiger_fields along with the check boxes.
	 */
	public function sgetColumntoTotal($primarymodule, $secondarymodule)
	{
		$options = [];
		$options[] = $this->sgetColumnstoTotalHTML($primarymodule, 0);
		if (!empty($secondarymodule)) {
			$countSecondaryModule = count($secondarymodule);
			for ($i = 0; $i < $countSecondaryModule; ++$i) {
				$options[] = $this->sgetColumnstoTotalHTML($secondarymodule[$i], ($i + 1));
			}
		}

		return $options;
	}

	/** Function to get the selected columns of total vtiger_fields in Reports
	 *  This function gets selected columns of total vtiger_field
	 *  and generated the html for that vtiger_fields
	 *  It returns the HTML of the vtiger_fields along with the check boxes.
	 */
	public function sgetColumntoTotalSelected($primarymodule, $secondarymodule, $reportid)
	{
		$adb = PearDatabase::getInstance();

		$options = [];
		if ($reportid != '') {
			$ssql = 'select vtiger_reportsummary.* from vtiger_reportsummary inner join vtiger_report on vtiger_report.reportid = vtiger_reportsummary.reportsummaryid where vtiger_report.reportid=?';
			$result = $adb->pquery($ssql, [$reportid]);
			if ($result) {
				$reportsummaryrow = $adb->fetchArray($result);

				do {
					$this->columnssummary[] = $reportsummaryrow['columnname'];
				} while ($reportsummaryrow = $adb->fetchArray($result));
			}
		}
		$options[] = $this->sgetColumnstoTotalHTML($primarymodule, 0);
		if ($secondarymodule != '') {
			$secondarymodule = explode(':', $secondarymodule);
			$countSecondaryModule = count($secondarymodule);
			for ($i = 0; $i < $countSecondaryModule; ++$i) {
				$options[] = $this->sgetColumnstoTotalHTML($secondarymodule[$i], ($i + 1));
			}
		}

		\App\Log::trace('Reports :: Successfully returned sgetColumntoTotalSelected');

		return $options;
	}

	/** Function to form the HTML for columns to total
	 *  This function formulates the HTML format of the
	 *  vtiger_fields along with four checkboxes
	 *  It returns the HTML of the vtiger_fields along with the check boxes.
	 */
	public function sgetColumnstoTotalHTML($module)
	{
		//retreive the vtiger_tabid
		$adb = PearDatabase::getInstance();
		$currentUser = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$tabid = \App\Module::getModuleId($module);
		$escapedchars = ['__SUM', '__AVG', '__MIN', '__MAX'];
		$sparams = [$tabid];
		$profileList = $currentUser->getProfiles();
		$ssql = 'select * from vtiger_field inner join vtiger_tab on vtiger_tab.tabid = vtiger_field.tabid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid  where vtiger_field.uitype != 50 and vtiger_field.tabid=? and vtiger_field.displaytype in (1,2,3) and vtiger_def_org_field.visible=0 and vtiger_profile2field.visible=0 and vtiger_field.presence in (0,2)';
		if ($profileList !== false && count($profileList) > 0) {
			$ssql .= ' and vtiger_profile2field.profileid in (' . $adb->generateQuestionMarks($profileList) . ')';
			array_push($sparams, $profileList);
		}
		//Added to avoid display the Related fields (Account name,Vandor name,product name, etc) in Report Calculations(SUM,AVG..)
		switch ($tabid) {
			case 4://Contacts
				$ssql .= " and vtiger_field.fieldname not in ('account_id')";
				break;
			case 6://Accounts
				$ssql .= " and vtiger_field.fieldname not in ('account_id')";
				break;
			case 9://Calandar
				$ssql .= " and vtiger_field.fieldname not in ('parent_id','contact_id')";
				break;
			case 13://Trouble tickets(HelpDesk)
				$ssql .= " and vtiger_field.fieldname not in ('parent_id','product_id')";
				break;
			case 14://Products
				$ssql .= " and vtiger_field.fieldname not in ('vendor_id','product_id')";
				break;
			case 21://Purchase Order
				$ssql .= " and vtiger_field.fieldname not in ('contact_id','vendor_id','currency_id')";
				break;
			case 26://Campaigns
				$ssql .= " and vtiger_field.fieldname not in ('product_id')";
				break;
		}

		$ssql .= ' order by sequence';

		$result = $adb->pquery($ssql, $sparams);
		$columntototalrow = $adb->fetchArray($result);
		$options_list = [];
		do {
			$typeofdata = explode('~', $columntototalrow['typeofdata']);

			if ($typeofdata[0] == 'N' || $typeofdata[0] == 'I' || ($typeofdata[0] == 'NN' && !empty($typeofdata[2]))) {
				$options = [];
				if (isset($this->columnssummary)) {
					$selectedcolumn = '';
					$selectedcolumn1 = '';

					$countColumnsSummary = count($this->columnssummary);
					for ($i = 0; $i < $countColumnsSummary; ++$i) {
						$selectedcolumnarray = explode(':', $this->columnssummary[$i]);
						$selectedcolumn = $selectedcolumnarray[1] . ':' . $selectedcolumnarray[2] . ':' .
							str_replace($escapedchars, '', $selectedcolumnarray[3]);

						if ($selectedcolumn != $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . str_replace(' ', '__', $columntototalrow['fieldlabel'])) {
							$selectedcolumn = '';
						} else {
							$selectedcolumn1[$selectedcolumnarray[4]] = $this->columnssummary[$i];
						}
					}
					if (!\App\Request::_isEmpty('record')) {
						$options['label'][] = \App\Language::translate($columntototalrow['tablabel'], $columntototalrow['tablabel']) . ' -' . \App\Language::translate($columntototalrow['fieldlabel'], $columntototalrow['tablabel']);
					}

					$columntototalrow['fieldlabel'] = str_replace(' ', '__', $columntototalrow['fieldlabel']);
					$options[] = \App\Language::translate($columntototalrow['tablabel'], $columntototalrow['tablabel']) . ' - ' . \App\Language::translate($columntototalrow['fieldlabel'], $columntototalrow['tablabel']);
					if ($selectedcolumn1[2] == 'cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '__SUM:2') {
						$options[] = '<input checked name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '__SUM:2" type="checkbox" value="">';
					} else {
						$options[] = '<input name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '__SUM:2" type="checkbox" value="">';
					}
					if ($selectedcolumn1[3] == 'cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '__AVG:3') {
						$options[] = '<input checked name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '__AVG:3" type="checkbox" value="">';
					} else {
						$options[] = '<input name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '__AVG:3" type="checkbox" value="">';
					}

					if ($selectedcolumn1[4] == 'cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '__MIN:4') {
						$options[] = '<input checked name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '__MIN:4" type="checkbox" value="">';
					} else {
						$options[] = '<input name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '__MIN:4" type="checkbox" value="">';
					}

					if ($selectedcolumn1[5] == 'cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '__MAX:5') {
						$options[] = '<input checked name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '__MAX:5" type="checkbox" value="">';
					} else {
						$options[] = '<input name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '__MAX:5" type="checkbox" value="">';
					}
				} else {
					$options[] = \App\Language::translate($columntototalrow['tablabel'], $columntototalrow['tablabel']) . ' - ' . \App\Language::translate($columntototalrow['fieldlabel'], $columntototalrow['tablabel']);
					$options[] = '<input name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '__SUM:2" type="checkbox" value="">';
					$options[] = '<input name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '__AVG:3" type="checkbox" value="" >';
					$options[] = '<input name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '__MIN:4"type="checkbox" value="" >';
					$options[] = '<input name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '__MAX:5" type="checkbox" value="" >';
				}
				$options_list[] = $options;
			}
		} while ($columntototalrow = $adb->fetchArray($result));

		\App\Log::trace('Reports :: Successfully returned sgetColumnstoTotalHTML');

		return $options_list;
	}
}
