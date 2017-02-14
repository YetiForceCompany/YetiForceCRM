<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Project_DetailView_Model extends Vtiger_DetailView_Model
{

	public function getDetailViewLinks($linkParams)
	{
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$recordModel = $this->getRecord();
		$linkModelList = parent::getDetailViewLinks($linkParams);
		$recordId = $recordModel->getId();

		if (Users_Privileges_Model::isPermitted('ProjectTask', 'EditView')) {
			$viewLinks = [
				'linktype' => 'DETAILVIEW',
				'linklabel' => 'Add Project Task',
				'linkurl' => 'index.php?module=ProjectTask&action=EditView&projectid=' . $recordId . '&return_module=Project&return_action=DetailView&return_id=' . $recordId,
				'linkicon' => 'glyphicon glyphicon-tasks',
				'linkhint' => 'Add Project Task',
			];
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($viewLinks);
		}
		if (Users_Privileges_Model::isPermitted('Documents', 'EditView')) {
			$viewLinks = [
				'linktype' => 'DETAILVIEW',
				'linklabel' => 'Add Note',
				'linkurl' => 'index.php?module=Documents&action=EditView&return_module=Project&return_action=DetailView&return_id=' . $recordId . '&parent_id=' . $recordId,
				'linkicon' => 'glyphicon glyphicon-file',
				'linkhint' => 'Add Note',
			];
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($viewLinks);
		}
		return $linkModelList;
	}

	/**
	 * Function to get the detail view related links
	 * @return <array> - list of links parameters
	 */
	public function getDetailViewRelatedLinks()
	{
		$recordModel = $this->getRecord();
		$moduleName = $recordModel->getModuleName();
		$relatedLinks = parent::getDetailViewRelatedLinks();
		$parentModel = Vtiger_Module_Model::getInstance('OSSTimeControl');
		if ($parentModel->isActive()) {
			$relatedLinks[] = [
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => vtranslate('LBL_CHARTS', $moduleName),
				'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showCharts&requestMode=charts',
				'linkicon' => '',
				'linkKey' => 'LBL_RECORD_SUMMARY',
				'related' => 'Charts'
			];
		}
		if (!Settings_ModuleManager_Library_Model::checkLibrary('Gantt')) {
			$relatedLinks[] = [
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => vtranslate('LBL_GANTT', $moduleName),
				'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showGantt',
				'linkicon' => '',
				'linkKey' => 'LBL_GANTT',
				'related' => 'Gantt'
			];
		}
		return $relatedLinks;
	}
}
