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
		$sql = " SELECT vtiger_links.*, mdw.userid, mdw.data, mdw.active, mdw.title, mdw.size, mdw.filterid, mdw.id as widgetid, mdw.position as position, vtiger_links.linkid as id, mdw.limit, mdw.cache, mdw.owners 
			FROM vtiger_links 
			LEFT JOIN vtiger_module_dashboard_widgets mdw ON vtiger_links.linkid = mdw.linkid
			WHERE mdw.userid = ? AND vtiger_links.linktype = ? AND mdw.module = ? AND `active` = ?";
		$params = array($currentUser->getId(), 'DASHBOARDWIDGET', $moduleModel->getId(), $action);
		$result = $db->pquery($sql, $params);

		$widgets = array();

		while ($row = $db->fetch_array($result)) {
			$row['linkid'] = $row['id'];
			if ($row['linklabel'] == 'Mini List') {
				$minilistWidget = Vtiger_Widget_Model::getInstanceFromValues($row);
				$minilistWidgetModel = new Vtiger_MiniList_Model();
				$minilistWidgetModel->setWidgetModel($minilistWidget);
				$minilistWidget->set('title', $minilistWidgetModel->getTitle());
				$widgets[] = $minilistWidget;
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
				$filterData = Zend_Json::decode(htmlspecialchars_decode($data));
				$module = $filterData['module'];
			}
			if (!$currentUserPrivilegeModel->hasModulePermission(getTabid($module))) {
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
		$urlParts = parse_url($linkUrl);
		parse_str($urlParts['query'], $params);
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
		//TODO: Need to review this API is needed?
		$moduleModel = $this->getModule();
		$widgets = array();

		return $widgets;
	}

	public function verifyDashboard($moduleName)
	{
		$log = vglobal('log');
		$log->debug("Entering Vtiger_DashBoard_Model::verifyDashboard() method ...");
		$adb = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$blockId = Settings_WidgetsManagement_Module_Model::getBlocksFromModule($moduleName, $currentUser->getRole());
		$query = 'SELECT vtiger_module_dashboard.*, vtiger_links.tabid FROM `vtiger_module_dashboard` INNER JOIN vtiger_links ON vtiger_links.linkid = vtiger_module_dashboard.linkid WHERE vtiger_module_dashboard.blockid IN (0,?);';
		if (count($blockId) == 0)
			return;
		$params = array($blockId);
		$result = $adb->pquery($query, $params);
		$num = $adb->num_rows($result);
		for ($i = 0; $i < $num; $i++) {
			$row = $adb->query_result_rowdata($result, $i);
			$row['data'] = htmlspecialchars_decode($row['data']);
			$row['size'] = htmlspecialchars_decode($row['size']);
			$row['owners'] = htmlspecialchars_decode($row['owners']);
			$query = 'SELECT * FROM `vtiger_module_dashboard_widgets` WHERE `userid` = ? AND `templateid` = ?;';
			$params = array($currentUser->getId(), $row['id']);
			$resultVerify = $adb->pquery($query, $params);
			if (!$adb->num_rows($resultVerify)) {

				$query = 'INSERT INTO vtiger_module_dashboard_widgets(`linkid`, `userid`, `templateid`, `filterid`, `title`, `data`, `size`, `limit`, `owners`, `isdefault`, `active`, `module`, `cache`) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?);';
				$active = 0;
				if ($row['isdefault'])
					$active = 1;
				$params = array($row['linkid'], $currentUser->getId(), $row['id'], $row['filterid'], $row['title'], $row['data'], $row['size'], $row['limit'], $row['owners'], $row['isdefault'], $active, $row['tabid'], $row['cache']);
				$adb->pquery($query, $params);
			}
		}
		$log->debug("Exiting Vtiger_DashBoard_Model::verifyDashboard() method ...");
		return $num;
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
}
