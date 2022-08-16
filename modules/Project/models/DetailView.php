<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * *********************************************************************************** */

class Project_DetailView_Model extends Vtiger_DetailView_Model
{
	/** {@inheritdoc} */
	public function getDetailViewLinks(array $linkParams): array
	{
		$recordModel = $this->getRecord();
		$linkModelList = parent::getDetailViewLinks($linkParams);
		if (!$recordModel->isReadOnly()) {
			$recordId = $recordModel->getId();
			if (\App\Privilege::isPermitted('ProjectTask', 'EditView')) {
				$linkModelList['DETAIL_VIEW_BASIC'][] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'DETAIL_VIEW_BASIC',
					'linklabel' => 'Add Project Task',
					'linkurl' => "index.php?module=ProjectTask&action=EditView&projectid={$recordId}&return_module=Project&return_action=DetailView&return_id={$recordId}",
					'linkicon' => 'fas fa-tasks',
					'linkhint' => 'Add Project Task',
					'linkclass' => 'btn-outline-dark btn-sm'
				]);
			}
			if (\App\Privilege::isPermitted('Documents', 'EditView')) {
				$linkModelList['DETAIL_VIEW_BASIC'][] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'DETAIL_VIEW_BASIC',
					'linklabel' => 'Add Note',
					'linkurl' => "index.php?module=Documents&action=EditView&return_module=Project&return_action=DetailView&return_id={$recordId}&parent_id={$recordId}",
					'linkicon' => 'fas fa-file',
					'linkhint' => 'Add Note',
					'linkclass' => 'btn-outline-dark btn-sm'
				]);
			}
		}
		return $linkModelList;
	}

	/** {@inheritdoc} */
	public function getDetailViewRelatedLinks()
	{
		$recordModel = $this->getRecord();
		$moduleName = $recordModel->getModuleName();
		$relatedLinks = parent::getDetailViewRelatedLinks();
		if (\App\Config::module('Project', 'showGanttTab', true) && \App\Module::isModuleActive('ProjectTask') && \App\Module::isModuleActive('ProjectMilestone')) {
			$relatedLinks[] = [
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => \App\Language::translate('LBL_GANTT', $moduleName),
				'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showGantt',
				'linkicon' => '',
				'linkKey' => 'LBL_GANTT',
				'related' => 'Gantt',
			];
		}
		return $relatedLinks;
	}
}
