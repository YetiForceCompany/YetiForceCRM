<?php

/**
 * Vtiger calendar dashboard class
 * @package YetiForce.Dashboard
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class Vtiger_Calendar_Dashboard extends Vtiger_IndexAjax_View
{

	public function process(\App\Request $request)
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
		$viewer->assign('NAMELENGTH', AppConfig::main('title_max_length'));
		$viewer->assign('HREFNAMELENGTH', AppConfig::main('href_max_length'));
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
