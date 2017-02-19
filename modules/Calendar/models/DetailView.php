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

class Calendar_DetailView_Model extends Vtiger_DetailView_Model
{

	/**
	 * Function to get the detail view related links
	 * @return <array> - list of links parameters
	 */
	public function getDetailViewRelatedLinks()
	{
		$recordModel = $this->getRecord();
		$moduleName = $recordModel->getType();
		$relatedLinks = array();
		//link which shows the summary information(generally detail of record)
		$relatedLinks[] = array(
			'linktype' => 'DETAILVIEWTAB',
			'linklabel' => vtranslate('LBL_RECORD_DETAILS', $moduleName),
			'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showDetailViewByMode&requestMode=full',
			'linkicon' => '',
			'linkKey' => 'LBL_RECORD_DETAILS',
			'related' => 'Details'
		);

		$parentModuleModel = $this->getModule();
		if ($parentModuleModel->isTrackingEnabled()) {
			$relatedLinks[] = [
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => 'LBL_UPDATES',
				'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showRecentActivities&page=1',
				'linkicon' => '',
				'related' => 'Updates',
				'countRelated' => AppConfig::module('ModTracker', 'UNREVIEWED_COUNT') && $parentModuleModel->isPermitted('ReviewingUpdates'),
				'badgeClass' => 'bgDanger'
			];
		}
		return $relatedLinks;
	}

	/**
	 * Function to get the detail view links (links and widgets)
	 * @param <array> $linkParams - parameters which will be used to calicaulate the params
	 * @return <array> - array of link models in the format as below
	 *                   array('linktype'=>list of link models);
	 */
	public function getDetailViewLinks($linkParams)
	{
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$linkModelList = parent::getDetailViewLinks($linkParams);
		$recordModel = $this->getRecord();
		$moduleName = $recordModel->getModuleName();
		$recordId = $recordModel->getId();
		$status = $recordModel->get('activitystatus');
		$statusActivity = Calendar_Module_Model::getComponentActivityStateLabel('current');

		if ($recordModel->isEditable() && $this->getModule()->isPermitted('DetailView') && isPermitted($moduleName, 'ActivityComplete', $recordId) == 'yes' && isPermitted($moduleName, 'ActivityCancel', $recordId) == 'yes' && isPermitted($moduleName, 'ActivityPostponed', $recordId) == 'yes' && in_array($status, $statusActivity)) {
			$basicActionLink = [
				'linktype' => 'DETAILVIEW',
				'linklabel' => 'LBL_SET_RECORD_STATUS',
				'linkurl' => '#',
				'linkdata' => ['url' => $recordModel->getActivityStateModalUrl()],
				'linkicon' => 'glyphicon glyphicon-ok',
				'linkclass' => 'showModal closeCalendarRekord'
			];
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}
		if (App\Privilege::isPermitted('OpenStreetMap') && !$recordModel->isEmpty('location')) {
			$basicActionLink = [
				'linktype' => 'DETAILVIEW',
				'linklabel' => 'LBL_SHOW_LOCATION',
				'linkurl' => 'javascript:Vtiger_Index_Js.showLocation(\'' . $recordModel->get('location') . '\')',
				'linkicon' => 'glyphicon glyphicon-map-marker',
			];
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}

		if ($recordModel->isDeletable() && $recordModel->get('reapeat') === 1) {
			foreach ($linkModelList['DETAILVIEW'] as $key => $linkObject) {
				if ($linkObject->linklabel == 'LBL_DELETE_RECORD') {
					unset($linkModelList['DETAILVIEW'][$key]);
				}
			}
			$deletelinkModel = [
				'linktype' => 'DETAILVIEW',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => 'javascript:Calendar_Detail_Js.deleteRecord("' . $recordModel->getDeleteUrl() . '")',
				'linkicon' => 'glyphicon glyphicon-trash',
				'title' => App\Language::translate('LBL_DELETE_RECORD')
			];
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($deletelinkModel);
		}
		return $linkModelList;
	}
}
