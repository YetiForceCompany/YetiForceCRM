<?php
/* * *******************************************************************************
 * * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ****************************************************************************** */
require_once('modules/CustomView/CustomView.php');

class ListViewSession
{

	public $module = null;
	public $viewname = null;
	public $start = null;
	public $sorder = null;
	public $sortby = null;
	public $page_view = null;

	/*	 * initializes ListViewSession
	 * Portions created by vtigerCRM are Copyright (C) vtigerCRM.
	 * All Rights Reserved.
	 */

	public function __construct()
	{

		$currentModule = vglobal('currentModule');
		\App\Log::trace("Entering ListViewSession() method ...");

		$this->module = $currentModule;
		$this->sortby = 'ASC';
		$this->start = 1;
	}

	public function getRequestStartPage()
	{
		$start = AppRequest::get('start');
		if (!is_numeric($start)) {
			$start = 1;
		}
		if ($start < 1) {
			$start = 1;
		}
		$start = ceil($start);
		return $start;
	}

	public static function getListViewNavigation($currentRecordId)
	{
		$adb = PearDatabase::getInstance();

		$currentModule = vglobal('currentModule');
		$current_user = vglobal('current_user');
		$listMaxEntriesPerPage = AppConfig::main('list_max_entries_per_page');

		$reUseData = false;
		$displayBufferRecordCount = 10;
		$bufferRecordCount = 15;
		if ($currentModule == 'Documents') {
			$sql = "select folderid from vtiger_notes where notesid=?";
			$params = array($currentRecordId);
			$result = $adb->pquery($sql, $params);
			$folderId = $adb->query_result($result, 0, 'folderid');
		}
		$viewId = App\CustomView::getInstance($currentModule)->getViewId();
		if (!empty($_SESSION[$currentModule . '_DetailView_Navigation' . $viewId])) {
			$recordNavigationInfo = \App\Json::decode($_SESSION[$currentModule . '_DetailView_Navigation' . $viewId]);
			$pageNumber = 0;
			if (count($recordNavigationInfo) == 1) {
				foreach ($recordNavigationInfo as $recordIdList) {
					if (in_array($currentRecordId, $recordIdList)) {
						$reUseData = true;
					}
				}
			} else {
				$recordList = [];
				$recordPageMapping = [];
				foreach ($recordNavigationInfo as $start => $recordIdList) {
					foreach ($recordIdList as $index => $recordId) {
						$recordList[] = $recordId;
						$recordPageMapping[$recordId] = $start;
						if ($recordId == $currentRecordId) {
							$searchKey = count($recordList) - 1;
							AppRequest::set('start', $start);
						}
					}
				}
				$countRecordList = count($recordList);
				if ($searchKey > $displayBufferRecordCount - 1 && $searchKey < $countRecordList - $displayBufferRecordCount) {
					$reUseData = true;
				}
			}
		}
		if ($reUseData === false && !empty($list_query)) {
			$recordNavigationInfo = [];
			if (!AppRequest::isEmpty('start')) {
				$start = ListViewSession::getRequestStartPage();
			} else {
				$start = App\CustomView::getCurrentPage($currentModule, $viewId);
			}
			$startRecord = (($start - 1) * $listMaxEntriesPerPage) - $bufferRecordCount;
			if ($startRecord < 0) {
				$startRecord = 0;
			}

			$instance = CRMEntity::getInstance($currentModule);
			$instance->getNonAdminAccessControlQuery($currentModule, $current_user);
			vtlib_setup_modulevars($currentModule, $instance);
			if ($currentModule == 'Documents' && !empty($folderId)) {
				$list_query = preg_replace("/[\n\r\s]+/", " ", $list_query);
				$list_query = explode('ORDER BY', $list_query);
				$default_orderby = $list_query[1];
				$list_query = $list_query[0];
				$list_query .= " && vtiger_notes.folderid='$folderId'";
				$order_by = $instance->getOrderByForFolder($folderId);
				$sorder = $instance->getSortOrderForFolder($folderId);
				$tablename = getTableNameForField($currentModule, $order_by);
				$tablename = (($tablename != '') ? ($tablename . ".") : '');

				if (!empty($order_by)) {
					$list_query .= sprintf(' ORDER BY %s%s %s', $tablename, $order_by, $sorder);
				} elseif (!empty($default_orderby)) {
					$list_query .= sprintf(' ORDER BY %s', $default_orderby);
				}
			}
			if ($start != 1) {
				$recordCount = ($listMaxEntriesPerPage * $start + $bufferRecordCount);
			} else {
				$recordCount = ($listMaxEntriesPerPage + $bufferRecordCount);
			}
			if ($adb->isPostgres()) {
				$list_query .= " OFFSET $startRecord LIMIT $recordCount";
			} else {
				$list_query .= " LIMIT $startRecord, $recordCount";
			}

			$resultAllCRMIDlist_query = $adb->pquery($list_query, []);
			$navigationRecordList = [];
			while ($forAllCRMID = $adb->fetch_array($resultAllCRMIDlist_query)) {
				$navigationRecordList[] = $forAllCRMID[$instance->table_index];
			}

			$pageCount = 0;
			$current = $start;
			if ($start == 1) {
				$firstPageRecordCount = $listMaxEntriesPerPage;
			} else {
				$firstPageRecordCount = $bufferRecordCount;
				$current -= 1;
			}

			$searchKey = array_search($currentRecordId, $navigationRecordList);
			$recordNavigationInfo = [];
			if ($searchKey !== false) {
				foreach ($navigationRecordList as $index => $recordId) {
					if (!is_array($recordNavigationInfo[$current])) {
						$recordNavigationInfo[$current] = [];
					}
					if ($index == $firstPageRecordCount || $index == ($firstPageRecordCount + $pageCount * $listMaxEntriesPerPage)) {
						$current++;
						$pageCount++;
					}
					$recordNavigationInfo[$current][] = $recordId;
				}
			}
			$_SESSION[$currentModule . '_DetailView_Navigation' . $viewId] = \App\Json::encode($recordNavigationInfo);
		}
		return $recordNavigationInfo;
	}

	public function getRequestCurrentPage($currentModule, $query, $viewid, $queryMode = false)
	{
		$adb = PearDatabase::getInstance();
		$start = 1;
		if (AppRequest::has('query') && AppRequest::get('query') == 'true' && AppRequest::get('start') != 'last') {
			return ListViewSession::getRequestStartPage();
		}
		if (!AppRequest::isEmpty('start')) {
			$start = AppRequest::get('start');
			if ($start == 'last') {
				$count_result = $adb->query(vtlib\Functions::mkCountQuery($query));
				$noofrows = $adb->query_result($count_result, 0, "count");
				if ($noofrows > 0) {
					$start = ceil($noofrows / AppConfig::main('list_max_entries_per_page'));
				}
			}
			if (!is_numeric($start)) {
				$start = 1;
			} elseif ($start < 1) {
				$start = 1;
			}
			$start = ceil($start);
		} else if (!empty($_SESSION['lvs'][$currentModule][$viewid]['start'])) {
			$start = $_SESSION['lvs'][$currentModule][$viewid]['start'];
		}
		if (!$queryMode) {
			$_SESSION['lvs'][$currentModule][$viewid]['start'] = intval($start);
		}
		return $start;
	}
}
