<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 *************************************************************************************/

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
	 * Function returns the list of Widgets
	 * @return <Array of Vtiger_Widget_Model>
	 */
	public function getSelectableDashboard() {
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$moduleModel = $this->getModule();

		$sql = 'SELECT * FROM vtiger_links WHERE (linktype = ? AND tabid = ? AND linkid NOT IN (SELECT linkid FROM vtiger_module_dashboard_widgets WHERE userid = ?)) OR linklabel in (?,?)';
		$params = array('DASHBOARDWIDGET', $moduleModel->getId(), $currentUser->getId(),'Mini List', 'Notebook');
		$result = $db->pquery($sql, $params);

		$widgets = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			
			if($row['linkdata']){
				$data = Zend_Json::decode(html_entity_decode($row['linkdata']));
				if($data['inactive'] == 1 && in_array($currentUser->getRole(), $data['roles'])){
					continue;
				}
			}
			if($row['linklabel'] == 'Tag Cloud') {
				$isTagCloudExists = getTagCloudView($currentUser->getId());
				if($isTagCloudExists == 'false') {
					continue;
				}
			}
			$widgets[] = Vtiger_Widget_Model::getInstanceFromValues($row);
		}

		return $widgets;
	}

	/**
	 * Function returns List of User's selected Dashboard Widgets
	 * @return <Array of Vtiger_Widget_Model>
	 */
	public function getDashboards() {
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$moduleModel = $this->getModule();

		$sql = " SELECT vtiger_links.*, vtiger_module_dashboard_widgets.userid, vtiger_module_dashboard_widgets.id as widgetid, vtiger_module_dashboard_widgets.position as position, vtiger_links.linkid as id 
			FROM vtiger_links 
			INNER JOIN vtiger_module_dashboard_widgets ON vtiger_links.linkid=vtiger_module_dashboard_widgets.linkid
			WHERE vtiger_module_dashboard_widgets.userid = ? AND linktype = ? AND tabid = ?";
		$params = array($currentUser->getId(), 'DASHBOARDWIDGET', $moduleModel->getId());
		$result = $db->pquery($sql, $params);

		$widgets = array();

		for($i=0, $len=$db->num_rows($result); $i<$len; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$row['linkid'] = $row['id'];
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
