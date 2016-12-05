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

class Settings_WidgetsManagement_Configuration_View extends Settings_Vtiger_Index_View
{

	public function process(Vtiger_Request $request)
	{
		
		\App\Log::trace(__METHOD__ . ' | Start');
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$sourceModule = $request->get('sourceModule');
		$widgetsManagementModel = new Settings_WidgetsManagement_Module_Model();
		$dashboardModules = $widgetsManagementModel->getSelectableDashboard();

		if (empty($sourceModule)){
			$sourceModule = 'Home';
		}

		$currentDashboard = $request->get('dashboardId');
		if(empty($currentDashboard)) {
			$currentDashboard = Settings_WidgetsManagement_Module_Model::getDefaultDashboard();
		}
		$viewer = $this->getViewer($request);
		// get widgets list
		$widgets = $dashboardModules[$sourceModule];
		$dashboardStored = $widgetsManagementModel->getDashboardForModule($sourceModule, $currentDashboard);
		$defaultValues = $widgetsManagementModel->getDefaultValues();
		$size = $widgetsManagementModel->getSize();
		$widgetsWithLimit = $widgetsManagementModel->getWidgetsWithLimit();
		$authorization = Settings_Roles_Record_Model::getAll();
		$bloks = $widgetsManagementModel->getBlocksId($currentDashboard);
		$specialWidgets = Settings_WidgetsManagement_Module_Model::getSpecialWidgets($sourceModule);
		$filterSelect = $widgetsManagementModel->getFilterSelect();
		$filterSelectDefault = $widgetsManagementModel->getFilterSelectDefault();
		$widgetsWithFilterUsers = $widgetsManagementModel->getWidgetsWithFilterUsers();
		$restrictFilter = $widgetsManagementModel->getRestrictFilter();

		$viewer->assign('CURRENT_DASHBOARD', $currentDashboard);
		$viewer->assign('DASHBOARD_TYPES', Settings_WidgetsManagement_Module_Model::getDashboardTypes());
		$viewer->assign('FILTER_SELECT', $filterSelect);
		$viewer->assign('FILTER_SELECT_DEFAULT', $filterSelectDefault);
		$viewer->assign('DATE_SELECT_DEFAULT', Settings_WidgetsManagement_Module_Model::getDateSelectDefault());
		$viewer->assign('WIDGETS_WITH_FILTER_DATE', Settings_WidgetsManagement_Module_Model::getWidgetsWithDate());
		$viewer->assign('WIDGETS_WITH_FILTER_USERS', $widgetsWithFilterUsers);
		$viewer->assign('ALL_AUTHORIZATION', $authorization);
		$viewer->assign('SELECTED_MODULE_NAME', $sourceModule);
		$viewer->assign('SUPPORTED_MODULES', array_keys($dashboardModules));
		$viewer->assign('DASHBOARD_AUTHORIZATION_BLOCKS', $bloks[$sourceModule]);
		$viewer->assign('WIDGETS_AUTHORIZATION_INFO', $dashboardStored);
		$viewer->assign('SPECIAL_WIDGETS', $specialWidgets);
		$viewer->assign('CURRENTUSER', $currentUser);
		$viewer->assign('WIDGETS', $widgets);
		$viewer->assign('SIZE', $size);
		$viewer->assign('DEFAULTVALUES', $defaultValues);
		$viewer->assign('TITLE_OF_LIMIT', $widgetsWithLimit);
		$viewer->assign('QUALIFIED_MODULE', $request->getModule(false));
		$viewer->assign('RESTRICT_FILTER', $restrictFilter);

		echo $viewer->view('Configuration.tpl', $request->getModule(false), true);
		\App\Log::trace(__METHOD__ . ' | End');
	}
}
