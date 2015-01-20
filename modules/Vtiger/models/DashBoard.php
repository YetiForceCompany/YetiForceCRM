<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 ************************************************************************************/

class Vtiger_DashBoard_Model extends Vtiger_Base_Model {

	/**
	 * Function to get Module instance
	 * @return <Vtiger_Module_Model>
	 */
	public function getModule() {
		return $this->module;
	}

	/**
	 * Function to set the module instance
	 * @param <Vtiger_Module_Model> $moduleInstance - module model
	 * @return Vtiger_DetailView_Model>
	 */
	public function setModule($moduleInstance) {
		$this->module = $moduleInstance;
		return $this;
	}

	/**
	 *  Function to get the module name
	 *  @return <String> - name of the module
	 */
	public function getModuleName(){
		return $this->getModule()->get('name');
	}

	/**
	 * Function returns List of User's selected Dashboard Widgets
	 * @return <Array of Vtiger_Widget_Model>
	 */
	public function getDashboards($action = 1) {
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$moduleModel = $this->getModule();
		if($action == 'Header')
			$action = 0;
		$sql = " SELECT vtiger_links.*, mdw.userid, mdw.data, mdw.active,  mdw.title, mdw.filterid, mdw.id as widgetid, mdw.position as position, vtiger_links.linkid as id 
			FROM vtiger_links 
			INNER JOIN vtiger_module_dashboard_widgets mdw ON vtiger_links.linkid = mdw.linkid
			WHERE mdw.userid = ? AND linktype = ? AND tabid = ? AND `active` = ?";
		$params = array($currentUser->getId(), 'DASHBOARDWIDGET', $moduleModel->getId(), $action);
		$result = $db->pquery($sql, $params);

		$widgets = array();

		for($i=0, $len=$db->num_rows($result); $i<$len; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$row['linkid'] = $row['id'];
			if($row['linklabel'] == 'Mini List'){
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
			$redex = '/module=(.+?)&+/';
			preg_match( $redex , $url , $match);
			if( isPermitted($match[1],'Index') == 'no'){
				unset($widgets[$index]);
			}		
			if($label == 'Tag Cloud') {
				$isTagCloudExists = getTagCloudView($currentUser->getId());
				if($isTagCloudExists === 'false')  unset($widgets[$index]);
			}
		}

		return $widgets;
	}

	/**
	 * Function to get the default widgets(Deprecated)
	 * @return <Array of Vtiger_Widget_Model>
	 */
	public function getDefaultWidgets() {
		//TODO: Need to review this API is needed?
		$moduleModel = $this->getModule();
		$widgets = array();

		return $widgets;
	}

	public function verifyDashboard($moduleName) {
		global $log;
		$log->debug("Entering Vtiger_DashBoard_Model::verifyDashboard() method ...");
		$adb = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$moduleModel = $this->getModule();
		$blockId = Settings_WidgetsManagement_Module_Model::getBlocksFromModule($moduleName, $currentUser->getRole() );
		$query='SELECT * FROM `vtiger_module_dashboard` WHERE `blockid` = ?;';
		$params = array( $blockId );
		$result = $adb->pquery($query,$params);
		for ( $i=0; $i<$adb->num_rows( $result ); $i++ ) {
			$row = $adb->query_result_rowdata($result, $i);
			$row['data'] = htmlspecialchars_decode($row['data']);
			$query='SELECT * FROM `vtiger_module_dashboard_widgets` WHERE `userid` = ? AND `templateid` = ?;';
			$params = array($currentUser->getId(), $row['id'] );
			$resultVerify = $adb->pquery($query,$params);
			if(!$adb->num_rows( $resultVerify )) {
				
				$query='INSERT INTO vtiger_module_dashboard_widgets(`linkid`, `userid`, `templateid`, `filterid`, `title`, `data`, `isdefault`, `active`) VALUES(?,?,?,?,?,?,?,?);';
				$active = 0;
				if($row['isdefault'])
					$active = 1;
				$params = array($row['linkid'], $currentUser->getId(), $row['id'], $row['filterid'], $row['title'], $row['data'], $row['isdefault'], $active);
				$adb->pquery($query,$params);
			}
		}
		$log->debug("Exiting Vtiger_DashBoard_Model::verifyDashboard() method ...");
	}

	/**
	 * Function to get the instance
	 * @param <String> $moduleName - module name
	 * @return <Vtiger_DashBoard_Model>
	 */
	public static function getInstance($moduleName) {
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'DashBoard', $moduleName);
		$instance = new $modelClassName();

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		return $instance->setModule($moduleModel);
	}


}
