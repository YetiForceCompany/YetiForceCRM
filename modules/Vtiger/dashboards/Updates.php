<?php

/**
 * Updates Dashboard Class.
 *
 * @package Dashboard
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Koń <a.kon@yetiforce.com>
 */
class Vtiger_Updates_Dashboard extends Vtiger_IndexAjax_View
{
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$data = $request->getAll();
		$moduleName = $request->getModule();
		$type = $request->getByType('type');
		$page = $request->getInteger('page');
		$linkId = $request->getInteger('linkid');
		$widget = Vtiger_Widget_Model::getInstance($linkId, \App\User::getCurrentUserId());
		$limit = (int) $widget->get('limit');

		if (empty($limit)) {
			$limit = 10;
		}
		if (empty($page)) {
			$page = 1;
		}
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $page);
		$pagingModel->set('limit', $limit);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		if ($request->has('content')) {
			$viewer->view('dashboards/UpdatesContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/Updates.tpl', $moduleName);
		}
	}
}
