<?php
/**
 * Settings OSSMailView index view class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_WidgetsManagement_Module_Model extends Settings_Vtiger_Module_Model
{
	/** {@inheritdoc} */
	public $name = 'WidgetsManagement';

	public function getWidgetsWithLimit(): array
	{
		return ['History', 'Upcoming Activities', 'Overdue Activities', 'Delegated project tasks', 'Delegated (overdue) project tasks',
			'LBL_EXPIRING_SOLD_PRODUCTS',
			'LBL_NEW_ACCOUNTS', 'LBL_NEGLECTED_ACCOUNTS', 'LBL_UPDATES',
			'LBL_NOTIFICATION_BY_SENDER', 'LBL_NOTIFICATION_BY_RECIPIENT', 'DW_SUMMATION_BY_USER', 'Leads by Status',
			'Leads by Industry', 'Leads by Source', 'Leads by Status Converted', 'Employees Time Control', 'LBL_ALL_TIME_CONTROL',
			'LBL_CLOSED_TICKETS_BY_PRIORITY', 'LBL_CLOSED_TICKETS_BY_USER', 'LBL_ACCOUNTS_BY_INDUSTRY'
		];
	}

	public static function getWidgetSpecial(): array
	{
		return ['Mini List', 'Notebook', 'Chart', 'ChartFilter', 'Rss'];
	}

	public static function getDateSelectDefault(): array
	{
		return [
			'day' => 'PLL_CURRENT_DAY',
			'week' => 'PLL_CURRENT_WEEK',
			'month' => 'PLL_CURRENT_MONTH',
			'year' => 'PLL_CURRENT_YEAR',
		];
	}

	public static function getDataKeys(): array
	{
		return ['skip_year', 'date_fields'];
	}

	/**
	 * Function to get available filters.
	 *
	 * @return string[]
	 */
	public function getFilterSelect(): array
	{
		return ['LBL_MINE' => 'mine', 'LBL_ALL' => 'all', 'LBL_USERS' => 'users', 'LBL_GROUPS' => 'groups', 'LBL_GROUP_USERS' => 'groupUsers', 'LBL_ROLE_USERS' => 'roleUsers', 'LBL_ROLE_AND_SUBORDINATES_USERS' => 'rsUsers'];
	}

	public function getFilterSelectDefault(): array
	{
		return ['LBL_MINE' => 'mine', 'LBL_ALL' => 'all'];
	}

	public function getWidgetsWithDate(): array
	{
		return [
			'LBL_NOTIFICATION_BY_SENDER', 'LBL_NOTIFICATION_BY_RECIPIENT', 'DW_SUMMATION_BY_USER', 'Leads by Status',
			'Leads by Industry', 'Leads by Source', 'Leads by Status Converted', 'Employees Time Control', 'LBL_ALL_TIME_CONTROL',
			'LBL_CLOSED_TICKETS_BY_PRIORITY', 'LBL_CLOSED_TICKETS_BY_USER', 'LBL_ACCOUNTS_BY_INDUSTRY'
		];
	}

	public function getWidgetsWithFilterUsers(): array
	{
		return [
			'Leads by Status Converted', 'Graf', 'Tickets by Status', 'Leads by Industry',
			'Leads by Source', 'Leads by Status', 'Funnel', 'Upcoming Activities', 'Overdue Activities',
			'Delegated project tasks', 'Delegated (overdue) project tasks',
			'LBL_ALL_TIME_CONTROL',
			'LBL_NEW_ACCOUNTS', 'LBL_NEGLECTED_ACCOUNTS', 'LBL_CLOSED_TICKETS_BY_PRIORITY', 'LBL_ACCOUNTS_BY_INDUSTRY',
			'LBL_TOTAL_ESTIMATED_VALUE_BY_STATUS', 'LBL_UPCOMING_PROJECT_TASKS', 'LBL_COMPLETED_PROJECT_TASKS', 'LBL_UPDATES', 'DW_TEAMS_ESTIMATED_SALES', 'DW_ACTUAL_SALES_OF_TEAM',
		];
	}

	public function getSize(): array
	{
		$width = [3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
		$height = [3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
		return ['width' => $width, 'height' => $height];
	}

	public function getDefaultValues(): array
	{
		return ['width' => 4, 'height' => 4];
	}

	/**
	 * Get default date range.
	 *
	 * @param Vtiger_Widget_Model $widgetModel
	 *
	 * @return array range ['2018-03-02','2018-03-04']
	 */
	public static function getDefaultDateRange($widgetModel)
	{
		$defaultDate = $widgetModel->get('date');
		if ('day' === $defaultDate) {
			$timeStart = date('Y-m-d');
		} elseif ('week' === $defaultDate) {
			$timeStart = date('Y-m-d', strtotime('last Monday'));
		} elseif ('month' === $defaultDate) {
			$timeStart = date('Y-m-01');
		} elseif ('year' === $defaultDate) {
			$timeStart = date('Y-01-01');
		} else {
			$timeStart = date('Y-m-d', strtotime('-1 month'));
		}
		return [$timeStart, date('Y-m-d', mktime(23, 59, 59, (int) date('n'), (int) date('j'), (int) date('Y')))];
	}

	/**
	 * Function to get all dashboard.
	 *
	 * @return array
	 */
	public static function getDashboardTypes()
	{
		if (App\Cache::has('WidgetsDashboard', 'AllTypes')) {
			return App\Cache::get('WidgetsDashboard', 'AllTypes');
		}
		$types = (new App\Db\Query())->from('u_#__dashboard_type')->indexBy('dashboard_id')->all();
		App\Cache::save('WidgetsDashboard', 'AllTypes', $types);

		return $types;
	}

	/**
	 * Function to get id of default dashboard.
	 *
	 * @return int
	 */
	public static function getDefaultDashboard()
	{
		$allTypes = self::getDashboardTypes();
		$dashboardId = 0;
		foreach ($allTypes as $dashboard) {
			if (1 === (int) $dashboard['system']) {
				$dashboardId = $dashboard['dashboard_id'];
				break;
			}
		}
		return $dashboardId;
	}

	public static function saveDashboard($dashboardId, $dashboardName)
	{
		if (empty($dashboardId)) {
			App\Db::getInstance()->createCommand()
				->insert('u_#__dashboard_type', ['name' => $dashboardName])
				->execute();
		} else {
			App\Db::getInstance()->createCommand()
				->update('u_#__dashboard_type', ['name' => $dashboardName], ['dashboard_id' => $dashboardId])
				->execute();
		}
		App\Cache::delete('WidgetsDashboard', 'AllTypes');
	}

	public static function deleteDashboard($dashboardId)
	{
		$db = App\Db::getInstance();
		$db->createCommand()->delete('u_#__dashboard_type', ['dashboard_id' => $dashboardId])->execute();
		$blocks = (new App\Db\Query())->select(['id'])->from('vtiger_module_dashboard_blocks')
			->where(['dashboard_id' => $dashboardId])->createCommand()->queryColumn();
		$db->createCommand()->delete('vtiger_module_dashboard_blocks', ['dashboard_id' => $dashboardId])->execute();
		$db->createCommand()->delete('vtiger_module_dashboard', ['blockid' => $blocks])->execute();
		$db->createCommand()->delete('vtiger_module_dashboard_widgets', ['dashboardid' => $dashboardId])->execute();
		App\Cache::delete('WidgetsDashboard', 'AllTypes');
	}

	public static function getDashboardInfo($dashboardId)
	{
		return (new App\Db\Query())->from('u_#__dashboard_type')
			->where(['dashboard_id' => (int) $dashboardId])
			->one();
	}

	/**
	 * Function appoints the proper owner.
	 *
	 * @param Vtiger_Widget_Model $widgetModel
	 * @param string              $moduleName
	 * @param mixed               $owner
	 *
	 * @return mixed
	 */
	public static function getDefaultUserId($widgetModel, $moduleName = false, $owner = false)
	{
		\App\Log::trace('Entering ' . __METHOD__);
		$currentUser = \App\User::getCurrentUserModel();
		$user = '';

		if ($moduleName) {
			$accessibleUsers = \App\Fields\Owner::getInstance($moduleName, $currentUser)->getAccessibleUsersForModule();
			$accessibleGroups = \App\Fields\Owner::getInstance($moduleName, $currentUser)->getAccessibleGroupForModule();
		} else {
			$accessibleUsers = \App\Fields\Owner::getInstance(false, $currentUser)->getAccessibleUsers();
			$accessibleGroups = \App\Fields\Owner::getInstance(false, $currentUser)->getAccessibleGroups();
		}
		$owners = \App\Json::decode(html_entity_decode($widgetModel->get('owners')));
		if ($owner) {
			if (('all' !== $owner && !isset($accessibleUsers[$owner]) && !isset($accessibleGroups[$owner])) || ('all' === $owner && !\in_array($owner, $owners['available']))) {
				return false;
			}

			return $owner;
		}
		$defaultSelected = $owners['default'];

		if (!\is_array($owners['available'])) {
			$owners['available'] = [$owners['available']];
		}

		if ('mine' == $defaultSelected && \in_array($defaultSelected, $owners['available'])) {
			$user = $currentUser->getId();
		} elseif ('all' == $defaultSelected && \in_array($defaultSelected, $owners['available'])) {
			$user = $defaultSelected;
		} elseif (\in_array('users', $owners['available'])) {
			if (key($accessibleUsers) == $currentUser->getId()) {
				next($accessibleUsers);
			}
			$user = key($accessibleUsers);
		} elseif (\in_array('groups', $owners['available'])) {
			$user = key($accessibleGroups);
		}
		if (empty($user) && $owners['available']) {
			reset($owners['available']);
			$user = current($owners['available']);
		}
		if (empty($user)) {
			$user = false;
		}
		\App\Log::trace('Exiting ' . __METHOD__);

		return $user;
	}

	public function getSelectableDashboard()
	{
		$dataReader = (new \App\Db\Query())->from('vtiger_links')
			->innerJoin('vtiger_tab', 'vtiger_links.tabid = vtiger_tab.tabid')
			->where(['linktype' => 'DASHBOARDWIDGET', 'vtiger_tab.presence' => 0])
			->createCommand()->query();
		$widgets = [];
		while ($row = $dataReader->read()) {
			$moduleName = \App\Module::getModuleName($row['tabid']);
			$widgets[$moduleName][] = Vtiger_Widget_Model::getInstanceFromValues($row);
		}
		$dataReader->close();

		return $widgets;
	}

	public function getPredefinedWidgetsByBlock(int $blockId)
	{
		$subQuery = (new \App\Db\Query())->from('vtiger_module_dashboard')->select(['vtiger_links.linkid'])
			->innerJoin('vtiger_links', 'vtiger_module_dashboard.linkid = vtiger_links.linkid')
			->where(['blockid' => $blockId])->andWhere(['not', ['linklabel' => ['Multifilter', 'Upcoming events']]]);
		$dataReader = (new \App\Db\Query())->from('vtiger_links')
			->select(['vtiger_links.*', 'blockid' => 'vtiger_module_dashboard_blocks.id'])
			->innerJoin('vtiger_tab', 'vtiger_links.tabid = vtiger_tab.tabid')
			->innerJoin('vtiger_module_dashboard_blocks', 'vtiger_tab.tabid = vtiger_module_dashboard_blocks.tabid')
			->where(['vtiger_module_dashboard_blocks.id' => $blockId, 'linktype' => 'DASHBOARDWIDGET', 'vtiger_tab.presence' => 0])
			->andWhere(['not', ['or', ['linklabel' => self::getWidgetSpecial()], ['vtiger_links.linkid' => $subQuery]]])
			->createCommand()->query();
		$widgets = [];
		while ($row = $dataReader->read()) {
			$widgets[] = Vtiger_Widget_Model::getInstanceFromValues($row);
		}
		$dataReader->close();

		return $widgets;
	}

	public function addBlock($data, $moduleName, $addToUser)
	{
		$db = App\Db::getInstance();
		$tabId = \App\Module::getModuleId($moduleName);
		$db->createCommand()
			->insert('vtiger_module_dashboard_blocks', [
				'authorized' => $data['authorized'],
				'tabid' => $tabId,
				'dashboard_id' => $data['dashboardId'],
			])->execute();

		return [
			'success' => true,
			'id' => $db->getLastInsertID('vtiger_module_dashboard_blocks_id_seq'),
		];
	}

	/**
	 * Add widget.
	 *
	 * @param array  $data
	 * @param string $moduleName
	 * @param bool   $addToUser
	 *
	 * @return array
	 */
	public function addWidget($data, $moduleName, $addToUser = false)
	{
		\App\Log::trace('Entering Settings_WidgetsManagement_Module_Model::addWidget(' . $moduleName . ') method ...');
		$db = App\Db::getInstance();
		$status = false;
		$widgetWithLimit = $this->getWidgetsWithLimit();
		if (!empty($data['name']) && \in_array($data['name'], $widgetWithLimit)) {
			$status = true;
		}
		if ($status && empty($data['limit'])) {
			$data['limit'] = 10;
		}
		if (empty($data['isdefault']) || 1 != $data['isdefault'] || '1' != $data['isdefault']) {
			$data['isdefault'] = 0;
		}
		if (!empty($data['filterid'])) {
			if (\is_string($data['filterid'])) {
				$filters = explode(',', $data['filterid']);
			} elseif (\is_array($data['filterid'])) {
				$filters = $data['filterid'];
			}
			if (\count($filters) > \App\Config::performance('CHART_MULTI_FILTER_LIMIT')) {
				throw new App\Exceptions\IllegalValue("ERR_VALUE_IS_TOO_LONG||filterid||{$moduleName}||" . implode(',', $filters), 406);
			}
		}
		$data['data'] = App\Json::decode(\App\Purifier::decodeHtml($data['data'] ?? ''));
		foreach (self::getDataKeys() as $key) {
			if (isset($data[$key])) {
				$data['data'][$key] = $data[$key];
				unset($data[$key]);
			}
		}
		if (!empty($data['data']['additionalFiltersFields']) && \count($data['data']['additionalFiltersFields']) > \App\Config::performance('CHART_ADDITIONAL_FILTERS_LIMIT')) {
			throw new App\Exceptions\IllegalValue('ERR_VALUE_IS_TOO_LONG||additionalFiltersFields||' . implode(',', $data['data']['additionalFiltersFields']), 406);
		}
		$data['data'] = App\Json::encode($data['data']);
		$size = \App\Json::encode([
			'width' => $data['width'],
			'height' => $data['height'],
		]);
		$owners = \App\Json::encode([
			'default' => $data['default_owner'] ?? '',
			'available' => $data['owners_all'] ?? '',
		]);
		$db->createCommand()->insert('vtiger_module_dashboard', [
			'linkid' => $data['linkid'],
			'blockid' => $data['blockid'],
			'filterid' => $data['filterid'] ?? '',
			'title' => $data['title'] ?? '',
			'data' => $data['data'],
			'size' => $size,
			'limit' => $data['limit'] ?? null,
			'owners' => $owners,
			'isdefault' => $data['isdefault'],
			'cache' => $data['cache'] ?? null,
			'date' => $data['default_date'] ?? null,
		])->execute();
		$templateId = $db->getLastInsertID('vtiger_module_dashboard_id_seq');
		if ($addToUser) {
			$active = 0;
			if ($data['isdefault']) {
				$active = 1;
			}
			$db->createCommand()->insert('vtiger_module_dashboard_widgets', [
				'linkid' => $data['linkid'],
				'userid' => \App\User::getCurrentUserId(),
				'templateid' => $templateId,
				'filterid' => $data['filterid'],
				'title' => $data['title'],
				'data' => $data['data'],
				'size' => $size, 'limit' => $data['limit'] ?? null,
				'owners' => $owners,
				'isdefault' => $data['isdefault'],
				'active' => $active,
				'module' => \vtlib\Link::getLinkData($data['linkid'])['tabid'] ?? \App\Module::getModuleId($moduleName),
				'cache' => $data['cache'] ?? null,
				'date' => $data['default_date'] ?? null,
				'dashboardid' => empty($data['dashboardId']) ? self::getDefaultDashboard() : $data['dashboardId'],
			])->execute();
			$widgetId = $db->getLastInsertID('vtiger_module_dashboard_widgets_id_seq');
		}
		\App\Log::trace('Exiting Settings_WidgetsManagement_Module_Model::addWidget() method ...');
		return ['success' => true, 'id' => $templateId, 'wid' => $widgetId ?? '', 'status' => $status, 'text' => \App\Language::translate('LBL_WIDGET_ADDED', 'Settings::WidgetsManagement')];
	}

	/**
	 * Gets blocks.
	 *
	 * @param int $dashboard
	 *
	 * @return array
	 */
	public function getBlocksId(int $dashboard): array
	{
		\App\Log::trace('Entering Settings_WidgetsManagement_Module_Model::getBlocksId() method ...');
		$dataReader = (new App\Db\Query())->select(['vtiger_module_dashboard_blocks.*', 'vtiger_role.rolename'])
			->from('vtiger_module_dashboard_blocks')
			->innerJoin('vtiger_role', 'vtiger_module_dashboard_blocks.authorized = vtiger_role.roleid')
			->where(['vtiger_module_dashboard_blocks.dashboard_id' => $dashboard])
			->union((new App\Db\Query())->select(['vtiger_module_dashboard_blocks.*', 'rolename' => 'w_#__servers.name'])->from('vtiger_module_dashboard_blocks')
			->innerJoin('w_#__servers', 'vtiger_module_dashboard_blocks.authorized = w_#__servers.id')
			->where(['vtiger_module_dashboard_blocks.dashboard_id' => $dashboard])
			)->createCommand()->query();
		$data = [];
		while ($row = $dataReader->read()) {
			$blockId = $row['id'];
			$tabId = $row['tabid'];
			$moduleName = \App\Module::getModuleName($tabId);
			$data[$moduleName][$blockId]['name'] = $row['rolename'];
			$data[$moduleName][$blockId]['code'] = $row['authorized'];
		}
		$dataReader->close();
		\App\Log::trace('Exiting Settings_WidgetsManagement_Module_Model::getBlocksId() method ...');

		return $data;
	}

	public static function getBlocksFromModule($moduleName, $authorized, $dashboard)
	{
		\App\Log::trace('getBlocksFromModule(' . $moduleName . ', ' . $authorized . ') method ...');
		$tabId = \App\Module::getModuleId($moduleName);
		$data = [];
		if (false === $dashboard) {
			$dashboard = null;
		}
		$query = (new \App\Db\Query())
			->from('vtiger_module_dashboard_blocks')
			->where(['tabid' => $tabId, 'dashboard_id' => $dashboard]);
		if ($authorized) {
			$query->andWhere(['authorized' => $authorized]);
		}
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$data[$row['authorized']] = $row['id'];
		}
		$dataReader->close();

		return $data;
	}

	public static function getSpecialWidgets($moduleName)
	{
		\App\Log::trace("Entering Settings_WidgetsManagement_Module_Model::getSpecialWidgets($moduleName) method ...");
		$tabId = \App\Module::getModuleId($moduleName);
		$query = (new \App\Db\Query())->from('vtiger_links')
			->where(['tabid' => $tabId, 'linklabel' => self::getWidgetSpecial()]);
		$dataReader = $query->createCommand()->query();
		$widgets = [];
		while ($row = $dataReader->read()) {
			$widgets[$row['linklabel']] = Vtiger_Widget_Model::getInstanceFromValues($row);
		}
		$dataReader->close();
		\App\Log::trace('Exiting Settings_WidgetsManagement_Module_Model::getSpecialWidgets() method ...');

		return $widgets;
	}

	/**
	 * Gets all id widgets for the module.
	 *
	 * @param string $moduleName
	 *
	 * @return array
	 * */
	public function getDashboardForModule($moduleName)
	{
		\App\Log::trace('Entering Settings_WidgetsManagement_Module_Model::getDashboardForModule(' . $moduleName . ') method ...');
		$tabId = \App\Module::getModuleId($moduleName);
		$data = [];
		$dataReader = (new \App\Db\Query())->select([
			'mdw.blockid', 'mdw.data', 'mdw.title', 'mdw.filterid', 'mdw.id',
			'mdw.size', 'mdw.limit', 'mdw.isdefault', 'mdw.owners', 'mdw.cache', 'mdw.date',
			'vtiger_links.*', 'mdb.authorized',
		])
			->from('vtiger_module_dashboard AS mdw')
			->innerJoin('vtiger_links', 'mdw.linkid = vtiger_links.linkid')
			->innerJoin('vtiger_module_dashboard_blocks AS mdb', 'mdw.blockid = mdb.id AND vtiger_links.tabid = mdb.tabid')
			->where(['vtiger_links.tabid' => $tabId])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$data[$row['blockid']][] = Vtiger_Widget_Model::getInstanceFromValues($row);
		}
		$dataReader->close();
		\App\Log::trace('Exiting Settings_WidgetsManagement_Module_Model::getDashboardForModule() method ...');

		return $data;
	}

	/**
	 * Remove widget.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function removeWidget($data)
	{
		\App\Db::getInstance()->createCommand()
			->delete('vtiger_module_dashboard', ['vtiger_module_dashboard.id' => $data['id']])
			->execute();

		return ['success' => true];
	}

	/**
	 * Remove block.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function removeBlock($data)
	{
		$db = App\Db::getInstance();
		\App\Log::trace("Entering Settings_WidgetsManagement_Module_Model::removeBlock({$data['blockid']}) method ...");
		$db->createCommand()->delete('vtiger_module_dashboard_blocks', ['id' => $data['blockid']])->execute();
		$db->createCommand()->delete('vtiger_module_dashboard', ['blockid' => $data['blockid']])->execute();
		\App\Log::trace('Exiting Settings_WidgetsManagement_Module_Model::removeBlock() method ...');

		return ['success' => true];
	}
}
