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

class Settings_WidgetsManagement_Module_Model extends Settings_Vtiger_Module_Model
{

	public function getRestrictFilter()
	{
		return [
			"LBL_CREATED_BY_ME_BUT_NOT_MINE_ACTIVITIES" => ['mine']
		];
	}

	public function getWidgetsWithLimit()
	{
		$widgetWithLimit = ['History', 'Upcoming Activities', 'Overdue Activities', 'Mini List', 'Delegated project tasks', 'Delegated (overdue) project tasks', 'Delagated Events/To Do', 'Delegated (overdue) Events/ToDos', 'LBL_EXPIRING_SOLD_PRODUCTS',
			"LBL_CREATED_BY_ME_BUT_NOT_MINE_ACTIVITIES", 'LBL_NEW_ACCOUNTS', 'LBL_NEGLECTED_ACCOUNTS'];
		return $widgetWithLimit;
	}

	static public function getWidgetSpecial()
	{
		return ['Mini List', 'Notebook', 'Chart', 'ChartFilter', 'Rss'];
	}

	public static function getDateSelectDefault()
	{
		return [
			'day' => 'PLL_CURRENT_DAY',
			'week' => 'PLL_CURRENT_WEEK',
			'month' => 'PLL_CURRENT_MONTH',
			'year' => 'PLL_CURRENT_YEAR'
		];
	}

	public static function getDefaultDate($widgetModel)
	{
		$defaultDate = $widgetModel->get('date');
		if ($defaultDate === 'day') {
			$timeStart = date('Y-m-d');
		} elseif ($defaultDate === 'week') {
			$timeStart = date('Y-m-d', strtotime('last Monday'));
		} elseif ($defaultDate === 'month') {
			$timeStart = date('Y-m-01');
		} elseif ($defaultDate === 'year') {
			$timeStart = date('Y-01-01');
		} else {
			return false;
		}
		return ['start' => $timeStart, 'end' => date('Y-m-d')];
	}

	public static function getDashboardTypes()
	{
		return (new App\Db\Query())->from('u_#__dashboard_type')->all();
	}

	public static function getDefaultDashboard()
	{
		return (new App\Db\Query())->select('dashboard_id')
				->from('u_#__dashboard_type')
				->where(['system' => 1])
				->scalar();
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
	}

	public static function deleteDashboard($dashboardId)
	{
		$db = App\Db::getInstance();
		$db->createCommand()->delete('u_#__dashboard_type', ['dashboard_id' => $dashboardId])->execute();
		$blocks = (new App\Db\Query())->select('id')->from('vtiger_module_dashboard_blocks')
				->where(['dashboard_id' => $dashboardId])->createCommand()->queryColumn();
		$db->createCommand()->delete('vtiger_module_dashboard_blocks', ['dashboard_id' => $dashboardId])->execute();
		$db->createCommand()->delete('vtiger_module_dashboard', ['blockid' => $blocks])->execute();
		$db->createCommand()->delete('vtiger_module_dashboard_widgets', ['dashboardid' => $dashboardId])->execute();
	}

	public static function getDashboardInfo($dashboardId)
	{
		return (new App\Db\Query())->from('u_#__dashboard_type')
				->where(['dashboard_id' => (int) $dashboardId])
				->one();
	}

	/**
	 * Function appoints the proper owner
	 * @param Vtiger_Widget_Model $widgetModel
	 * @param string $moduleName
	 * @param mixed $owner
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
			if (($owner !== 'all' && !isset($accessibleUsers[$owner]) && !isset($accessibleGroups[$owner])) || ($owner === 'all' && !in_array($owner, $owners['available']))) {
				return false;
			}
			return $owner;
		}
		$defaultSelected = $owners['default'];

		if (!is_array($owners['available'])) {
			$owners['available'] = [$owners['available']];
		}

		if ($defaultSelected == 'mine' && in_array($defaultSelected, $owners['available'])) {
			$user = $currentUser->getUserId();
		} elseif ($defaultSelected == 'all' && in_array($defaultSelected, $owners['available'])) {
			$user = $defaultSelected;
		} elseif (in_array('users', $owners['available'])) {
			if (key($accessibleUsers) == $currentUser->getUserId())
				next($accessibleUsers);
			$user = key($accessibleUsers);
		} elseif (in_array('groups', $owners['available'])) {
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

	public function getFilterSelect()
	{

		\App\Log::trace("Entering Settings_WidgetsManagement_Module_Model::getFilterSelect() method ...");

		$filterSelect = ['LBL_MINE' => 'mine', 'LBL_ALL' => 'all', 'LBL_USERS' => 'users', 'LBL_GROUPS' => 'groups'];

		\App\Log::trace("Exiting Settings_WidgetsManagement_Module_Model::getFilterSelect() method ...");
		return $filterSelect;
	}

	public function getFilterSelectDefault()
	{

		\App\Log::trace("Entering Settings_WidgetsManagement_Module_Model::getFilterSelectDefault() method ...");

		$filterSelectDefault = ['LBL_MINE' => 'mine', 'LBL_ALL' => 'all'];

		\App\Log::trace("Exiting Settings_WidgetsManagement_Module_Model::getFilterSelectDefault() method ...");
		return $filterSelectDefault;
	}

	public static function getWidgetsWithDate()
	{
		return [
			'LBL_NOTIFICATION_BY_SENDER', 'LBL_NOTIFICATION_BY_RECIPIENT', 'DW_SUMMATION_BY_USER', 'Leads by Status',
			'Leads by Industry', 'Leads by Source', 'Leads by Status Converted', 'Employees Time Control', 'LBL_ALL_TIME_CONTROL',
			'LBL_CLOSED_TICKETS_BY_PRIORITY', 'LBL_CLOSED_TICKETS_BY_USER', 'LBL_ACCOUNTS_BY_INDUSTRY'
		];
	}

	public function getWidgetsWithFilterUsers()
	{
		return [
			'Leads by Status Converted', 'Graf', 'Tickets by Status', 'Leads by Industry',
			'Leads by Source', 'Leads by Status', 'Funnel', 'Upcoming Activities', 'Overdue Activities',
			'Mini List', 'Delegated project tasks', 'Delegated (overdue) project tasks',
			'Delagated Events/To Dos', 'Delegated (overdue) Events/ToDos', 'Calendar',
			'LBL_CREATED_BY_ME_BUT_NOT_MINE_ACTIVITIES', 'DW_SUMMATION_BY_MONTHS', 'LBL_ALL_TIME_CONTROL',
			'LBL_NEW_ACCOUNTS', 'LBL_NEGLECTED_ACCOUNTS', 'LBL_CLOSED_TICKETS_BY_PRIORITY', 'LBL_ACCOUNTS_BY_INDUSTRY',
			'LBL_TOTAL_ESTIMATED_VALUE_BY_STATUS'
		];
	}

	public function getSize()
	{

		\App\Log::trace("Entering Settings_WidgetsManagement_Module_Model::getSize() method ...");

		$width = array(3, 4, 5, 6, 7, 8, 9, 10, 11, 12);
		$height = array(3, 4, 5, 6, 7, 8, 9, 10, 11, 12);

		\App\Log::trace("Exiting Settings_WidgetsManagement_Module_Model::getSize() method ...");
		return array('width' => $width, 'height' => $height);
	}

	public function getDefaultValues()
	{

		\App\Log::trace("Entering Settings_WidgetsManagement_Module_Model::getDefaultValues() method ...");

		$defaultValues = array('width' => 4, 'height' => 4);

		\App\Log::trace("Exiting Settings_WidgetsManagement_Module_Model::getDefaultValues() method ...");
		return $defaultValues;
	}

	public function getSelectableDashboard()
	{

		\App\Log::trace("Entering Settings_WidgetsManagement_Module_Model::getSelectableDashboard() method ...");
		$dataReader = (new \App\Db\Query())->from('vtiger_links')
				->innerJoin('vtiger_tab', 'vtiger_links.tabid = vtiger_tab.tabid')
				->where(['linktype' => 'DASHBOARDWIDGET', 'vtiger_tab.presence' => 0])
				->createCommand()->query();
		$widgets = [];
		while ($row = $dataReader->read()) {
			$moduleName = \App\Module::getModuleName($row['tabid']);
			$widgets[$moduleName][] = Vtiger_Widget_Model::getInstanceFromValues($row);
		}
		\App\Log::trace("Exiting Settings_WidgetsManagement_Module_Model::getSelectableDashboard() method ...");
		return $widgets;
	}

	/**
	 * Save data
	 * @param Array $data
	 * @param String $moduleName
	 * @return Array(success:true/false)
	 * */
	public function saveDetails($data, $moduleName)
	{
		\App\Log::trace("Entering Settings_WidgetsManagement_Module_Model::saveDetails($moduleName) method ...");

		$db = \App\Db::getInstance();
		$isWidgetExists = (new \App\Db\Query())
			->from('vtiger_module_dashboard')
			->where(['id' => $data['id']])
			->exists();
		if ($isWidgetExists) {
			$size = \App\Json::encode(['width' => $data['width'], 'height' => $data['height']]);
			$insert = [
				'isdefault' => (int) $data['isdefault'],
				'size' => $size,
				'limit' => $data['limit'],
				'cache' => $data['cache'],
				'date' => $data['default_date']
			];
			if (!empty($data['default_owner']) && !empty($data['owners_all'])) {
				$insert['owners'] = \App\Json::encode(['default' => $data['default_owner'], 'available' => $data['owners_all']]);
			}
			if ($data['type'] == 'DW_SUMMATION_BY_MONTHS') {
				$insert['data'] = \App\Json::encode(['plotLimit' => $data['plotLimit'], 'plotTickSize' => $data['plotTickSize']]);
			}
			if ($data['type'] == 'DW_SUMMATION_BY_USER') {
				$insert['data'] = \App\Json::encode(['showUsers' => isset($data['showUsers']) ? 1 : 0]);
			}
			$db->createCommand()->update('vtiger_module_dashboard', $insert, ['id' => $data['id']])
				->execute();

			$insert['active'] = isset($data['isdefault']) ? 1 : 0;
			$db->createCommand()->update('vtiger_module_dashboard_widgets', $insert, ['templateid' => $data['id']])
				->execute();
		}
		\App\Log::trace("Exiting Settings_WidgetsManagement_Module_Model::saveData() method ...");
		return ['success' => true];
	}

	public function addBlock($data, $moduleName, $addToUser)
	{
		\App\Log::trace("Entering Settings_WidgetsManagement_Module_Model::addBlock(" . $data . ", " . $moduleName . ") method ...");
		$db = App\Db::getInstance();
		$tabId = \App\Module::getModuleId($moduleName);
		$db->createCommand()
			->insert('vtiger_module_dashboard_blocks', [
				'authorized' => $data['authorized'],
				'tabid' => $tabId,
				'dashboard_id' => $data['dashboardId']
			])->execute();
		\App\Log::trace("Exiting Settings_WidgetsManagement_Module_Model::addBlock() method ...");
		return [
			'success' => true,
			'id' => $db->getLastInsertID('vtiger_module_dashboard_blocks_id_seq')
		];
	}

	public function addWidget($data, $moduleName, $addToUser = false)
	{
		\App\Log::trace("Entering Settings_WidgetsManagement_Module_Model::addWidget(" . $data . ", " . $moduleName . ") method ...");
		$db = App\Db::getInstance();
		$status = false;
		$widgetWithLimit = self::getWidgetsWithLimit();
		if (in_array($data['name'], $widgetWithLimit))
			$status = true;

		if ($status && !$data['limit'])
			$data['limit'] = 10;
		if ($data['isdefault'] != 1 || $data['isdefault'] != '1')
			$data['isdefault'] = 0;
		$size = \App\Json::encode([
				'width' => $data['width'],
				'height' => $data['height']
		]);
		$owners = \App\Json::encode([
				'default' => $data['default_owner'],
				'available' => $data['owners_all']
		]);
		$db->createCommand()->insert('vtiger_module_dashboard', [
			'linkid' => $data['linkid'],
			'blockid' => $data['blockid'],
			'filterid' => $data['filterid'],
			'title' => $data['title'],
			'data' => $data['data'],
			'size' => $size,
			'limit' => $data['limit'],
			'owners' => $owners,
			'isdefault' => $data['isdefault'],
			'cache' => $data['cache'],
			'date' => $data['default_date'],
		])->execute();
		$templateId = $db->getLastInsertID('vtiger_module_dashboard_id_seq');
		if ($addToUser) {
			$active = 0;
			if ($data['isdefault'])
				$active = 1;
			$db->createCommand()->insert('vtiger_module_dashboard_widgets', [
				'linkid' => $data['linkid'], 'userid' => Users_Record_Model::getCurrentUserModel()->getId(), 'templateid' => $templateId,
				'filterid' => $data['filterid'],
				'title' => $data['title'],
				'data' => $data['data'],
				'size' => $size, 'limit' => $data['limit'],
				'owners' => $owners,
				'isdefault' => $data['isdefault'],
				'active' => $active,
				'module' => \App\Module::getModuleId($moduleName),
				'cache' => $data['cache'],
				'date' => $data['default_date'],
				'dashboardid' => empty($data['dashboardId']) ? self::getDefaultDashboard() : $data['dashboardId']
			])->execute();
			$widgetId = $db->getLastInsertID('vtiger_module_dashboard_widgets_id_seq');
		}
		\App\Log::trace("Exiting Settings_WidgetsManagement_Module_Model::addWidget() method ...");
		return array('success' => true, 'id' => $templateId, 'wid' => $widgetId, 'status' => $status, 'text' => vtranslate('LBL_WIDGET_ADDED', 'Settings::WidgetsManagement'));
	}

	public function getBlocksId($dashboard)
	{
		\App\Log::trace("Entering Settings_WidgetsManagement_Module_Model::getBlocksId() method ...");
		$dataReader = (new App\Db\Query())->select('vtiger_module_dashboard_blocks.*, vtiger_role.rolename')
				->from('vtiger_module_dashboard_blocks')
				->innerJoin('vtiger_role', 'vtiger_module_dashboard_blocks.authorized = vtiger_role.roleid')
				->where(['vtiger_module_dashboard_blocks.dashboard_id' => $dashboard])
				->createCommand()->query();
		$data = [];
		while ($row = $dataReader->read()) {
			$blockId = $row['id'];
			$tabId = $row['tabid'];
			$moduleName = vtlib\Functions::getModuleName($tabId);
			$data[$moduleName][$blockId]['name'] = $row['rolename'];
			$data[$moduleName][$blockId]['code'] = $row['authorized'];
		}
		\App\Log::trace("Exiting Settings_WidgetsManagement_Module_Model::getBlocksId() method ...");
		return $data;
	}

	public static function getBlocksFromModule($moduleName, $authorized = '', $dashboard)
	{
		\App\Log::trace('getBlocksFromModule(' . $moduleName . ', ' . $authorized . ') method ...');
		$tabId = \App\Module::getModuleId($moduleName);
		$data = [];
		if ($dashboard === false)
			$dashboard = null;
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
		return $data;
	}

	public static function getSpecialWidgets($moduleName)
	{
		\App\Log::trace("Entering Settings_WidgetsManagement_Module_Model::getSpecialWidgets($moduleName) method ...");
		$tabId = \App\Module::getModuleId($moduleName);
		$query = (new \App\Db\Query())->from('vtiger_links')
			->where(['tabid' => $tabId, 'linklabel' => self::getWidgetSpecial()]);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$widgets[$row['linklabel']] = Vtiger_Widget_Model::getInstanceFromValues($row);
		}
		\App\Log::trace('Exiting Settings_WidgetsManagement_Module_Model::getSpecialWidgets() method ...');
		return $widgets;
	}

	/**
	 * Gets all id widgets for the module
	 * @param String $moduleName
	 * @return Array
	 * */
	public function getDashboardForModule($moduleName)
	{
		\App\Log::trace("Entering Settings_WidgetsManagement_Module_Model::getDashboardForModule(" . $moduleName . ") method ...");
		$tabId = \App\Module::getModuleId($moduleName);
		$data = [];
		$dataReader = (new \App\Db\Query())->select([
					'mdw.blockid', 'mdw.data', 'mdw.title', 'mdw.filterid', 'mdw.id',
					'mdw.size', 'mdw.limit', 'mdw.isdefault', 'mdw.owners', 'mdw.cache', 'mdw.date',
					'vtiger_links.*', 'mdb.authorized'
				])
				->from('vtiger_module_dashboard AS mdw')
				->innerJoin('vtiger_links', 'mdw.linkid = vtiger_links.linkid')
				->innerJoin('vtiger_module_dashboard_blocks AS mdb', 'mdw.blockid = mdb.id AND vtiger_links.tabid = mdb.tabid')
				->where(['vtiger_links.tabid' => $tabId])
				->createCommand()->query();
		$userId = '';
		$blockId = '';
		while ($row = $dataReader->read()) {
			if ($row['linklabel'] == 'Mini List') {
				$minilistWidget = Vtiger_Widget_Model::getInstanceFromValues($row);
				$minilistWidgetModel = new Vtiger_MiniList_Model();
				$minilistWidgetModel->setWidgetModel($minilistWidget);
				$minilistWidget->set('title', $minilistWidgetModel->getTitle());
				$data[$row['blockid']][] = $minilistWidget;
			} else if ($row['linklabel'] == 'ChartFilter') {
				$chartFilterWidget = Vtiger_Widget_Model::getInstanceFromValues($row);
				$chartFilterWidgetModel = new Vtiger_ChartFilter_Model();
				$chartFilterWidgetModel->setWidgetModel($chartFilterWidget);
				$chartFilterWidget->set('title', $chartFilterWidgetModel->getTitle());
				$data[$row['blockid']][] = $chartFilterWidget;
			} else
				$data[$row['blockid']][] = Vtiger_Widget_Model::getInstanceFromValues($row);
		}
		\App\Log::trace("Exiting Settings_WidgetsManagement_Module_Model::getDashboardForModule() method ...");
		return $data;
	}

	public function removeWidget($data)
	{

		\App\Log::trace("Entering Settings_WidgetsManagement_Module_Model::removeWidget(" . $data . ") method ...");
		$adb = PearDatabase::getInstance();
		$query = 'DELETE FROM vtiger_module_dashboard WHERE vtiger_module_dashboard.id = ?';
		$params = array($data['id']);
		$adb->pquery($query, $params);
		\App\Log::trace("Exiting Settings_WidgetsManagement_Module_Model::removeWidget() method ...");
		return array('success' => true);
	}

	public function removeBlock($data)
	{
		$db = App\Db::getInstance();
		\App\Log::trace("Entering Settings_WidgetsManagement_Module_Model::removeBlock(" . $data . ") method ...");
		$db->createCommand()->delete('vtiger_module_dashboard_blocks', ['id' => $data['blockid']])->execute();
		$db->createCommand()->delete('vtiger_module_dashboard', ['blockid' => $data['blockid']])->execute();
		\App\Log::trace("Exiting Settings_WidgetsManagement_Module_Model::removeBlock() method ...");
		return ['success' => true];
	}
}
