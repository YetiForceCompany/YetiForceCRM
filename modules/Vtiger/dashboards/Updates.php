<?php

/**
 * Updates Dashboard Class.
 *
 * @package Dashboard
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 */
class Vtiger_Updates_Dashboard extends Vtiger_IndexAjax_View
{
	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$page = $request->getInteger('page');
		$linkId = $request->getInteger('linkid');
		$widget = Vtiger_Widget_Model::getInstance($linkId, \App\User::getCurrentUserId());
		$dateRange = $request->getDateRange('dateRange');
		if (empty($dateRange)) {
			$dateRange[0] = App\Fields\Date::formatToDisplay('now');
			$dateRange[1] = App\Fields\Date::formatToDisplay('now');
		} else {
			$dateRange = \App\Fields\Date::formatRangeToDisplay($dateRange);
		}
		if (!$request->has('owner')) {
			$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget);
		} else {
			$owner = $request->getByType('owner', 2);
		}
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
		$updates = [];
		if ((false !== $owner && $widgetData = App\Json::decode($widget->get('data')))) {
			$updates = Vtiger_Module_Model::getInstance($moduleName)->getUpdates($widgetData, $pagingModel, $owner, $dateRange);
		}
		$viewer->assign('UPDATES', $updates);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('PAGE', $page);
		$viewer->assign('DATE_RANGE', $dateRange);
		$viewer->assign('NEXTPAGE', (\count($updates) < $limit) ? 0 : $page + 1);
		$viewer->assign('MODULE_NAME', $moduleName);
		if ($request->has('content')) {
			$viewer->view('dashboards/UpdatesContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/Updates.tpl', $moduleName);
		}
	}
}
