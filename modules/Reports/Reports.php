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
require_once('include/utils/UserInfoUtil.php');
require_once 'modules/Reports/ReportUtils.php';
global $calpath;
global $mod_strings;
global $app_list_strings;
global $modules;
global $blocks;
global $report_modules;
global $related_modules;
global $old_related_modules;

$old_related_modules = Array('Accounts' => Array('Contacts', 'Products'),
	'Contacts' => Array('Accounts'),
	'Calendar' => Array('Leads', 'Accounts', 'Contacts'),
	'Products' => Array('Accounts', 'Contacts'),
	'HelpDesk' => Array('Products'),
	'Campaigns' => Array('Products')
);

$related_modules = [];

class Reports extends CRMEntity
{

	/**
	 * This class has the informations for Reports and inherits class CRMEntity and
	 * has the variables required to generate,save,restore vtiger_reports
	 * and also the required functions for the same
	 * Contributor(s): ______________________________________..
	 */
	var $srptfldridjs;
	var $column_fields = [];
	var $sort_fields = [];
	var $sort_values = [];
	var $id;
	var $mode;
	var $mcount;
	var $startdate;
	var $enddate;
	var $ascdescorder;
	var $stdselectedfilter;
	var $stdselectedcolumn;
	var $primodule;
	var $secmodule;
	var $columnssummary;
	var $is_editable;
	var $reporttype;
	var $reportname;
	var $reportdescription;
	var $folderid;
	var $module_blocks;
	var $pri_module_columnslist;
	var $sec_module_columnslist;
	var $advft_criteria;
	var $adv_rel_fields = [];
	var $module_list = [];

	/** Function to set primodule,secmodule,reporttype,reportname,reportdescription,folderid for given vtiger_reportid
	 *  This function accepts the vtiger_reportid as argument
	 *  It sets primodule,secmodule,reporttype,reportname,reportdescription,folderid for the given vtiger_reportid
	 */
	public function __construct($reportid = "")
	{
		global $adb, $current_user, $theme, $mod_strings;
		$this->initListOfModules();
		if ($reportid != "") {
			// Lookup information in cache first
			$cachedInfo = VTCacheUtils::lookupReport_Info($current_user->id, $reportid);
			$subordinate_users = VTCacheUtils::lookupReport_SubordinateUsers($reportid);

			if ($cachedInfo === false) {
				$ssql = "select vtiger_reportmodules.*,vtiger_report.* from vtiger_report inner join vtiger_reportmodules on vtiger_report.reportid = vtiger_reportmodules.reportmodulesid";
				$ssql .= " where vtiger_report.reportid = ?";
				$params = array($reportid);

				require_once('include/utils/GetUserGroups.php');
				require('user_privileges/user_privileges_' . $current_user->id . '.php');
				$userGroups = new GetUserGroups();
				$userGroups->getAllUserGroups($current_user->id);
				$user_groups = $userGroups->user_groups;
				if (!empty($user_groups) && $is_admin == false) {
					$user_group_query = " (shareid IN (" . generateQuestionMarks($user_groups) . ") && setype='groups') OR";
					array_push($params, $user_groups);
				}

				$non_admin_query = " vtiger_report.reportid IN (SELECT reportid from vtiger_reportsharing WHERE $user_group_query (shareid=? && setype='users'))";
				if ($is_admin == false) {
					$ssql .= " and ( (" . $non_admin_query . ") or vtiger_report.sharingtype='Public' or vtiger_report.owner = ? or vtiger_report.owner in(select vtiger_user2role.userid from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like '" . $current_user_parent_role_seq . "::%'))";
					array_push($params, $current_user->id);
					array_push($params, $current_user->id);
				}
				$query = $adb->pquery('select userid from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like ?', ["$current_user_parent_role_seq::%"]);
				$subordinate_users = [];
				while (($value = $adb->getSingleValue($query)) !== false) {
					$subordinate_users[] = $value;
				}

				// Update subordinate user information for re-use
				VTCacheUtils::updateReport_SubordinateUsers($reportid, $subordinate_users);

				$result = $adb->pquery($ssql, $params);
				if ($result && $adb->num_rows($result)) {
					$reportmodulesrow = $adb->fetch_array($result);

					// Update information in cache now
					VTCacheUtils::updateReport_Info(
						$current_user->id, $reportid, $reportmodulesrow["primarymodule"], $reportmodulesrow["secondarymodules"], $reportmodulesrow["reporttype"], $reportmodulesrow["reportname"], $reportmodulesrow["description"], $reportmodulesrow["folderid"], $reportmodulesrow["owner"]
					);
				}

				// Re-look at cache to maintain code-consistency below
				$cachedInfo = VTCacheUtils::lookupReport_Info($current_user->id, $reportid);
			}

			if ($cachedInfo) {
				$this->primodule = $cachedInfo["primarymodule"];
				$this->secmodule = $cachedInfo["secondarymodules"];
				$this->reporttype = $cachedInfo["reporttype"];
				$this->reportname = decode_html($cachedInfo["reportname"]);
				$this->reportdescription = decode_html($cachedInfo["description"]);
				$this->folderid = $cachedInfo["folderid"];
				if ($is_admin == true || in_array($cachedInfo["owner"], $subordinate_users) || $cachedInfo["owner"] == $current_user->id)
					$this->is_editable = 'true';
				else
					$this->is_editable = 'false';
			} else {
				throw new \Exception\NoPermitted('LBL_PERMISSION_DENIED');
			}
		}
	}

	// Update the module list for listing columns for report creation.
	public function updateModuleList($module)
	{
		$adb = PearDatabase::getInstance();
		if (!isset($module))
			return;
		require_once('include/utils/utils.php');
		$tabid = \includes\Modules::getModuleId($module);
		if ($module == 'Calendar') {
			$tabid = [9, 16];
		}
		$sql = sprintf('SELECT blockid, blocklabel FROM vtiger_blocks WHERE tabid IN (%s)', generateQuestionMarks($tabid));
		$res = $adb->pquery($sql, [$tabid]);
		$noOfRows = $adb->num_rows($res);
		if ($noOfRows <= 0)
			return;
		for ($index = 0; $index < $noOfRows; ++$index) {
			$blockid = $adb->query_result($res, $index, 'blockid');
			if (in_array($blockid, $this->module_list[$module]))
				continue;
			$blockid_list[] = $blockid;
			$blocklabel = $adb->query_result($res, $index, 'blocklabel');
			$this->module_list[$module][$blocklabel] = $blockid;
		}
	}

	// Initializes the module list for listing columns for report creation.
	public function initListOfModules()
	{
		global $old_related_modules;

		$adb = PearDatabase::getInstance();
		$restricted_modules = array('Events');
		$restricted_blocks = array('LBL_COMMENTS', 'LBL_COMMENT_INFORMATION');

		$this->module_id = array();
		$this->module_list = array();

		// Prefetch module info to check active or not and also get list of tabs
		$modulerows = vtlib_prefetchModuleActiveInfo(false);

		$cachedInfo = VTCacheUtils::lookupReport_ListofModuleInfos();

		if ($cachedInfo !== false) {
			$this->module_list = $cachedInfo['module_list'];
			$this->related_modules = $cachedInfo['related_modules'];
		} else {

			if ($modulerows) {
				foreach ($modulerows as $resultrow) {
					if ($resultrow['presence'] == '1')
						continue;   // skip disabled modules
					if ($resultrow['isentitytype'] != '1')
						continue;  // skip extension modules
					if (in_array($resultrow['name'], $restricted_modules)) { // skip restricted modules
						continue;
					}
					if ($resultrow['name'] != 'Calendar') {
						$this->module_id[$resultrow['tabid']] = $resultrow['name'];
					} else {
						$this->module_id[9] = $resultrow['name'];
						$this->module_id[16] = $resultrow['name'];
					}
					$this->module_list[$resultrow['name']] = array();
				}

				$moduleids = array_keys($this->module_id);
				$query = sprintf('SELECT blockid, blocklabel, tabid FROM vtiger_blocks WHERE tabid IN (%s)', generateQuestionMarks($moduleids));
				$reportblocks = $adb->pquery($query, [$moduleids]);
				$prev_block_label = '';
				if ($adb->num_rows($reportblocks)) {
					while ($resultrow = $adb->fetch_array($reportblocks)) {
						$blockid = $resultrow['blockid'];
						$blocklabel = $resultrow['blocklabel'];
						$module = $this->module_id[$resultrow['tabid']];

						if (in_array($blocklabel, $restricted_blocks) ||
							in_array($blockid, $this->module_list[$module]) ||
							isset($this->module_list[$module][\includes\Language::translate($blocklabel, $module)])
						) {
							continue;
						}

						if (!empty($blocklabel)) {
							if ($module == 'Calendar' && $blocklabel == 'LBL_CUSTOM_INFORMATION')
								$this->module_list[$module][$blockid] = \includes\Language::translate($blocklabel, $module);
							else
								$this->module_list[$module][$blockid] = \includes\Language::translate($blocklabel, $module);
							$prev_block_label = $blocklabel;
						} else {
							$this->module_list[$module][$blockid] = \includes\Language::translate($prev_block_label, $module);
						}
					}
				}
				$query = sprintf("SELECT vtiger_tab.name, vtiger_relatedlists.tabid FROM vtiger_tab
					INNER JOIN vtiger_relatedlists on vtiger_tab.tabid=vtiger_relatedlists.related_tabid
					WHERE vtiger_tab.isentitytype=1
					AND vtiger_tab.name NOT IN(%s)
					AND vtiger_tab.presence = 0 && vtiger_relatedlists.label!='Activity History'
					UNION
					SELECT relmodule, vtiger_tab.tabid FROM vtiger_fieldmodulerel
					INNER JOIN vtiger_tab on vtiger_tab.name = vtiger_fieldmodulerel.module
					WHERE vtiger_tab.isentitytype = 1
					AND vtiger_tab.name NOT IN(%s)
					AND vtiger_tab.presence = 0", generateQuestionMarks($restricted_modules), generateQuestionMarks($restricted_modules));
				$relatedmodules = $adb->pquery($query, [$restricted_modules, $restricted_modules]);
				if ($adb->num_rows($relatedmodules)) {
					while ($resultrow = $adb->fetch_array($relatedmodules)) {
						$module = $this->module_id[$resultrow['tabid']];

						if (!isset($this->related_modules[$module])) {
							$this->related_modules[$module] = array();
						}

						if ($module != $resultrow['name']) {
							$this->related_modules[$module][] = $resultrow['name'];
						}

						// To achieve Backward Compatability with Report relations
						if (isset($old_related_modules[$module])) {

							$rel_mod = array();
							foreach ($old_related_modules[$module] as $key => $name) {
								if (\includes\Modules::isModuleActive($name) && isPermitted($name, 'index', '')) {
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
				foreach ($this->related_modules as $module => $related_modules) {
					if ($module == 'Emails') {
						$this->related_modules[$module] = getEmailRelatedModules();
					}
				}
				// Put the information in cache for re-use
				VTCacheUtils::updateReport_ListofModuleInfos($this->module_list, $this->related_modules);
			}
		}
	}
	// END

	/** Function to get the Listview of Reports
	 *  This function accepts no argument
	 *  This generate the Reports view page and returns a string
	 *  contains HTML
	 */
	public function sgetRptFldr($mode = '')
	{

		global $mod_strings;
		$adb = PearDatabase::getInstance();
		$log = LoggerManager::getInstance();
		$returndata = [];
		$sql = "select * from vtiger_reportfolder order by folderid";
		$result = $adb->pquery($sql, array());
		$reportfldrow = $adb->fetch_array($result);
		if ($mode != '') {
			// Fetch detials of all reports of folder at once
			$reportsInAllFolders = $this->sgetRptsforFldr(false);

			do {
				if ($reportfldrow["state"] == $mode) {
					$details = [];
					$details['state'] = $reportfldrow["state"];
					$details['id'] = $reportfldrow["folderid"];
					$details['name'] = ($mod_strings[$reportfldrow["foldername"]] == '' ) ? $reportfldrow["foldername"] : $mod_strings[$reportfldrow["foldername"]];
					$details['description'] = $reportfldrow["description"];
					$details['fname'] = popup_decode_html($details['name']);
					$details['fdescription'] = popup_decode_html($reportfldrow["description"]);
					$details['details'] = $reportsInAllFolders[$reportfldrow["folderid"]];
					$returndata[] = $details;
				}
			} while ($reportfldrow = $adb->fetch_array($result));
		} else {
			do {
				$details = [];
				$details['state'] = $reportfldrow["state"];
				$details['id'] = $reportfldrow["folderid"];
				$details['name'] = ($mod_strings[$reportfldrow["foldername"]] == '' ) ? $reportfldrow["foldername"] : $mod_strings[$reportfldrow["foldername"]];
				$details['description'] = $reportfldrow["description"];
				$details['fname'] = popup_decode_html($details['name']);
				$details['fdescription'] = popup_decode_html($reportfldrow["description"]);
				$returndata[] = $details;
			} while ($reportfldrow = $adb->fetch_array($result));
		}

		$log->info("Reports :: ListView->Successfully returned vtiger_report folder HTML");
		return $returndata;
	}

	/** Function to get all Reports when in list view
	 *  This function accepts the folderid,paramslist
	 *  This Generates the Reports under each Reports module
	 *  This Returns a HTML sring
	 */
	public function sgetAllRpt($fldrId, $paramsList)
	{
		$adb = PearDatabase::getInstance();
		$log = LoggerManager::getInstance();
		$returndata = [];
		$sql = "select vtiger_report.*, vtiger_reportmodules.*, vtiger_reportfolder.folderid from vtiger_report inner join vtiger_reportfolder on vtiger_reportfolder.folderid = vtiger_report.folderid";
		$sql.=" inner join vtiger_reportmodules on vtiger_reportmodules.reportmodulesid = vtiger_report.reportid";
		if ($paramsList) {
			$startIndex = $paramsList['startIndex'];
			$pageLimit = $paramsList['pageLimit'];
			$orderBy = $paramsList['orderBy'];
			$sortBy = $paramsList['sortBy'];
			if ($orderBy) {
				$sql .= " ORDER BY $orderBy $sortBy";
			}
			$sql .= " LIMIT $startIndex," . ($pageLimit + 1);
		}
		$result = $adb->pquery($sql, $params);
		$report = $adb->fetch_array($result);
		if (count($report) > 0) {
			do {
				$report_details = [];
				$report_details ['customizable'] = $report["customizable"];
				$report_details ['reportid'] = $report["reportid"];
				$report_details ['primarymodule'] = $report["primarymodule"];
				$report_details ['secondarymodules'] = $report["secondarymodules"];
				$report_details ['state'] = $report["state"];
				$report_details ['description'] = $report["description"];
				$report_details ['reportname'] = $report["reportname"];
				$report_details ['sharingtype'] = $report["sharingtype"];
				$report_details['folderid'] = $report["folderid"];
				if ($is_admin == true)
					$report_details ['editable'] = 'true';
				else
					$report_details['editable'] = 'false';

				if (isPermitted($report["primarymodule"], 'index') == "yes")
					$returndata[] = $report_details;
			}while ($report = $adb->fetch_array($result));
		}
		$log->info("Reports :: ListView->Successfully returned vtiger_report details HTML");
		return $returndata;
	}

	/** Function to get the Reports inside each modules
	 *  This function accepts the folderid
	 *  This Generates the Reports under each Reports module
	 *  This Returns a HTML sring
	 */
	public function sgetRptsforFldr($rpt_fldr_id, $paramsList = false)
	{
		$srptdetails = "";
		$adb = PearDatabase::getInstance();
		$currentUser = Users_Privileges_Model::getCurrentUserModel();
		$log = vglobal('log');
		$mod_strings = vglobal('mod_strings');
		$returndata = [];

		require_once('include/utils/UserInfoUtil.php');

		$sql = "select vtiger_report.*, vtiger_reportmodules.*, vtiger_reportfolder.folderid from vtiger_report inner join vtiger_reportfolder on vtiger_reportfolder.folderid = vtiger_report.folderid";
		$sql .= " inner join vtiger_reportmodules on vtiger_reportmodules.reportmodulesid = vtiger_report.reportid";

		$params = array();

		// If information is required only for specific report folder?
		if ($rpt_fldr_id !== false) {
			$sql .= " where vtiger_reportfolder.folderid=?";
			$params[] = $rpt_fldr_id;
		}

		require('user_privileges/user_privileges_' . $currentUser->getId() . '.php');
		require_once('include/utils/GetUserGroups.php');
		$userGroups = new GetUserGroups();
		$userGroups->getAllUserGroups($currentUser->getId());
		$user_groups = $userGroups->user_groups;
		if (!empty($user_groups) && $is_admin == false) {
			$user_group_query = " (shareid IN (" . generateQuestionMarks($user_groups) . ") && setype='groups') OR";
			array_push($params, $user_groups);
		}

		$non_admin_query = " vtiger_report.reportid IN (SELECT reportid from vtiger_reportsharing WHERE $user_group_query (shareid=? && setype='users'))";
		if ($is_admin == false) {
			$sql .= " and ( (" . $non_admin_query . ") or vtiger_report.sharingtype='Public' or vtiger_report.owner = ? or vtiger_report.owner in(select vtiger_user2role.userid from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like '" . $current_user_parent_role_seq . "::%'))";
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
			$sql .= " LIMIT $startIndex," . ($pageLimit + 1);
		}
		$query = $adb->pquery('select userid from vtiger_user2role inner join vtiger_users on vtiger_users.id=vtiger_user2role.userid inner join vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid where vtiger_role.parentrole like ?', [$current_user_parent_role_seq . '::%']);
		$subordinate_users = [];
		while (($value = $adb->getSingleValue($query)) !== false) {
			$subordinate_users[] = $value;
		}
		$result = $adb->pquery($sql, $params);

		$report = $adb->fetch_array($result);
		$numRows = $adb->getRowCount($result);
		if ($numRows) {
			do {
				$report_details = [];
				$report_details['customizable'] = $report["customizable"];
				$report_details['reportid'] = $report["reportid"];
				$report_details['primarymodule'] = $report["primarymodule"];
				$report_details['secondarymodules'] = $report["secondarymodules"];
				$report_details['state'] = $report["state"];
				$report_details['description'] = $report["description"];
				$report_details['reportname'] = $report["reportname"];
				$report_details['reporttype'] = $report["reporttype"];
				$report_details['sharingtype'] = $report["sharingtype"];
				if ($is_admin == true || in_array($report["owner"], $subordinate_users) || $report["owner"] == $currentUser->getId())
					$report_details['editable'] = 'true';
				else
					$report_details['editable'] = 'false';

				if (isPermitted($report["primarymodule"], 'index') == "yes")
					$returndata [$report["folderid"]][] = $report_details;
			}while ($report = $adb->fetch_array($result));
		}

		if ($rpt_fldr_id !== false) {
			$returndata = $returndata[$rpt_fldr_id];
		}

		$log->info("Reports :: ListView->Successfully returned vtiger_report details HTML");
		return $returndata;
	}

	/** Function to get the array of ids
	 *  This function forms the array for the ExpandCollapse
	 *  Javascript
	 *  It returns the array of ids
	 *  Array('1RptFldr','2RptFldr',........,'9RptFldr','10RptFldr')
	 */
	public function sgetJsRptFldr()
	{
		$srptfldr_js = "var ReportListArray=new Array(" . $this->srptfldridjs . ")
			setExpandCollapse()";
		return $srptfldr_js;
	}

	/** Function to set the Primary module vtiger_fields for the given Report
	 *  This function sets the primary module columns for the given Report
	 *  It accepts the Primary module as the argument and set the vtiger_fields of the module
	 *  to the varialbe pri_module_columnslist and returns true if sucess
	 */
	public function getPriModuleColumnsList($module)
	{
		$allColumnsListByBlocks = & $this->getColumnsListbyBlock($module, array_keys($this->module_list[$module]), true);
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
		if ($module == 'Emails') {
			foreach ($ret_module_list[$module] as $key => $value) {
				foreach ($value as $key1 => $value1) {
					if ($key1 == 'vtiger_activity:time_start:Emails_Time_Start:time_start:T') {
						unset($ret_module_list[$module][$key][$key1]);
					}
				}
			}
		}
		$this->pri_module_columnslist = $ret_module_list;
		return true;
	}

	/** Function to set the Secondary module fileds for the given Report
	 *  This function sets the secondary module columns for the given module
	 *  It accepts the module as the argument and set the vtiger_fields of the module
	 *  to the varialbe sec_module_columnslist and returns true if sucess
	 */
	public function getSecModuleColumnsList($module)
	{
		if ($module != "") {
			$secmodule = explode(":", $module);
			for ($i = 0; $i < count($secmodule); $i++) {
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
			if ($module == 'Emails') {
				foreach ($this->sec_module_columnslist[$module] as $key => $value) {
					foreach ($value as $key1 => $value1) {
						if ($key1 == 'vtiger_activity:time_start:Emails_Time_Start:time_start:T') {
							unset($this->sec_module_columnslist[$module][$key][$key1]);
						}
					}
				}
			}
		}
		return true;
	}

	/**
	 *
	 * @param String $module
	 * @param type $blockIdList
	 * @param Array $currentFieldList
	 * @return Array
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
		$allColumnsListByBlocks = & $this->getColumnsListbyBlock($module, array_keys($this->module_list[$module]), true);
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
	 *  Array module_columnlist[ vtiger_fieldtablename:fieldcolname:module_fieldlabel1:fieldname:fieldtypeofdata]=fieldlabel
	 */
	public function getColumnsListbyBlock($module, $block, $group_res_by_block = false)
	{
		$adb = PearDatabase::getInstance();
		$log = vglobal('log');
		$current_user = vglobal('current_user');

		if (is_string($block))
			$block = explode(",", $block);
		$skipTalbes = array('vtiger_emaildetails', 'vtiger_attachments');

		$tabid = \includes\Modules::getModuleId($module);
		if ($module == 'Calendar') {
			$tabid = array('9', '16');
		}
		$params = array($tabid, $block);

		require('user_privileges/user_privileges_' . $current_user->id . '.php');
		//Security Check
		if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
			$sql = sprintf("select * from vtiger_field where vtiger_field.tabid in (%s) and vtiger_field.block in (%s) and vtiger_field.displaytype in (1,2,3,10) and vtiger_field.presence in (0,2) && tablename NOT IN (%s) ", generateQuestionMarks($tabid), generateQuestionMarks($block), generateQuestionMarks($skipTalbes));

			//fix for Ticket #4016
			if ($module == "Calendar")
				$sql.=" group by vtiger_field.fieldlabel order by sequence";
			else
				$sql.=" order by sequence";
		}
		else {

			$profileList = getCurrentUserProfileList();
			$sql = sprintf("select * from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid where vtiger_field.tabid in (%s)  and vtiger_field.block in (%s) and vtiger_field.displaytype in (1,2,3,10) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)", generateQuestionMarks($tabid), generateQuestionMarks($block));
			if (count($profileList) > 0) {
				$sql .= " and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ")";
				array_push($params, $profileList);
			}
			$sql .= ' and tablename NOT IN (' . generateQuestionMarks($skipTalbes) . ') ';

			//fix for Ticket #4016
			if ($module == "Calendar")
				$sql.=" group by vtiger_field.fieldlabel order by sequence";
			else
				$sql.=" group by vtiger_field.fieldid order by sequence";
		}
		array_push($params, $skipTalbes);

		$result = $adb->pquery($sql, $params);
		$noofrows = $adb->num_rows($result);
		for ($i = 0; $i < $noofrows; $i++) {
			$fieldtablename = $adb->query_result($result, $i, "tablename");
			$fieldcolname = $adb->query_result($result, $i, "columnname");
			$fieldname = $adb->query_result($result, $i, "fieldname");
			$fieldtype = $adb->query_result($result, $i, "typeofdata");
			$uitype = $adb->query_result($result, $i, "uitype");
			$fieldtype = explode("~", $fieldtype);
			$fieldtypeofdata = $fieldtype[0];
			$blockid = $adb->query_result($result, $i, "block");

			//Here we Changing the displaytype of the field. So that its criteria will be displayed correctly in Reports Advance Filter.
			$fieldtypeofdata = \vtlib\Functions::transformFieldTypeOfData($fieldtablename, $fieldcolname, $fieldtypeofdata);

			if ($uitype == 68 || $uitype == 59) {
				$fieldtypeofdata = 'V';
			}
			if ($fieldtablename == "vtiger_crmentity") {
				$fieldtablename = $fieldtablename . $module;
			}
			if ($fieldname == "assigned_user_id") {
				$fieldtablename = "vtiger_users" . $module;
				$fieldcolname = "user_name";
			}
			if ($fieldname == "assigned_user_id1") {
				$fieldtablename = "vtiger_usersRel1";
				$fieldcolname = "user_name";
			}

			$fieldlabel = $adb->query_result($result, $i, "fieldlabel");
			if ($module == 'Emails' && $fieldlabel == 'Date & Time Sent') {
				$fieldlabel = 'Date Sent';
				$fieldtypeofdata = 'D';
			}
			$fieldlabel1 = str_replace(" ", "__", $fieldlabel);
			$optionvalue = $fieldtablename . ":" . $fieldcolname . ":" . $module . "__" . $fieldlabel1 . ":" . $fieldname . ":" . $fieldtypeofdata;

			$adv_rel_field_tod_value = '$' . $module . '#' . $fieldname . '$' . "::" . \includes\Language::translate($module, $module) . " " . \includes\Language::translate($fieldlabel, $module);
			if (!is_array($this->adv_rel_fields[$fieldtypeofdata]) ||
				!in_array($adv_rel_field_tod_value, $this->adv_rel_fields[$fieldtypeofdata])) {
				$this->adv_rel_fields[$fieldtypeofdata][] = $adv_rel_field_tod_value;
			}
			//added to escape attachments fields in Reports as we have multiple attachments
			if ($module == 'HelpDesk' && $fieldname == 'filename')
				continue;

			if (is_string($block) || $group_res_by_block == false) {
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
	 *  It accepts the repordid as argument
	 */
	public function getSelectedStandardCriteria($reportid)
	{
		$adb = PearDatabase::getInstance();
		$sSQL = "select vtiger_reportdatefilter.* from vtiger_reportdatefilter inner join vtiger_report on vtiger_report.reportid = vtiger_reportdatefilter.datefilterid where vtiger_report.reportid=?";
		$result = $adb->pquery($sSQL, array($reportid));
		$selectedstdfilter = $adb->fetch_array($result);

		$this->stdselectedcolumn = $selectedstdfilter["datecolumnname"];
		$this->stdselectedfilter = $selectedstdfilter["datefilter"];

		if ($selectedstdfilter["datefilter"] == "custom") {
			if ($selectedstdfilter["startdate"] != "0000-00-00") {
				$startDateTime = new DateTimeField($selectedstdfilter["startdate"] . ' ' . date('H:i:s'));
				$this->startdate = $startDateTime->getDisplayDate();
			}
			if ($selectedstdfilter["enddate"] != "0000-00-00") {
				$endDateTime = new DateTimeField($selectedstdfilter["enddate"] . ' ' . date('H:i:s'));
				$this->enddate = $endDateTime->getDisplayDate();
			}
		}
	}

	/** Function to get the combo values for the standard filter
	 *  This function get the combo values for the standard filter for the given vtiger_report
	 *  and return a HTML string
	 */
	public function getSelectedStdFilterCriteria($selecteddatefilter = "")
	{
		global $mod_strings;

		$datefiltervalue = Array("custom", "prevfy", "thisfy", "nextfy", "prevfq", "thisfq", "nextfq",
			"yesterday", "today", "tomorrow", "lastweek", "thisweek", "nextweek", "lastmonth", "thismonth",
			"nextmonth", "last7days", "last30days", "last60days", "last90days", "last120days",
			"next30days", "next60days", "next90days", "next120days"
		);

		$datefilterdisplay = Array("Custom", "Previous FY", "Current FY", "Next FY", "Previous FQ", "Current FQ", "Next FQ", "Yesterday",
			"Today", "Tomorrow", "Last Week", "Current Week", "Next Week", "Last Month", "Current Month",
			"Next Month", "Last 7 Days", "Last 30 Days", "Last 60 Days", "Last 90 Days", "Last 120 Days",
			"Next 7 Days", "Next 30 Days", "Next 60 Days", "Next 90 Days", "Next 120 Days"
		);

		for ($i = 0; $i < count($datefiltervalue); $i++) {
			if ($selecteddatefilter == $datefiltervalue[$i]) {
				$sshtml .= "<option selected value='" . $datefiltervalue[$i] . "'>" . $mod_strings[$datefilterdisplay[$i]] . "</option>";
			} else {
				$sshtml .= "<option value='" . $datefiltervalue[$i] . "'>" . $mod_strings[$datefilterdisplay[$i]] . "</option>";
			}
		}

		return $sshtml;
	}

	/** Function to get the selected standard filter columns
	 *  This function returns the selected standard filter criteria
	 *  which is selected for vtiger_reports as an array
	 *  Array stdcriteria_list[fieldtablename:fieldcolname:module_fieldlabel1]=fieldlabel
	 */
	public function getStdCriteriaByModule($module)
	{
		$adb = PearDatabase::getInstance();
		$log = vglobal('log');
		$current_user = vglobal('current_user');
		require('user_privileges/user_privileges_' . $current_user->id . '.php');

		$tabid = \includes\Modules::getModuleId($module);
		foreach ($this->module_list[$module] as $key => $blockid) {
			$blockids[] = $blockid;
		}
		$blockids = implode(",", $blockids);

		$params = array($tabid, $blockids);
		if ($is_admin == true || $profileGlobalPermission[1] == 0 || $profileGlobalPermission[2] == 0) {
			//uitype 6 and 23 added for start_date,EndDate,Expected Close Date
			$sql = sprintf('select * from vtiger_field where vtiger_field.tabid=? and (vtiger_field.uitype =5 or vtiger_field.uitype = 6 or vtiger_field.uitype = 23 or vtiger_field.displaytype=2) and vtiger_field.block in () and vtiger_field.presence in (0,2) order by vtiger_field.sequence', generateQuestionMarks($block));
		} else {
			$profileList = getCurrentUserProfileList();
			$sql = sprintf('select * from vtiger_field inner join vtiger_tab on vtiger_tab.tabid = vtiger_field.tabid inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid  where vtiger_field.tabid=? and (vtiger_field.uitype =5 or vtiger_field.displaytype=2) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.block in (%s) and vtiger_field.presence in (0,2)', generateQuestionMarks($block));
			if (count($profileList) > 0) {
				$sql .= " and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ")";
				array_push($params, $profileList);
			}
			$sql .= ' order by vtiger_field.sequence';
		}

		$result = $adb->pquery($sql, $params);

		while ($criteriatyperow = $adb->fetch_array($result)) {
			$fieldtablename = $criteriatyperow["tablename"];
			$fieldcolname = $criteriatyperow["columnname"];
			$fieldlabel = $criteriatyperow["fieldlabel"];

			if ($fieldtablename == "vtiger_crmentity") {
				$fieldtablename = $fieldtablename . $module;
			}
			$fieldlabel1 = str_replace(" ", "__", $fieldlabel);
			$optionvalue = $fieldtablename . ":" . $fieldcolname . ":" . $module . "__" . $fieldlabel1;
			$stdcriteria_list[$optionvalue] = $fieldlabel;
		}

		$log->info("Reports :: StdfilterColumns->Successfully returned Stdfilter for" . $module);
		return $stdcriteria_list;
	}

	public function getEscapedColumns($selectedfields)
	{
		$fieldname = $selectedfields[3];
		if ($fieldname == "parent_id") {
			if ($this->primarymodule == "HelpDesk" && $selectedfields[0] == "vtiger_crmentityRelHelpDesk") {
				$querycolumn = "case vtiger_crmentityRelHelpDesk.setype when 'Accounts' then vtiger_accountRelHelpDesk.accountname when 'Contacts' then vtiger_contactdetailsRelHelpDesk.lastname End" . " '" . $selectedfields[2] . "', vtiger_crmentityRelHelpDesk.setype 'Entity_type'";
				return $querycolumn;
			}
			if ($this->primarymodule == "Products" || $this->secondarymodule == "Products") {
				$querycolumn = "case vtiger_crmentityRelProducts.setype when 'Accounts' then vtiger_accountRelProducts.accountname when 'Leads' then vtiger_leaddetailsRelProducts.lastname End" . " '" . $selectedfields[2] . "', vtiger_crmentityRelProducts.setype 'Entity_type'";
			}
			if ($this->primarymodule == "Calendar" || $this->secondarymodule == "Calendar") {
				$querycolumn = "case vtiger_crmentityRelCalendar.setype when 'Accounts' then vtiger_accountRelCalendar.accountname when 'Leads' then vtiger_leaddetailsRelCalendar.lastname End" . " '" . $selectedfields[2] . "', vtiger_crmentityRelCalendar.setype 'Entity_type'";
			}
		}
		return $querycolumn;
	}

	public function getaccesfield($module)
	{
		$current_user = vglobal('current_user');
		$adb = PearDatabase::getInstance();
		$access_fields = [];

		$profileList = getCurrentUserProfileList();
		$query = "select vtiger_field.fieldname from vtiger_field inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid where";
		$params = array();
		if ($module == "Calendar") {
			$query .= " vtiger_field.tabid in (9,16) and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)";
			if (count($profileList) > 0) {
				$query .= " and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ")";
				array_push($params, $profileList);
			}
			$query .= " group by vtiger_field.fieldid order by block,sequence";
		} else {
			array_push($params, $this->primodule, $this->secmodule);
			$query .= " vtiger_field.tabid in (select tabid from vtiger_tab where vtiger_tab.name in (?,?)) and vtiger_field.displaytype in (1,2,3) and vtiger_profile2field.visible=0 and vtiger_def_org_field.visible=0 and vtiger_field.presence in (0,2)";
			if (count($profileList) > 0) {
				$query .= " and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ")";
				array_push($params, $profileList);
			}
			$query .= " group by vtiger_field.fieldid order by block,sequence";
		}
		$result = $adb->pquery($query, $params);


		while ($collistrow = $adb->fetch_array($result)) {
			$access_fields[] = $collistrow["fieldname"];
		}
		return $access_fields;
	}

	/** Function to set the order of grouping and to find the columns responsible
	 *  to the grouping
	 *  This function accepts the vtiger_reportid as variable,sets the variable ascdescorder[] to the sort order and
	 *  returns the array array_list which has the column responsible for the grouping
	 *  Array array_list[0]=columnname
	 */
	public function getSelctedSortingColumns($reportid)
	{

		$adb = PearDatabase::getInstance();
		$log = vglobal('log');

		$sreportsortsql = "select vtiger_reportsortcol.* from vtiger_report";
		$sreportsortsql .= " inner join vtiger_reportsortcol on vtiger_report.reportid = vtiger_reportsortcol.reportid";
		$sreportsortsql .= " where vtiger_report.reportid =? order by vtiger_reportsortcol.sortcolid";

		$result = $adb->pquery($sreportsortsql, array($reportid));
		$noofrows = $adb->num_rows($result);

		for ($i = 0; $i < $noofrows; $i++) {
			$fieldcolname = $adb->query_result($result, $i, "columnname");
			$sort_values = $adb->query_result($result, $i, "sortorder");
			$this->ascdescorder[] = $sort_values;
			$array_list[] = $fieldcolname;
		}

		$log->info("Reports :: Successfully returned getSelctedSortingColumns");
		return $array_list;
	}

	/** Function to get the selected columns list for a selected vtiger_report
	 *  This function accepts the vtiger_reportid as the argument and get the selected columns
	 *  for the given vtiger_reportid and it forms a combo lists and returns
	 *  HTML of the combo values
	 */
	public function getSelectedColumnsList($reportid)
	{
		$adb = PearDatabase::getInstance();
		$log = LoggerManager::getInstance();
		$current_user = vglobal('current_user');

		$ssql = "select vtiger_selectcolumn.* from vtiger_report inner join vtiger_selectquery on vtiger_selectquery.queryid = vtiger_report.queryid";
		$ssql .= " left join vtiger_selectcolumn on vtiger_selectcolumn.queryid = vtiger_selectquery.queryid";
		$ssql .= " where vtiger_report.reportid = ?";
		$ssql .= " order by vtiger_selectcolumn.columnindex";
		$result = $adb->pquery($ssql, array($reportid));
		$permitted_fields = [];

		$selected_mod = explode(':', $this->secmodule);
		array_push($selected_mod, $this->primodule);

		$inventoryModules = getInventoryModules();
		while ($columnslistrow = $adb->fetch_array($result)) {
			$fieldname = '';
			$fieldcolname = $columnslistrow['columnname'];

			$selmod_field_disabled = true;
			foreach ($selected_mod as $smod) {
				if ((stripos($fieldcolname, ':' . $smod . '__') > -1) && \includes\Modules::isModuleActive($smod)) {
					$selmod_field_disabled = false;
					break;
				}
			}
			if ($selmod_field_disabled == false) {
				list($tablename, $colname, $module_field, $fieldname, $single) = split(':', $fieldcolname);
				require('user_privileges/user_privileges_' . $current_user->id . '.php');
				list($module, $field) = explode('__', $module_field);
				if (sizeof($permitted_fields) == 0 && $is_admin == false && $profileGlobalPermission[1] == 1 && $profileGlobalPermission[2] == 1) {
					$permitted_fields = $this->getaccesfield($module);
				}
				$querycolumns = $this->getEscapedColumns($selectedfields);
				$fieldlabel = trim(str_replace($module, ' ', $module_field));
				$mod_arr = explode('__', $fieldlabel);
				$mod = ($mod_arr[0] == '') ? $module : $mod_arr[0];
				$fieldlabel = trim(str_replace('__', ' ', $fieldlabel));
				//modified code to support i18n issue
				$mod_lbl = \includes\Language::translate($mod, $module); //module
				$fld_lbl = \includes\Language::translate($fieldlabel, $module); //fieldlabel
				$fieldlabel = $mod_lbl . ' ' . $fld_lbl;
				if (in_array($mod, $inventoryModules) && $fieldname == 'serviceid') {
					$shtml .= "<option permission='yes' value=\"" . $fieldcolname . "\">" . $fieldlabel . "</option>";
				} else if (CheckFieldPermission($fieldname, $mod) != 'true' && $colname != "crmid") {
					$shtml .= "<option permission='no' value=\"" . $fieldcolname . "\" disabled = 'true'>" . $fieldlabel . "</option>";
				} else {
					$shtml .= "<option permission='yes' value=\"" . $fieldcolname . "\">" . $fieldlabel . "</option>";
				}
			}
			//end
		}
		$log->info("ReportRun :: Successfully returned getQueryColumnsList" . $reportid);
		return $shtml;
	}

	public function getAdvancedFilterList($reportid)
	{
		$adb = PearDatabase::getInstance();
		global $modules;
		$log = vglobal('log');
		$current_user = vglobal('current_user');

		$advft_criteria = array();

		$sql = 'SELECT * FROM vtiger_relcriteria_grouping WHERE queryid = ? ORDER BY groupid';
		$groupsresult = $adb->pquery($sql, array($reportid));

		$i = 1;
		$j = 0;
		while ($relcriteriagroup = $adb->fetch_array($groupsresult)) {
			$groupId = $relcriteriagroup["groupid"];
			$groupCondition = $relcriteriagroup["group_condition"];

			$ssql = 'select vtiger_relcriteria.* from vtiger_report
						inner join vtiger_relcriteria on vtiger_relcriteria.queryid = vtiger_report.queryid
						left join vtiger_relcriteria_grouping on vtiger_relcriteria.queryid = vtiger_relcriteria_grouping.queryid
								and vtiger_relcriteria.groupid = vtiger_relcriteria_grouping.groupid';
			$ssql.= " where vtiger_report.reportid = ? && vtiger_relcriteria.groupid = ? order by vtiger_relcriteria.columnindex";

			$result = $adb->pquery($ssql, array($reportid, $groupId));
			$noOfColumns = $adb->num_rows($result);
			if ($noOfColumns <= 0)
				continue;

			while ($relcriteriarow = $adb->fetch_array($result)) {
				$columnIndex = $relcriteriarow["columnindex"];
				$criteria = array();
				$criteria['columnname'] = $relcriteriarow["columnname"];
				$criteria['comparator'] = $relcriteriarow["comparator"];
				$advfilterval = $relcriteriarow["value"];
				$col = explode(":", $relcriteriarow["columnname"]);

				$moduleFieldLabel = $col[2];
				$fieldName = $col[3];

				list($module, $fieldLabel) = explode('__', $moduleFieldLabel, 2);
				$fieldInfo = getFieldByReportLabel($module, $fieldLabel);
				$fieldType = null;
				if (!empty($fieldInfo)) {
					$field = WebserviceField::fromArray($adb, $fieldInfo);
					$fieldType = $field->getFieldDataType();
				}
				if ($fieldType == 'currency') {
					if ($field->getUIType() == '71') {
						$advfilterval = CurrencyField::convertToUserFormat($advfilterval, $current_user);
					} else if ($field->getUIType() == '72') {
						$advfilterval = CurrencyField::convertToUserFormat($advfilterval, $current_user, true);
					}
				}

				$temp_val = explode(",", $relcriteriarow["value"]);
				if ($col[4] == 'D' || ($col[4] == 'T' && $col[1] != 'time_start' && $col[1] != 'time_end') || ($col[4] == 'DT')) {
					$val = [];
					for ($x = 0; $x < count($temp_val); $x++) {
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
					$advfilterval = implode(",", $val);
				}

				//In vtiger6 report filter conditions, if the value has "(double quotes) then it is failed.
				$criteria['value'] = Vtiger_Util_Helper::toSafeHTML(decode_html($advfilterval));
				$criteria['column_condition'] = $relcriteriarow["column_condition"];

				$advft_criteria[$relcriteriarow['groupid']]['columns'][$j] = $criteria;
				$advft_criteria[$relcriteriarow['groupid']]['condition'] = $groupCondition;
				$j++;
			}
			$i++;
		}
		// Clear the condition (and/or) for last group, if any.
		if (!empty($advft_criteria[$i - 1]['condition']))
			$advft_criteria[$i - 1]['condition'] = '';
		$this->advft_criteria = $advft_criteria;
		$log->info("Reports :: Successfully returned getAdvancedFilterList");
		return true;
	}
	//<<<<<<<<advanced filter>>>>>>>>>>>>>>

	/** Function to get the list of vtiger_report folders when Save and run  the vtiger_report
	 *  This function gets the vtiger_report folders from database and form
	 *  a combo values of the folders and return
	 *  HTML of the combo values
	 */
	public function sgetRptFldrSaveReport()
	{
		$adb = PearDatabase::getInstance();
		$log = vglobal('log');

		$sql = "select * from vtiger_reportfolder order by folderid";
		$result = $adb->pquery($sql, array());
		$reportfldrow = $adb->fetch_array($result);
		$x = 0;
		do {
			$shtml .= "<option value='" . $reportfldrow['folderid'] . "'>" . $reportfldrow['foldername'] . "</option>";
		} while ($reportfldrow = $adb->fetch_array($result));

		$log->info("Reports :: Successfully returned sgetRptFldrSaveReport");
		return $shtml;
	}

	/** Function to get the column to total vtiger_fields in Reports
	 *  This function gets columns to total vtiger_field
	 *  and generated the html for that vtiger_fields
	 *  It returns the HTML of the vtiger_fields along with the check boxes
	 */
	public function sgetColumntoTotal($primarymodule, $secondarymodule)
	{
		$options = [];
		$options [] = $this->sgetColumnstoTotalHTML($primarymodule, 0);
		if (!empty($secondarymodule)) {
			for ($i = 0; $i < count($secondarymodule); $i++) {
				$options [] = $this->sgetColumnstoTotalHTML($secondarymodule[$i], ($i + 1));
			}
		}
		return $options;
	}

	/** Function to get the selected columns of total vtiger_fields in Reports
	 *  This function gets selected columns of total vtiger_field
	 *  and generated the html for that vtiger_fields
	 *  It returns the HTML of the vtiger_fields along with the check boxes
	 */
	public function sgetColumntoTotalSelected($primarymodule, $secondarymodule, $reportid)
	{
		$adb = PearDatabase::getInstance();
		$log = vglobal('log');
		$options = [];
		if ($reportid != "") {
			$ssql = "select vtiger_reportsummary.* from vtiger_reportsummary inner join vtiger_report on vtiger_report.reportid = vtiger_reportsummary.reportsummaryid where vtiger_report.reportid=?";
			$result = $adb->pquery($ssql, array($reportid));
			if ($result) {
				$reportsummaryrow = $adb->fetch_array($result);

				do {
					$this->columnssummary[] = $reportsummaryrow["columnname"];
				} while ($reportsummaryrow = $adb->fetch_array($result));
			}
		}
		$options [] = $this->sgetColumnstoTotalHTML($primarymodule, 0);
		if ($secondarymodule != "") {
			$secondarymodule = explode(":", $secondarymodule);
			for ($i = 0; $i < count($secondarymodule); $i++) {
				$options [] = $this->sgetColumnstoTotalHTML($secondarymodule[$i], ($i + 1));
			}
		}

		$log->info("Reports :: Successfully returned sgetColumntoTotalSelected");
		return $options;
	}

	/** Function to form the HTML for columns to total
	 *  This function formulates the HTML format of the
	 *  vtiger_fields along with four checkboxes
	 *  It returns the HTML of the vtiger_fields along with the check boxes
	 */
	public function sgetColumnstoTotalHTML($module)
	{
		//retreive the vtiger_tabid
		$adb = PearDatabase::getInstance();

		$log = LoggerManager::getInstance();
		$currentUser = Users_Privileges_Model::getCurrentUserModel();
		$privileges = Vtiger_Util_Helper::getUserPrivilegesFile($currentUser->getId());


		$tabid = \includes\Modules::getModuleId($module);
		$escapedchars = Array('__SUM', '__AVG', '__MIN', '__MAX');
		$sparams = array($tabid);
		if ($privileges['is_admin'] == true || $privileges['profile_global_permission'][1] == 0 || $privileges['profile_global_permission'][2] == 0) {
			$ssql = "select * from vtiger_field inner join vtiger_tab on vtiger_tab.tabid = vtiger_field.tabid where vtiger_field.uitype != 50 and vtiger_field.tabid=? and vtiger_field.displaytype in (1,2,3) and vtiger_field.presence in (0,2) ";
		} else {
			$profileList = getCurrentUserProfileList();
			$ssql = "select * from vtiger_field inner join vtiger_tab on vtiger_tab.tabid = vtiger_field.tabid inner join vtiger_def_org_field on vtiger_def_org_field.fieldid=vtiger_field.fieldid inner join vtiger_profile2field on vtiger_profile2field.fieldid=vtiger_field.fieldid  where vtiger_field.uitype != 50 and vtiger_field.tabid=? and vtiger_field.displaytype in (1,2,3) and vtiger_def_org_field.visible=0 and vtiger_profile2field.visible=0 and vtiger_field.presence in (0,2)";
			if (count($profileList) > 0) {
				$ssql .= " and vtiger_profile2field.profileid in (" . generateQuestionMarks($profileList) . ")";
				array_push($sparams, $profileList);
			}
		}

		//Added to avoid display the Related fields (Account name,Vandor name,product name, etc) in Report Calculations(SUM,AVG..)
		switch ($tabid) {
			case 4://Contacts
				$ssql.= " and vtiger_field.fieldname not in ('account_id')";
				break;
			case 6://Accounts
				$ssql.= " and vtiger_field.fieldname not in ('account_id')";
				break;
			case 9://Calandar
				$ssql.= " and vtiger_field.fieldname not in ('parent_id','contact_id')";
				break;
			case 13://Trouble tickets(HelpDesk)
				$ssql.= " and vtiger_field.fieldname not in ('parent_id','product_id')";
				break;
			case 14://Products
				$ssql.= " and vtiger_field.fieldname not in ('vendor_id','product_id')";
				break;
			case 21://Purchase Order
				$ssql.= " and vtiger_field.fieldname not in ('contact_id','vendor_id','currency_id')";
				break;
			case 26://Campaigns
				$ssql.= " and vtiger_field.fieldname not in ('product_id')";
				break;
		}

		$ssql.= " order by sequence";

		$result = $adb->pquery($ssql, $sparams);
		$columntototalrow = $adb->fetch_array($result);
		$options_list = [];
		do {
			$typeofdata = explode("~", $columntototalrow["typeofdata"]);

			if ($typeofdata[0] == "N" || $typeofdata[0] == "I" || ($typeofdata[0] == "NN" && !empty($typeofdata[2]))) {
				$options = [];
				if (isset($this->columnssummary)) {
					$selectedcolumn = "";
					$selectedcolumn1 = "";

					for ($i = 0; $i < count($this->columnssummary); $i++) {
						$selectedcolumnarray = explode(":", $this->columnssummary[$i]);
						$selectedcolumn = $selectedcolumnarray[1] . ":" . $selectedcolumnarray[2] . ":" .
							str_replace($escapedchars, "", $selectedcolumnarray[3]);

						if ($selectedcolumn != $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . str_replace(" ", "__", $columntototalrow['fieldlabel'])) {
							$selectedcolumn = "";
						} else {
							$selectedcolumn1[$selectedcolumnarray[4]] = $this->columnssummary[$i];
						}
					}
					if (!AppRequest::isEmpty('record')) {
						$options['label'][] = \includes\Language::translate($columntototalrow['tablabel'], $columntototalrow['tablabel']) . ' -' . \includes\Language::translate($columntototalrow['fieldlabel'], $columntototalrow['tablabel']);
					}

					$columntototalrow['fieldlabel'] = str_replace(" ", "__", $columntototalrow['fieldlabel']);
					$options [] = \includes\Language::translate($columntototalrow['tablabel'], $columntototalrow['tablabel']) . ' - ' . \includes\Language::translate($columntototalrow['fieldlabel'], $columntototalrow['tablabel']);
					if ($selectedcolumn1[2] == "cb:" . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . "__SUM:2") {
						$options [] = '<input checked name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '__SUM:2" type="checkbox" value="">';
					} else {
						$options [] = '<input name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '__SUM:2" type="checkbox" value="">';
					}
					if ($selectedcolumn1[3] == "cb:" . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . "__AVG:3") {
						$options [] = '<input checked name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '__AVG:3" type="checkbox" value="">';
					} else {
						$options [] = '<input name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '__AVG:3" type="checkbox" value="">';
					}

					if ($selectedcolumn1[4] == "cb:" . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . "__MIN:4") {
						$options [] = '<input checked name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '__MIN:4" type="checkbox" value="">';
					} else {
						$options [] = '<input name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '__MIN:4" type="checkbox" value="">';
					}

					if ($selectedcolumn1[5] == "cb:" . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . "__MAX:5") {
						$options [] = '<input checked name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '__MAX:5" type="checkbox" value="">';
					} else {
						$options [] = '<input name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '__MAX:5" type="checkbox" value="">';
					}
				} else {
					$options [] = \includes\Language::translate($columntototalrow['tablabel'], $columntototalrow['tablabel']) . ' - ' . \includes\Language::translate($columntototalrow['fieldlabel'], $columntototalrow['tablabel']);
					$options [] = '<input name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '__SUM:2" type="checkbox" value="">';
					$options [] = '<input name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '__AVG:3" type="checkbox" value="" >';
					$options [] = '<input name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '__MIN:4"type="checkbox" value="" >';
					$options [] = '<input name="cb:' . $columntototalrow['tablename'] . ':' . $columntototalrow['columnname'] . ':' . $columntototalrow['fieldlabel'] . '__MAX:5" type="checkbox" value="" >';
				}
				$options_list [] = $options;
			}
		} while ($columntototalrow = $adb->fetch_array($result));

		$log->info("Reports :: Successfully returned sgetColumnstoTotalHTML");
		return $options_list;
	}
}

/** Function to get the primary module list in vtiger_reports
 *  This function generates the list of primary modules in vtiger_reports
 *  and returns an array of permitted modules
 */
function getReportsModuleList($focus)
{
	$adb = PearDatabase::getInstance();
	global $app_list_strings;
	//global $report_modules;
	global $mod_strings;
	$modules = [];
	foreach ($focus->module_list as $key => $value) {
		if (isPermitted($key, 'index') == "yes") {
			$count_flag = 1;
			$modules [$key] = \includes\Language::translate($key, $key);
		}
	}
	asort($modules);
	return $modules;
}

/** Function to get the Related module list in vtiger_reports
 *  This function generates the list of secondary modules in vtiger_reports
 *  and returns the related module as an Array
 */
function getReportRelatedModules($module, $focus)
{
	global $app_list_strings;
	global $related_modules;
	global $mod_strings;
	$optionhtml = [];
	if (\includes\Modules::isModuleActive($module)) {
		if (!empty($focus->related_modules[$module])) {
			foreach ($focus->related_modules[$module] as $rel_modules) {
				if (isPermitted($rel_modules, 'index') == "yes") {
					$optionhtml [] = $rel_modules;
				}
			}
		}
	}


	return $optionhtml;
}

function updateAdvancedCriteria($reportid, $advft_criteria, $advft_criteria_groups)
{

	$adb = PearDatabase::getInstance();
	$log = vglobal('log');

	$idelrelcriteriasql = "delete from vtiger_relcriteria where queryid=?";
	$idelrelcriteriasqlresult = $adb->pquery($idelrelcriteriasql, array($reportid));

	$idelrelcriteriagroupsql = "delete from vtiger_relcriteria_grouping where queryid=?";
	$idelrelcriteriagroupsqlresult = $adb->pquery($idelrelcriteriagroupsql, array($reportid));

	if (empty($advft_criteria))
		return;

	foreach ($advft_criteria as $column_index => $column_condition) {

		if (empty($column_condition))
			continue;

		$adv_filter_column = $column_condition["columnname"];
		$adv_filter_comparator = $column_condition["comparator"];
		$adv_filter_value = $column_condition["value"];
		$adv_filter_column_condition = $column_condition["columncondition"];
		$adv_filter_groupid = $column_condition["groupid"];

		$column_info = explode(":", $adv_filter_column);
		$moduleFieldLabel = $column_info[2];
		$fieldName = $column_info[3];

		list($module, $fieldLabel) = explode('__', $moduleFieldLabel, 2);
		$fieldInfo = getFieldByReportLabel($module, $fieldLabel);
		$fieldType = null;
		if (!empty($fieldInfo)) {
			$field = WebserviceField::fromArray($adb, $fieldInfo);
			$fieldType = $field->getFieldDataType();
		}
		if ($fieldType == 'currency') {
			// Some of the currency fields like Unit Price, Total, Sub-total etc of Inventory modules, do not need currency conversion
			if ($field->getUIType() == '72') {
				$adv_filter_value = CurrencyField::convertToDBFormat($adv_filter_value, null, true);
			} else {
				$adv_filter_value = CurrencyField::convertToDBFormat($adv_filter_value);
			}
		}

		$temp_val = explode(",", $adv_filter_value);
		if (($column_info[4] == 'D' || ($column_info[4] == 'T' && $column_info[1] != 'time_start' && $column_info[1] != 'time_end') || ($column_info[4] == 'DT')) && ($column_info[4] != '' && $adv_filter_value != '' )) {
			$val = [];
			for ($x = 0; $x < count($temp_val); $x++) {
				if (trim($temp_val[$x]) != '') {
					$date = new DateTimeField(trim($temp_val[$x]));
					if ($column_info[4] == 'D') {
						$val[$x] = DateTimeField::convertToUserFormat(
								trim($temp_val[$x]));
					} elseif ($column_info[4] == 'DT') {
						$val[$x] = $date->getDBInsertDateTimeValue();
					} else {
						$val[$x] = $date->getDBInsertTimeValue();
					}
				}
			}
			$adv_filter_value = implode(",", $val);
		}

		$irelcriteriasql = "insert into vtiger_relcriteria(QUERYID,COLUMNINDEX,COLUMNNAME,COMPARATOR,VALUE,GROUPID,COLUMN_CONDITION) values (?,?,?,?,?,?,?)";
		$irelcriteriaresult = $adb->pquery($irelcriteriasql, array($reportid, $column_index, $adv_filter_column, $adv_filter_comparator, $adv_filter_value, $adv_filter_groupid, $adv_filter_column_condition));

		// Update the condition expression for the group to which the condition column belongs
		$groupConditionExpression = '';
		if (!empty($advft_criteria_groups[$adv_filter_groupid]["conditionexpression"])) {
			$groupConditionExpression = $advft_criteria_groups[$adv_filter_groupid]["conditionexpression"];
		}
		$groupConditionExpression = $groupConditionExpression . ' ' . $column_index . ' ' . $adv_filter_column_condition;
		$advft_criteria_groups[$adv_filter_groupid]["conditionexpression"] = $groupConditionExpression;
	}

	foreach ($advft_criteria_groups as $group_index => $group_condition_info) {

		if (empty($group_condition_info))
			continue;
		if (empty($group_condition_info["conditionexpression"]))
			continue; // Case when the group doesn't have any column criteria

		$irelcriteriagroupsql = "insert into vtiger_relcriteria_grouping(GROUPID,QUERYID,GROUP_CONDITION,CONDITION_EXPRESSION) values (?,?,?,?)";
		$irelcriteriagroupresult = $adb->pquery($irelcriteriagroupsql, array($group_index, $reportid, $group_condition_info["groupcondition"], $group_condition_info["conditionexpression"]));
	}
}
