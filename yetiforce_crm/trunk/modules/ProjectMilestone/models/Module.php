<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ************************************************************************************/

class ProjectMilestone_Module_Model extends Vtiger_Module_Model {

	public function getSideBarLinks($linkParams) {
		$linkTypes = array('SIDEBARLINK', 'SIDEBARWIDGET');
		$links = parent::getSideBarLinks($linkParams);
		unset($links['SIDEBARLINK']);

		$quickLinks = array(
			array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_PROJECTS_LIST',
				'linkurl' => $this->getProjectsListUrl(),
				'linkicon' => '',
			),
			array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_TASKS_LIST',
				'linkurl' => $this->getTasksListUrl(),
				'linkicon' => '',
			),
            array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_MILESTONES_LIST',
				'linkurl' => $this->getListViewUrl(),
				'linkicon' => '',
			),
		);
		foreach($quickLinks as $quickLink) {
			$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
		}

		return $links;
	}

	public function getProjectsListUrl() {
		$taskModel = Vtiger_Module_Model::getInstance('Project');
		return $taskModel->getListViewUrl();
	}
	
    public function getTasksListUrl() {
		$taskModel = Vtiger_Module_Model::getInstance('ProjectTask');
		return $taskModel->getListViewUrl();
	}
}
?>
