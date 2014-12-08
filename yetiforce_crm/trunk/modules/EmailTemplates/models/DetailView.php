<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class EmailTemplates_DetailView_Model extends Vtiger_DetailView_Model {
	
	/**
	 * Function to get the detail view links (links and widgets)
	 * @param <array> $linkParams - parameters which will be used to calicaulate the params
	 * @return <array> - array of link models in the format as below
	 *                   array('linktype'=>list of link models);
	 */
	public function getDetailViewLinks($linkParams) {
		$linkTypes = array('DETAILVIEWBASIC','DETAILVIEW');
		$moduleModel = $this->getModule();
		$recordModel = $this->getRecord();

		$moduleName = $moduleModel->getName();
		$recordId = $recordModel->getId();

		$detailViewLink = array();

		$detailViewLinks[] = array(
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => 'LBL_EDIT',
				'linkurl' => $recordModel->getEditViewUrl(),
				'linkicon' => ''
		);

		foreach ($detailViewLinks as $detailViewLink) {
			$linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($detailViewLink);
		}

		$linkModelListDetails = Vtiger_Link_Model::getAllByType($moduleModel->getId(),$linkTypes,$linkParams);
		$detailViewBasiclinks = $linkModelListDetails['DETAILVIEWBASIC'];
		unset($linkModelListDetails['DETAILVIEWBASIC']);

			$deletelinkModel = array(
					'linktype' => 'DETAILVIEW',
					'linklabel' => 'LBL_DELETE',
					'linkurl' => 'javascript:Vtiger_Detail_Js.deleteRecord("'.$recordModel->getDeleteUrl().'")',
					'linkicon' => ''
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($deletelinkModel);

		if(!empty($detailViewBasiclinks)) {
			foreach($detailViewBasiclinks as $linkModel) {
				$linkModelList['DETAILVIEW'][] = $linkModel;
			}
		}
		return $linkModelList;
	}
	
	/**
	 * Function to get the Quick Links for the Detail view of the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$linkTypes = array('SIDEBARLINK', 'SIDEBARWIDGET');
		$moduleLinks = $this->getModule()->getSideBarLinks($linkTypes);
		
		$listLinkTypes = array('DETAILVIEWSIDEBARLINK', 'DETAILVIEWSIDEBARWIDGET');
		$listLinks = Vtiger_Link_Model::getAllByType($this->getModule()->getId(), $listLinkTypes);
		
		if($listLinks['DETAILVIEWSIDEBARLINK']) {
			foreach($listLinks['DETAILVIEWSIDEBARLINK'] as $link) {
				$link->linkurl = $link->linkurl.'&record='.$this->getRecord()->getId().'&source_module='.$this->getModule()->getName();
				$moduleLinks['SIDEBARLINK'][] = $link;
			}
		}

		if($listLinks['DETAILVIEWSIDEBARWIDGET']) {
			foreach($listLinks['DETAILVIEWSIDEBARWIDGET'] as $link) {
				$link->linkurl = $link->linkurl.'&record='.$this->getRecord()->getId().'&source_module='.$this->getModule()->getName();
				$moduleLinks['SIDEBARWIDGET'][] = $link;
			}
		}
		
		return $moduleLinks;
	}
	
	/**
	 * Function to get the instance
	 * @param <String> $moduleName - module name
	 * @param <String> $recordId - record id
	 * @return <Vtiger_DetailView_Model>
	 */
	public static function getInstance($moduleName,$recordId) {
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'DetailView', $moduleName);
		$instance = new $modelClassName();

		$moduleModel = EmailTemplates_Module_Model::getInstance($moduleName);
		$recordModel = EmailTemplates_Record_Model::getInstanceById($recordId, $moduleName);
		
		return $instance->setModule($moduleModel)->setRecord($recordModel);
	}
}