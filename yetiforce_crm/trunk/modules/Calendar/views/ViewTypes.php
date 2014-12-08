<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_ViewTypes_View extends Vtiger_IndexAjax_View {

    function __construct() {
        parent::__construct();
        $this->exposeMethod('getViewTypes');
		$this->exposeMethod('getSharedUsersList');
    }
        
	function getViewTypes(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$calendarViews = Calendar_Module_Model::getCalendarViewTypes($currentUser->id);

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('VIEWTYPES', $calendarViews);
		$viewer->view('CalendarViewTypes.tpl', $moduleName);
	}
	
	/**
	 * Function to get Shared Users
	 * @param Vtiger_Request $request
	 */
	function getSharedUsersList(Vtiger_Request $request){
		$viewer = $this->getViewer($request);
		$currentUser = Users_Record_Model::getCurrentUserModel();
		

		$moduleName = $request->getModule();
		$sharedUsers = Calendar_Module_Model::getSharedUsersOfCurrentUser($currentUser->id);
		$sharedUsersInfo = Calendar_Module_Model::getSharedUsersInfoOfCurrentUser($currentUser->id);
		
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('SHAREDUSERS', $sharedUsers);
		$viewer->assign('SHAREDUSERS_INFO', $sharedUsersInfo);
		$viewer->assign('CURRENTUSER_MODEL',$currentUser);
		$viewer->view('CalendarSharedUsers.tpl', $moduleName);
	}
}
