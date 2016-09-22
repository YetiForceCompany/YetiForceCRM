<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Vtiger_DashBoard_Model extends Vtiger_Base_Model
{

	/**
	 * Function to get Module instance
	 * @return <Vtiger_Module_Model>
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Function to set the module instance
	 * @param <Vtiger_Module_Model> $moduleInstance - module model
	 * @return Vtiger_DetailView_Model>
	 */
	public function setModule($moduleInstance)
	{
		$this->module = $moduleInstance;
		return $this;
	}

	/**
	 *  Function to get the module name
	 *  @return <String> - name of the module
	 */
	public function getModuleName()
	{
		return $this->getModule()->get('name');
	}

	/**
	 * Function returns List of User's selected Dashboard Widgets
	 * @return <Array of Vtiger_Widget_Model>
	 */
	public function getDashboards($action = 1)
	{
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$currentUserPrivilegeModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleModel = $this->getModule();

		if ($action == 'Header')
			$action = 0;
		$sql = " SELECT 
					vtiger_links.*,
					mdw.userid,
					mdw.data,
					mdw.active,
					mdw.title,
					mdw.size,
					mdw.filterid,
					mdw.id AS widgetid,
					mdw.position,
					vtiger_links.linkid AS id,
					mdw.limit,
					mdw.cache,
					mdw.owners,
					mdw.isdefault
				  FROM
					vtiger_links 
					LEFT JOIN vtiger_module_dashboard_widgets mdw 
					  ON vtiger_links.linkid = mdw.linkid 
				  WHERE mdw.userid = ? 
					AND vtiger_links.linktype = ? 
					AND mdw.module = ? 
					AND `active` = ?";
		$params = [$currentUser->getId(), 'DASHBOARDWIDGET', $moduleModel->getId(), $action];

		$result = $db->pquery($sql, $params);

		$widgets = [];

		while ($row = $db->fetch_array($result)) {
			$row['linkid'] = $row['id'];
			if ($row['linklabel'] == 'Mini List') {
				if (!$row['isdeafult'])
					$row['deleteFromList'] = true;
				$minilistWidget = Vtiger_Widget_Model::getInstanceFromValues($row);
				$minilistWidgetModel = new Vtiger_MiniList_Model();
				$minilistWidgetModel->setWidgetModel($minilistWidget);
				$minilistWidget->set('title', $minilistWidgetModel->getTitle());
				$widgets[] = $minilistWidget;
			} elseif ($row['linklabel'] == 'ChartFilter') {
				if (!$row['isdeafult'])
					$row['deleteFromList'] = true;
				$charFilterWidget = Vtiger_Widget_Model::getInstanceFromValues($row);
				$chartFilterWidgetModel = new Vtiger_ChartFilter_Model();
				$chartFilterWidgetModel->setWidgetModel($charFilterWidget);
				$charFilterWidget->set('title', $chartFilterWidgetModel->getTitle());
				$widgets[] = $charFilterWidget;
			} else
				$widgets[] = Vtiger_Widget_Model::getInstanceFromValues($row);
		}

		foreach ($widgets as $index => $widget) {
			$label = $widget->get('linklabel');
			$url = $widget->get('linkurl');
			$data = $widget->get('data');
			$filterid = $widget->get('filterid');
			$module = $this->getModuleNameFromLink($url, $label);

			if ($module == 'Home' && !empty($filterid) && !empty($data)) {
				$filterData = \includes\utils\Json::decode(htmlspecialchars_decode($data));
				$module = $filterData['module'];
			}
			if (!$currentUserPrivilegeModel->hasModulePermission($module)) {
				unset($widgets[$index]);
			}
		}

		return $widgets;
	}

	/**
	 * Function to get the module name of a widget using linkurl
	 * @param <string> $linkUrl
	 * @param <string> $linkLabel
	 * @return <string> $module - Module Name
	 */
	public function getModuleNameFromLink($linkUrl, $linkLabel)
	{
		$params = vtlib\Functions::getQueryParams($linkUrl);
		$module = $params['module'];
		if ($linkLabel == 'Overdue Activities' || $linkLabel == 'Upcoming Activities') {
			$module = 'Calendar';
		}
		return $module;
	}

	/**
	 * Function to get the default widgets(Deprecated)
	 * @return <Array of Vtiger_Widget_Model>
	 */
	public function getDefaultWidgets()
	{
		$moduleModel = $this->getModule();
		$widgets = [];

		return $widgets;
	}

	public function verifyDashboard($moduleName)
	{
		$log = \LoggerManager::getInstance();
		$log->debug('Entering ' . __CLASS__ . ':' . __FUNCTION__ . '(' . $moduleName . ')');
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$blockId = Settings_WidgetsManagement_Module_Model::getBlocksFromModule($moduleName, $currentUser->getRole());
		if (count($blockId) == 0) {
			$log->debug('Exiting ' . __CLASS__ . ':' . __FUNCTION__);
			return;
		}
		$query = 'SELECT vtiger_module_dashboard.*, vtiger_links.tabid FROM `vtiger_module_dashboard` 
			INNER JOIN vtiger_links ON vtiger_links.linkid = vtiger_module_dashboard.linkid 
			WHERE vtiger_module_dashboard.blockid = ?;';
		$result = $db->pquery($query, $blockId);
		while ($row = $db->getRow($result)) {
			$row['data'] = htmlspecialchars_decode($row['data']);
			$row['size'] = htmlspecialchars_decode($row['size']);
			$row['owners'] = htmlspecialchars_decode($row['owners']);
			$query = 'SELECT 1 FROM `vtiger_module_dashboard_widgets` WHERE `userid` = ? && `templateid` = ?;';
			$resultVerify = $db->pquery($query, [$currentUser->getId(), $row['id']]);
			if (!$db->getRowCount($resultVerify)) {
				$active = $row['isdefault'] ? 1 : 0;
				$db->insert('vtiger_module_dashboard_widgets', [
					'linkid' => $row['linkid'],
					'userid' => $currentUser->getId(),
					'templateid' => $row['id'],
					'filterid' => $row['filterid'],
					'title' => $row['title'],
					'data' => $row['data'],
					'size' => $row['size'],
					'limit' => $row['limit'],
					'owners' => $row['owners'],
					'isdefault' => $row['isdefault'],
					'active' => $active,
					'module' => $row['tabid'],
					'cache' => $row['cache']
				]);
			}
		}
		$log->debug('Exiting ' . __CLASS__ . ':' . __FUNCTION__);
	}

	/**
	 * Function to get the instance
	 * @param <String> $moduleName - module name
	 * @return <Vtiger_DashBoard_Model>
	 */
	public static function getInstance($moduleName)
	{
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'DashBoard', $moduleName);
		$instance = new $modelClassName();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		return $instance->setModule($moduleModel);
	}

	/**
	 * Function to get modules with widgets
	 * @param <String> $moduleName - module name
	 * @return <Array> $modules
	 */
	public static function getModulesWithWidgets($moduleName = false)
	{
		$currentUser = Users_Privileges_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT vtiger_module_dashboard_widgets.module, vtiger_module_dashboard_blocks.tabid FROM vtiger_module_dashboard 
			LEFT JOIN vtiger_module_dashboard_blocks ON vtiger_module_dashboard_blocks.id = vtiger_module_dashboard.blockid
			LEFT JOIN `vtiger_module_dashboard_widgets` ON `vtiger_module_dashboard_widgets`.templateid = vtiger_module_dashboard.id
			WHERE userid = ? OR authorized = ? GROUP BY module, tabid;', [$currentUser->getId(), $currentUser->getRole()]);
		$modules = [];
		while ($row = $db->getRow($result)) {
			$tabId = $row['module'] ? $row['module'] : $row['tabid'];
			if (!isset($modules[$tabId])) {
				$modules[$tabId] = vtlib\Functions::getModuleName($tabId);
			}
		}
		ksort($modules);
		if ($moduleName && ($tabId = vtlib\Functions::getModuleId($moduleName))) {
			unset($modules[$tabId]);
			$modules = array_merge([$tabId => $moduleName], $modules);
		}
		return $modules;
	}
}
