<?php

/**
 * Updates Dashboard Class.
 *
 * @package Dashboard
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
\Vtiger_Loader::includeOnce('~/modules/ModTracker/ModTracker.php');
/**
 * ModTracker_Updates_Dashboard class.
 */
class ModTracker_Updates_Dashboard extends Vtiger_IndexAjax_View
{
	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		parent::checkPermission($request);
		$userPrivilegesModel = \Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if ($request->has('sourceModule') && (!isset(ModTracker::getTrackingModules()[$request->getInteger('sourceModule')]) || !$userPrivilegesModel->hasModulePermission($request->getInteger('sourceModule')))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$page = $request->getInteger('page');
		$linkId = $request->getInteger('linkid');
		$dateRange = $request->getDateRange('dateRange');
		$widget = \Vtiger_Widget_Model::getInstance($linkId, \App\User::getCurrentUserId());

		if (empty($dateRange)) {
			$dateRange = [];
			$dateRange[0] = date('Y-m-d');
			$dateRange[1] = date('Y-m-d');
		}

		$selectedModule = $request->getInteger('sourceModule', 0);
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

		$updates = $actions = [];
		$widgetData = App\Json::decode($widget->get('data'));
		$widgetData['actions'] = $widgetData['actions'] ?? array_keys(ModTracker::getAllActionsTypes());
		$available = \App\Json::decode(html_entity_decode($widget->get('owners')))['available'] ?? [];
		if (!\is_array($available)) {
			$available = [$available];
		}
		$accessibleUsers = \App\Fields\Owner::getInstance(false)->getAccessibleUsers();
		$accessibleGroups = \App\Fields\Owner::getInstance(false)->getAccessibleGroups();
		foreach (['owner' => false, 'historyOwner' => false] as $key => $defaultValue) {
			if (empty($widgetData[$key])
				|| ('all' !== $widgetData[$key] && !isset($accessibleUsers[$widgetData[$key]]) && !isset($accessibleGroups[$widgetData[$key]]))
				|| ('all' === $widgetData[$key] && !\in_array($widgetData[$key], $available))) {
				$defaultValue = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget, false, $defaultValue);
				$widgetData[$key] = empty($defaultValue) ? \App\User::getCurrentUserId() : $defaultValue;
			}
		}
		$owner = 'all' === $widgetData['owner'] ? null : $widgetData['owner'];
		$historyOwner = 'all' === $widgetData['historyOwner'] ? null : $widgetData['historyOwner'];
		if (!empty($widgetData['actions'])) {
			if ($selectedModule) {
				$updates = ModTracker_Updates_Helper::getUpdates(\App\Module::getModuleName($selectedModule), $widgetData['actions'], $dateRange, $owner, $historyOwner, $pagingModel);
			} else {
				[$updates, $actions] = ModTracker_Updates_Helper::getSummary($this->getModules(), $widgetData['actions'], $dateRange, $owner, $historyOwner);
			}
		}

		$viewer->assign('UPDATES', $updates);
		$viewer->assign('ACTIONS', $actions);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('DATE_RANGE', \App\Fields\Date::formatRangeToDisplay($dateRange));
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('WIDGET_DATA', $widgetData);
		$viewer->assign('ACCESSIBLE_USERS', $accessibleUsers);
		$viewer->assign('ACCESSIBLE_GROUPS', $accessibleGroups);
		$viewer->assign('AVAILABLE_OWNERS', $available);
		$viewer->assign('SELECTED_MODULE', $selectedModule);
		$viewer->assign('URL', $widget->getUrl());
		if ($request->has('content')) {
			if ($selectedModule) {
				$viewer->view('dashboards/UpdatesContents.tpl', $moduleName);
			} else {
				$viewer->view('dashboards/UpdatesContentsSummary.tpl', $moduleName);
			}
		} else {
			$viewer->assign('TRACKING_MODULES', $this->getModules());
			$viewer->view('dashboards/Updates.tpl', $moduleName);
		}
	}

	/**
	 * Gets modules.
	 *
	 * @return string[]
	 */
	public function getModules(): array
	{
		if (!isset($this->trackingModules)) {
			$this->trackingModules = [];
			$userPrivilegesModel = \Users_Privileges_Model::getCurrentUserPrivilegesModel();
			foreach (ModTracker::getTrackingModules() as $tabId => $moduleName) {
				if ($userPrivilegesModel->hasModulePermission($moduleName)) {
					$this->trackingModules[$tabId] = $moduleName;
				}
			}
		}
		return $this->trackingModules;
	}
}
