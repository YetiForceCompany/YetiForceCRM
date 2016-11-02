<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class OSSTimeControl_RightPanel_View extends Vtiger_IndexAjax_View
{

	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('getUsersList');
		$this->exposeMethod('getTypesList');
	}

	public function getUsersList(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('ALL_ACTIVEUSER_LIST', \App\Fields\Owner::getInstance(false, $currentUser)->getAccessibleUsers());
		$viewer->assign('ALL_ACTIVEGROUP_LIST', \App\Fields\Owner::getInstance(false, $currentUser)->getAccessibleGroups());
		$viewer->assign('USER_MODEL', $currentUser);
		$viewer->view('RightPanel.tpl', $moduleName);
	}

	public function getTypesList(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer->assign('ALL_ACTIVETYPES_LIST', OSSTimeControl_Calendar_Model::getCalendarTypes());
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('USER_MODEL', $currentUser);
		$viewer->view('RightPanel.tpl', $moduleName);
	}
}
