<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
include_once dirname(__FILE__) . '/FetchRecord.php';

include_once 'include/Webservices/History.php';

class Mobile_WS_History extends Mobile_WS_FetchRecord {
	
	function process(Mobile_API_Request $request) {
		$current_user = $this->getActiveUser();
		
		$page = intval($request->get('page', 0));
		$module = $request->get('module', '');
		$record = $request->get('record', '');
		$mode   = $request->get('mode', '');
		
		$options = array(
			'module' => $module,
			'record' => $record,
			'mode'   => $mode,
			'page'   => $page
		);
		
		$historyItems = vtws_history($options, $current_user);
		
		$this->resolveReferences($historyItems, $current_user);
		
		$result = array('history' => $historyItems);
		
		$response = new Mobile_API_Response();
		$response->setResult($result);
		return $response;
	}
	
	protected function resolveReferences(&$items, $user) {
		global $current_user; 
		if (!isset($current_user)) $current_user = $user; /* Required in getEntityFieldNameDisplay */
		
		foreach ($items as &$item) {
			$item['modifieduser'] = $this->fetchResolvedValueForId($item['modifieduser'], $user);
			$item['label'] = $this->fetchRecordLabelForId($item['id'], $user);
			unset($item);
		}
	}
	
	protected function fetchResolvedValueForId($id, $user) {
		$label = $this->fetchRecordLabelForId($id, $user);
		return array('value' => $id, 'label'=>$label);
	}
}