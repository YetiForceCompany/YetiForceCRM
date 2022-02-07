<?php

/**
 * Vtiger calendar dashboard class.
 *
 * @package Dashboard
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_Calendar_Dashboard extends Vtiger_IndexAjax_View
{
	public function process(App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$data = $request->getAll();

		$page = $request->getInteger('page');
		$linkId = $request->getInteger('linkid');

		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		if (!$request->has('owner')) {
			$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget);
		} else {
			$owner = $request->getByType('owner', 2);
		}

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $page);
		$pagingModel->set('limit', (int) $widget->get('limit'));

		$defaultDate = !empty($data['start']) ? $data['start'] : date('Y-m-d');
		$owner = $owner ? $owner : 'all';
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('SOURCE_MODULE', 'Calendar');
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('CURRENTUSER', $currentUser);
		$viewer->assign('NAMELENGTH', App\Config::main('title_max_length'));
		$viewer->assign('HREFNAMELENGTH', App\Config::main('href_max_length'));
		$viewer->assign('NODATAMSGLABLE', 'LBL_NO_SCHEDULED_ACTIVITIES');
		$viewer->assign('DATA', $data);
		$viewer->assign('DEFAULTDATE', $defaultDate);
		$viewer->assign('OWNER', $owner);
		$viewer->assign('VIEW', $request->getByType('view'));
		if ($request->has('content')) {
			$viewer->view('dashboards/CalendarContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/Calendar.tpl', $moduleName);
		}
	}
}
