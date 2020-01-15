<?php

/**
 * Updates Dashboard Class.
 *
 * @package Dashboard
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Adrian Kon <a.kon@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
\Vtiger_Loader::includeOnce('~/modules/ModTracker/ModTracker.php');
/**
 * ModTracker_Updates_Dashboard class.
 */
class ModTracker_Updates_Dashboard extends Vtiger_IndexAjax_View
{
	/**
	 * {@inheritdoc}
	 */
	public function checkPermission(App\Request $request)
	{
		parent::checkPermission($request);
		$userPrivilegesModel = \Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if ($request->has('sourceModule') && (!isset(ModTracker::getTrackingModules()[$request->getInteger('sourceModule')]) || !$userPrivilegesModel->hasModulePermission($request->getInteger('sourceModule')))) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$page = $request->getInteger('page');
		$linkId = $request->getInteger('linkid');
		$dateRange = $request->getDateRange('dateRange');
		$widget = \Vtiger_Widget_Model::getInstance($linkId, \App\User::getCurrentUserId());

		if (empty($dateRange)) {
			$dateRange[0] = App\Fields\Date::formatToDisplay('now');
			$dateRange[1] = App\Fields\Date::formatToDisplay('now');
		} else {
			$dateRange = \App\Fields\Date::formatRangeToDisplay($dateRange);
		}

		if ($request->has('sourceModule')) {
			$selectedModule = $request->getInteger('sourceModule');
		} else {
			$selectedModule = key($this->getModules());
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
		$widgetData = App\Json::decode($widget->get('data'));
		$widgetData['actions'] = $widgetData['actions'] ?? array_keys(ModTracker::getAllActionsTypes());
		$available = \App\Json::decode(html_entity_decode($widget->get('owners')))['available'] ?? [];
		if (!\is_array($available)) {
			$available = [$available];
		}
		$accessibleUsers = \App\Fields\Owner::getInstance(false)->getAccessibleUsers();
		$accessibleGroups = \App\Fields\Owner::getInstance(false)->getAccessibleGroups();
		foreach (['owner' => false, 'historyOwner' => 'all'] as $key => $defaultValue) {
			if (empty($widgetData[$key]) ||
				('all' !== $widgetData[$key] && !isset($accessibleUsers[$widgetData[$key]]) && !isset($accessibleGroups[$widgetData[$key]])) ||
				('all' === $widgetData[$key] && !\in_array($widgetData[$key], $available))) {
				$defaultValue = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget, false, $defaultValue);
				$widgetData[$key] = empty($defaultValue) ? \App\User::getCurrentUserId() : $defaultValue;
			}
		}
		if (!empty($widgetData['actions'])) {
			$updates = $this->getUpdates($selectedModule, $widgetData, $dateRange, $pagingModel);
		}

		$viewer->assign('UPDATES', $updates);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('DATE_RANGE', $dateRange);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('WIDGET_DATA', $widgetData);
		$viewer->assign('ACCESSIBLE_USERS', $accessibleUsers);
		$viewer->assign('ACCESSIBLE_GROUPS', $accessibleGroups);
		$viewer->assign('AVAILABLE_OWNERS', $available);
		$viewer->assign('SELECTED_MODULE', $selectedModule);
		if ($request->has('content')) {
			$viewer->view('dashboards/UpdatesContents.tpl', $moduleName);
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

	/**
	 * Gets updates.
	 *
	 * @param int                 $moduleId
	 * @param array               $conditions
	 * @param array               $dateRange
	 * @param Vtiger_Paging_Model $pagingModel
	 *
	 * @return array
	 */
	public function getUpdates(int $moduleId, array $conditions, array $dateRange, Vtiger_Paging_Model $pagingModel): array
	{
		$updates = [];
		$owner = $conditions['owner'];
		$historyOwner = $conditions['historyOwner'];
		$moduleName = \App\Module::getModuleName($moduleId);
		$queryGenerator = (new \App\QueryGenerator($moduleName))
			->setFields([])
			->setCustomColumn('vtiger_modtracker_basic.*')
			->addJoin([
				'INNER JOIN',
				'vtiger_modtracker_basic',
				'vtiger_crmentity.crmid = vtiger_modtracker_basic.crmid'
			])
			->addNativeCondition(['vtiger_modtracker_basic.status' => $conditions['actions']])
			->addNativeCondition([
				'between',
				'vtiger_modtracker_basic.changedon',
				\App\Fields\Date::formatToDb($dateRange[0]) . ' 00:00:00', \App\Fields\Date::formatToDb($dateRange[1]) . ' 23:59:59'
			])
			->setLimit($pagingModel->getPageLimit() + 1)
			->setOffset($pagingModel->getStartIndex());
		if ('all' !== $owner) {
			$queryGenerator
				->addCondition('shownerid', $owner, 'e', false)
				->addCondition('assigned_user_id', $owner, 'e', false);
		}
		if ('all' !== $historyOwner) {
			if ('Groups' === \App\Fields\Owner::getType($historyOwner)) {
				$historyOwner = \App\PrivilegeUtil::getUsersByGroup($historyOwner);
			}
			$queryGenerator->addNativeCondition(['vtiger_modtracker_basic.whodid' => $historyOwner]);
		}
		$dataReader = $queryGenerator->createQuery()->orderBy(['vtiger_modtracker_basic.id' => SORT_DESC])->createCommand()->query();
		while ($row = $dataReader->read()) {
			if (\count($updates) === $pagingModel->getPageLimit()) {
				$pagingModel->set('nextPageExists', true);
				break;
			}
			$recordModel = new ModTracker_Record_Model();
			$recordModel->setData($row)->setParent($row['crmid'], $moduleName);
			$updates[$recordModel->getId()] = $recordModel;
		}
		return $updates;
	}
}
