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

class Vtiger_Calendar_Dashboard extends Vtiger_IndexAjax_View
{

	public function process(Vtiger_Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$data = $request->getAll();

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

		$defaultDate = $data['start'] ? $data['start'] : date('Y-m-d');
		$owner = $owner ? $owner : 'all';
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('SOURCE_MODULE', 'Calendar');
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('CURRENTUSER', $currentUser);
		$viewer->assign('NAMELENGHT', AppConfig::main('title_max_length'));
		$viewer->assign('HREFNAMELENGHT', AppConfig::main('href_max_length'));
		$viewer->assign('NODATAMSGLABLE', 'LBL_NO_SCHEDULED_ACTIVITIES');
		$viewer->assign('DATA', $data);
		$viewer->assign('DEFAULTDATE', $defaultDate);
		$viewer->assign('OWNER', $owner);
		$viewer->assign('VIEW', $request->get('view'));

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$viewer->assign('CURRENT_USER', $currentUserModel);

		$content = $request->get('content');
		if (!empty($content)) {
			$viewer->view('dashboards/CalendarContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/Calendar.tpl', $moduleName);
		}
	}
}
