<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
class Mobile_WS_FetchModuleFilters extends Mobile_WS_Controller {
	
	function process(Mobile_API_Request $request) {
		$response = new Mobile_API_Response();

		$module = $request->get('module');
		$current_user = $this->getActiveUser();
		
		$result = array();
		
		$filters = $this->getModuleFilters($module, $current_user);
		$yours = array();
		$others= array();
		if(!empty($filters)) {
			foreach($filters as $filter) {
				if($filter['userName'] == $current_user->column_fields['user_name']) {
					$yours[] = $filter;
				} else {
					$others[]= $filter;
				}
			}
		}
		
		$result['filters'] = array('yours' => $yours, 'others' => $others);
		$response->setResult($result);

		return $response;
	}

	protected function getModuleFilters($moduleName, $user) {
		
		$filters = array();
		
		global $adb;
		$sql = "SELECT vtiger_customview.*, vtiger_users.user_name FROM vtiger_customview 
			INNER JOIN vtiger_users ON vtiger_customview.userid = vtiger_users.id WHERE vtiger_customview.entitytype=?";
		$parameters = array($moduleName);

		if(!is_admin($user)) {
			require('user_privileges/user_privileges_'.$user->id.'.php');
			
			$sql .= " AND (vtiger_customview.status=0 or vtiger_customview.userid = ? or vtiger_customview.status = 3 or vtiger_customview.userid IN
			(SELECT vtiger_user2role.userid FROM vtiger_user2role INNER JOIN vtiger_users on vtiger_users.id=vtiger_user2role.userid 
			INNER JOIN vtiger_role on vtiger_role.roleid=vtiger_user2role.roleid WHERE vtiger_role.parentrole LIKE '".$current_user_parent_role_seq."::%'))";
			
			array_push($parameters, $current_user->id);
		}
		
		$result = $adb->pquery($sql, $parameters);
		if($result && $adb->num_rows($result)) {
			while($resultrow = $adb->fetch_array($result)) {
				$filters[] = $this->prepareFilterDetailUsingResultRow($resultrow);
			}
		}
		
		return $filters;
	}
	
	protected function prepareFilterDetailUsingResultRow($resultrow) {
		$filter = array();
		$filter['cvid'] = $resultrow['cvid'];
		$filter['viewname'] = decode_html($resultrow['viewname']);
		$filter['setdefault'] = $resultrow['setdefault'];
		$filter['setmetrics'] = $resultrow['setmetrics'];
		$filter['moduleName'] = decode_html($resultrow['entitytype']);
		$filter['status']     = decode_html($resultrow['status']);
		$filter['userName']   = decode_html($resultrow['user_name']);
		return $filter;
	}
}