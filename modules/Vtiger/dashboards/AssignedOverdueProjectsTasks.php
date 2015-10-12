<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************************************************************** */

class Vtiger_AssignedOverdueProjectsTasks_Dashboard extends Vtiger_IndexAjax_View
{

	public function process(Vtiger_Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$page = $request->get('page');
		$linkId = $request->get('linkid');

		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		if (!$request->has('owner'))
			$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget);
		else
			$owner = $request->get('owner');

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $page);
		$pagingModel->set('limit', (int) $widget->get('limit'));

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$projectsTasks = ($owner === false) ? array() : $moduleModel->getAssignedProjectsTasks('upcoming', $pagingModel, $owner);


		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('PROJECTSTASKS', $projectsTasks);
		$viewer->assign('PAGING', $pagingModel);
		$viewer->assign('CURRENTUSER', $currentUser);
		$title_max_length = vglobal('title_max_length');
		$href_max_length = vglobal('href_max_length');
		$viewer->assign('NAMELENGHT', $title_max_length);
		$viewer->assign('HREFNAMELENGHT', $href_max_length);
		$viewer->assign('OWNER', $owner);
		$viewer->assign('NODATAMSGLABLE', 'LBL_NO_OVERDUE_ACTIVITIES');
		$content = $request->get('content');
		if (!empty($content)) {
			$viewer->view('dashboards/AssignedProjectsTasksContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/AssignedProjectsTasks.tpl', $moduleName);
		}
	}
}
