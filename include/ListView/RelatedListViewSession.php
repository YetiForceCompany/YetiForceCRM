<?php
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/

require_once('include/logging.php');
require_once('include/ListView/ListViewSession.php');

/**initializes Related ListViewSession
 * Portions created by vtigerCRM are Copyright (C) vtigerCRM.
 * All Rights Reserved.
 */
class RelatedListViewSession {

	var $module = null;
	var $start = null;
	var $sorder = null;
	var $sortby = null;
	var $page_view = null;

	function RelatedListViewSession() {
		global $log,$currentModule;
		$log->debug("Entering RelatedListViewSession() method ...");

		$this->module = $currentModule;
		$this->start =1;
	}

	public static function addRelatedModuleToSession($relationId, $header) {
		global $currentModule;
		$_SESSION['relatedlist'][$currentModule][$relationId] = $header;
		$start = RelatedListViewSession::getRequestStartPage();
		RelatedListViewSession::saveRelatedModuleStartPage($relationId, $start);
	}

	public static function removeRelatedModuleFromSession($relationId, $header) {
		global $currentModule;

		unset($_SESSION['relatedlist'][$currentModule][$relationId]);
	}

	public static function getRelatedModulesFromSession() {
		global $currentModule;

		$allRelatedModuleList = isPresentRelatedLists($currentModule);
		$moduleList = array();
		if(is_array($_SESSION['relatedlist'][$currentModule])){
			foreach ($allRelatedModuleList as $relationId=>$label) {
				if(array_key_exists($relationId, $_SESSION['relatedlist'][$currentModule])){
					$moduleList[] = $_SESSION['relatedlist'][$currentModule][$relationId];
				}
			}
		}
		return $moduleList;
	}

	public static function saveRelatedModuleStartPage($relationId, $start) {
		global $currentModule;

		$_SESSION['rlvs'][$currentModule][$relationId]['start'] = $start;
	}

	public static function getCurrentPage($relationId) {
		global $currentModule;

		if(!empty($_SESSION['rlvs'][$currentModule][$relationId]['start'])){
			return $_SESSION['rlvs'][$currentModule][$relationId]['start'];
		}
		return 1;
	}

	public static function getRequestStartPage(){
		$start = $_REQUEST['start'];
		if(!is_numeric($start)){
			$start = 1;
		}
		if($start < 1){
			$start = 1;
		}
		$start = ceil($start);
		return $start;
	}

	public static function getRequestCurrentPage($relationId, $query) {
		global $list_max_entries_per_page, $adb;

		$start = 1;
		if(!empty($_REQUEST['start'])){
			$start = $_REQUEST['start'];
			if($start == 'last'){
				$count_result = $adb->query( Vtiger_Functions::mkCountQuery( $query));
				$noofrows = $adb->query_result($count_result,0,"count");
				if($noofrows > 0){
					$start = ceil($noofrows/$list_max_entries_per_page);
				}
			}
			if(!is_numeric($start)){
				$start = 1;
			}elseif($start < 1){
				$start = 1;
			}
			$start = ceil($start);
		}else {
			$start = RelatedListViewSession::getCurrentPage($relationId);
		}
		return $start;
	}

}
?>