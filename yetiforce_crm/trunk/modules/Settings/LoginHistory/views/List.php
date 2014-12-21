<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_LoginHistory_List_View extends Settings_Vtiger_List_View {
	
	function preProcess(Vtiger_Request $request, $display=true) {
		$viewer = $this->getViewer($request);
		$loginHistoryRecordModel = new  Settings_LoginHistory_Record_Model();
		$usersList = $loginHistoryRecordModel->getAccessibleUsers();
		$viewer->assign('USERSLIST',$usersList);
        $viewer->assign('SELECTED_USER',$request->get('user_name'));
		parent::preProcess($request, false);
	}
}