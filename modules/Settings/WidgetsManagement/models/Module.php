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

	public static function getDefaultUserId($widgetModel, $moduleName = false)
	{
		$log = LoggerManager::getInstance();
		$log->debug('Entering Settings_WidgetsManagement_Module_Model::getDefaultUserId() method ...');
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$user = '';

		if ($moduleName) {
			$accessibleUsers = \includes\fields\Owner::getInstance($moduleName, $currentUser)->getAccessibleUsersForModule();
			$accessibleGroups = \includes\fields\Owner::getInstance($moduleName, $currentUser)->getAccessibleGroupForModule();
		} else {
			$accessibleUsers = \includes\fields\Owner::getInstance(false, $currentUser)->getAccessibleUsers();
			$accessibleGroups = \includes\fields\Owner::getInstance(false, $currentUser)->getAccessibleGroups();
		}
		$owners = \includes\utils\Json::decode(html_entity_decode($widgetModel->get('owners')));
		$defaultSelected = $owners['default'];

		if (!is_array($owners['available']))
			$owners['available'] = array($owners['available']);

		if ($defaultSelected == 'mine' && in_array($defaultSelected, $owners['available']))
			$user = $currentUser->getId();
		elseif ($defaultSelected == 'all' && in_array($defaultSelected, $owners['available']))
			$user = $defaultSelected;
		elseif (in_array('users', $owners['available'])) {
			if (key($accessibleUsers) == $currentUser->getId())
				next($accessibleUsers);
			$user = key($accessibleUsers);
		}
		elseif (in_array('groups', $owners['available'])) {
			$user = key($accessibleGroups);
		}
		if (empty($user) && $owners['available']) {
			reset($owners['available']);
			$user = current($owners['available']);
		}
		if (empty($user))
			$user = false;
		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::getDefaultUserId() method ...");
		return $user;
	}

	public function getFilterSelect()
	{
		$log = vglobal('log');
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::getFilterSelect() method ...");

		$filterSelect = array('LBL_MINE' => 'mine', 'LBL_ALL' => 'all', 'LBL_USERS' => 'users', 'LBL_GROUPS' => 'groups');

		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::getFilterSelect() method ...");
		return $filterSelect;
	}

	public function getFilterSelectDefault()
	{
		$log = vglobal('log');
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::getFilterSelectDefault() method ...");

		$filterSelectDefault = array('LBL_MINE' => 'mine', 'LBL_ALL' => 'all');

		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::getFilterSelectDefault() method ...");
		return $filterSelectDefault;
	}

	public function getWidgetsWithFilterUsers()
	{
		return [
			'Leads by Status Converted', 'Graf', 'Tickets by Status', 'Leads by Industry',
			'Leads by Source', 'Leads by Status', 'Funnel', 'Upcoming Activities', 'Overdue Activities',
			'Mini List', 'Delegated project tasks', 'Delegated (overdue) project tasks',
			'Delagated Events/To Dos', 'Delegated (overdue) Events/ToDos', 'Calendar',
			'LBL_CREATED_BY_ME_BUT_NOT_MINE_ACTIVITIES', 'DW_SUMMATION_BY_MONTHS', 'LBL_ALL_TIME_CONTROL',
			'LBL_NEW_ACCOUNTS', 'LBL_NEGLECTED_ACCOUNTS'
		];
	}

	public function getSize()
	{
		$log = vglobal('log');
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::getSize() method ...");

		$width = array(3, 4, 5, 6, 7, 8, 9, 10, 11, 12);
		$height = array(3, 4, 5, 6, 7, 8, 9, 10, 11, 12);

		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::getSize() method ...");
		return array('width' => $width, 'height' => $height);
	}

	public function getDefaultValues()
	{
		$log = vglobal('log');
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::getDefaultValues() method ...");

		$defaultValues = array('width' => 4, 'height' => 4);

		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::getDefaultValues() method ...");
		return $defaultValues;
	}

	public function getSelectableDashboard()
	{
		$log = vglobal('log');
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::getSelectableDashboard() method ...");
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$sql = 'SELECT * FROM vtiger_links
				INNER JOIN `vtiger_tab`
					ON vtiger_links.`tabid` = vtiger_tab.`tabid`
				WHERE linktype = ? && vtiger_tab.`presence` = 0';

		$params = ['DASHBOARDWIDGET'];

		$result = $db->pquery($sql, $params);

		$widgets = [];
		$numRows = $db->getRowCount($result);
		for ($i = 0; $i < $numRows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$moduleName = vtlib\Functions::getModuleName($row['tabid']);
			if ($row['linklabel'] == 'Tag Cloud') {
				$isTagCloudExists = \vtlib\Functions::getTagCloudView($currentUser->getId());
				if ($isTagCloudExists == 'false') {
					continue;
				}
			}
			$moduleName = vtlib\Functions::getModuleName($row['tabid']);
			$widgets[$moduleName][] = Vtiger_Widget_Model::getInstanceFromValues($row);
		}
		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::getSelectableDashboard() method ...");
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
		$log = LoggerManager::getInstance();
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::saveDetails($moduleName) method ...");

		$adb = PearDatabase::getInstance();
		$query = 'SELECT * FROM `vtiger_module_dashboard` WHERE `id` = ? LIMIT 1; ';
		$params = [$data['id']];
		$result = $adb->pquery($query, $params);
		if ($adb->num_rows($result) > 0) {
			$size = \includes\utils\Json::encode(['width' => $data['width'], 'height' => $data['height']]);
			$insert = [
				'isdefault' => $data['isdefault'],
				'size' => $size,
				'limit' => $data['limit'],
				'cache' => $data['cache'],
			];
			if (!empty($data['default_owner']) && !empty($data['owners_all'])) {
				$insert['owners'] = \includes\utils\Json::encode(['default' => $data['default_owner'], 'available' => $data['owners_all']]);
			}
			if ($data['type'] == 'DW_SUMMATION_BY_MONTHS') {
				$insert['data'] = \includes\utils\Json::encode(['plotLimit' => $data['plotLimit'], 'plotTickSize' => $data['plotTickSize']]);
			}
			if ($data['type'] == 'DW_SUMMATION_BY_USER') {
				$insert['data'] = \includes\utils\Json::encode(['showUsers' => isset($data['showUsers']) ? 1 : 0]);
			}
			$adb->update('vtiger_module_dashboard', $insert, '`id` = ?', [$data['id']]);

			$insert['active'] = isset($data['isdefault']) ? 1 : 0;
			$adb->update('vtiger_module_dashboard_widgets', $insert, '`templateid` = ?', [$data['id']]);
		}
		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::saveData() method ...");
		return array('success' => true);
	}

	public function addBlock($data, $moduleName)
	{
		$log = vglobal('log');
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::addBlock(" . $data . ", " . $moduleName . ") method ...");
		$adb = PearDatabase::getInstance();
		$tabId = \includes\Modules::getModuleId($moduleName);
		$query = 'INSERT INTO vtiger_module_dashboard_blocks (`authorized`, `tabid`) VALUES (?, ?);';
		$params = array($data['authorized'], $tabId);
		$adb->pquery($query, $params);
		$blockId = $adb->getLastInsertID();
		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::addBlock() method ...");
		return array('success' => true, 'id' => $blockId);
	}

	public function addWidget($data, $moduleName, $addToUser = false)
	{
		$log = vglobal('log');
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::addWidget(" . $data . ", " . $moduleName . ") method ...");
		$adb = PearDatabase::getInstance();

		$status = false;
		$widgetWithLimit = self::getWidgetsWithLimit();
		if (in_array($data['name'], $widgetWithLimit))
			$status = true;

		if ($status && !$data['limit'])
			$data['limit'] = 10;
		$query = 'INSERT INTO vtiger_module_dashboard(`linkid`, `blockid`, `filterid`, `title`, `data`, `size`, `limit`, `owners`,`isdefault`, `cache`) VALUES(?,?,?,?,?,?,?,?,?,?);';
		if ($data['isdefault'] != 1 || $data['isdefault'] != '1')
			$data['isdefault'] = 0;
		$size = \includes\utils\Json::encode(array('width' => $data['width'], 'height' => $data['height']));
		$owners = \includes\utils\Json::encode(array('default' => $data['default_owner'], 'available' => $data['owners_all']));
		$params = array($data['linkid'], $data['blockid'], $data['filterid'], $data['title'], $data['data'], $size, $data['limit'], $owners, $data['isdefault'], $data['cache']);

		$adb->pquery($query, $params);
		$templateId = $adb->getLastInsertID();

		if ($addToUser) {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$module = vtlib\Functions::getModuleId($moduleName);

			$active = 0;
			if ($data['isdefault'])
				$active = 1;
			$insert = [
				'linkid' => $data['linkid'], 'userid' => $currentUser->getId(), 'templateid' => $templateId,
				'filterid' => $data['filterid'], 'title' => $data['title'], 'data' => $data['data'],
				'size' => $size, 'limit' => $data['limit'], 'owners' => $owners,
				'isdefault' => $data['isdefault'], 'active' => $active, 'module' => $module, 'cache' => $data['cache']
			];
			$adb->insert('vtiger_module_dashboard_widgets', $insert);
			$widgetId = $adb->getLastInsertID();
		}
		$text = vtranslate('LBL_WIDGET_ADDED', 'Settings::WidgetsManagement');
		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::addWidget() method ...");
		return array('success' => true, 'id' => $templateId, 'wid' => $widgetId, 'status' => $status, 'text' => $text);
	}

	public function getBlocksId()
	{
		$log = vglobal('log');
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::getBlocksId() method ...");
		$adb = PearDatabase::getInstance();
		$data = array();
		$query = 'SELECT 
				  `vtiger_module_dashboard_blocks`.* , `vtiger_role`.`rolename` 
				FROM
				  `vtiger_module_dashboard_blocks` 
				  INNER JOIN `vtiger_role` 
				  ON `vtiger_module_dashboard_blocks`.`authorized` = `vtiger_role`.`roleid`;';
		$result = $adb->query($query);
		for ($i = 0; $i < $adb->num_rows($result); $i++) {
			$blockId = $adb->query_result($result, $i, 'id');
			$authorizedName = $adb->query_result($result, $i, 'rolename');
			$tabId = $adb->query_result($result, $i, 'tabid');
			$authorized = $adb->query_result($result, $i, 'authorized');
			$moduleName = vtlib\Functions::getModuleName($tabId);
			$data[$moduleName][$blockId]['name'] = $authorizedName;
			$data[$moduleName][$blockId]['code'] = $authorized;
		}
		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::getBlocksId() method ...");
		return $data;
	}

	public static function getBlocksFromModule($moduleName, $authorized = '')
	{
		$log = vglobal('log');
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::getBlocksFromModule(" . $moduleName . ", " . $authorized . ") method ...");
		$adb = PearDatabase::getInstance();
		$tabId = \includes\Modules::getModuleId($moduleName);
		$data = [];
		$query = 'SELECT * FROM `vtiger_module_dashboard_blocks` WHERE `tabid` = ?';
		$params = [$tabId];
		if ($authorized) {
			$query .= ' && `authorized` = ? ;';
			$params[] = $authorized;
		}
		$result = $adb->pquery($query, $params);
		while ($row = $adb->fetch_array($result)) {
			$data[$row['authorized']] = $row['id'];
		}
		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::getBlocksFromModule() method ...");
		return $data;
	}

	public static function getSpecialWidgets($moduleName)
	{
		$log = vglobal('log');
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::getSpecialWidgets($moduleName) method ...");
		$db = PearDatabase::getInstance();
		$tabId = \includes\Modules::getModuleId($moduleName);
		$query = 'SELECT * FROM `vtiger_links` WHERE `tabid` = ? && linklabel IN (?, ?, ?, ?, ?)';
		$result = $db->pquery($query, array_merge([$tabId], self::getWidgetSpecial()));
		$widgets = [];
		while ($row = $db->fetch_array($result)) {
			$widgets[$row['linklabel']] = Vtiger_Widget_Model::getInstanceFromValues($row);
		}
		$log->debug('Exiting Settings_WidgetsManagement_Module_Model::getSpecialWidgets() method ...');
		return $widgets;
	}

	/**
	 * Gets all id widgets for the module
	 * @param String $moduleName
	 * @return Array
	 * */
	public function getDashboardForModule($moduleName)
	{
		$log = vglobal('log');
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::getDashboardForModule(" . $moduleName . ") method ...");
		$adb = PearDatabase::getInstance();
		$tabId = \includes\Modules::getModuleId($moduleName);
		$data = array();

		$query = 'SELECT 
				  mdw.blockid,
				  mdw.data,
				  mdw.title,
				  mdw.filterid,
				  mdw.id,
				  mdw.size,
				  mdw.limit,
				  mdw.isdefault,
				  mdw.owners,
				  mdw.cache,
				  `vtiger_links`.*,
				  `mdb`.`authorized`
				FROM
				  `vtiger_module_dashboard` AS mdw 
				  INNER JOIN `vtiger_links` 
					ON `mdw`.`linkid` = `vtiger_links`.`linkid` 
				  INNER JOIN `vtiger_module_dashboard_blocks` AS mdb 
					ON (`mdw`.`blockid` = `mdb`.`id` && `vtiger_links`.`tabid` = `mdb`.`tabid`)
				WHERE `vtiger_links`.`tabid` = ?';
		$params = array($tabId);
		$result = $adb->pquery($query, $params);
		$num = $adb->num_rows($result);
		$userId = '';
		$blockId = '';
		for ($i = 0; $i < $num; $i++) {
			$row = $adb->query_result_rowdata($result, $i);
			if ($row['linklabel'] == 'Mini List') {
				$minilistWidget = Vtiger_Widget_Model::getInstanceFromValues($row);
				$minilistWidgetModel = new Vtiger_MiniList_Model();
				$minilistWidgetModel->setWidgetModel($minilistWidget);
				$minilistWidget->set('title', $minilistWidgetModel->getTitle());
				$data[$row['blockid']][$i] = $minilistWidget;
			} else if ($row['linklabel'] == 'ChartFilter') {
				$chartFilterWidget = Vtiger_Widget_Model::getInstanceFromValues($row);
				$chartFilterWidgetModel = new Vtiger_ChartFilter_Model();
				$chartFilterWidgetModel->setWidgetModel($chartFilterWidget);
				$chartFilterWidget->set('title', $chartFilterWidgetModel->getTitle());
				$data[$row['blockid']][$i] = $chartFilterWidget;
			} else
				$data[$row['blockid']][$i] = Vtiger_Widget_Model::getInstanceFromValues($row);
		}
		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::getDashboardForModule() method ...");
		return $data;
	}

	public function removeWidget($data)
	{
		$log = vglobal('log');
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::removeWidget(" . $data . ") method ...");
		$adb = PearDatabase::getInstance();
		$query = 'DELETE FROM vtiger_module_dashboard WHERE vtiger_module_dashboard.id = ?;';
		$params = array($data['id']);
		$adb->pquery($query, $params);
		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::removeWidget() method ...");
		return array('success' => true);
	}

	public function removeBlock($data)
	{
		$log = vglobal('log');
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::removeBlock(" . $data . ") method ...");
		$adb = PearDatabase::getInstance();

		$adb->pquery('DELETE FROM `vtiger_module_dashboard_blocks` WHERE id = ?;', [$data['blockid']]);
		$adb->pquery('DELETE FROM `vtiger_module_dashboard` WHERE blockid = ?;', [$data['blockid']]);

		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::removeBlock() method ...");
		return array('success' => true);
	}
}
