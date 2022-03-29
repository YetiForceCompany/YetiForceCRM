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

class Accounts_DetailView_Model extends Vtiger_DetailView_Model
{
	/** {@inheritdoc} */
	public function getDetailViewLinks(array $linkParams): array
	{
		$linkModelList = parent::getDetailViewLinks($linkParams);
		if ($this->getRecord()->isEditable() && $this->getModule()->isPermitted('DetailTransferOwnership')) {
			$massActionLink = [
				'linktype' => 'DETAIL_VIEW_BASIC',
				'linklabel' => 'LBL_TRANSFER_OWNERSHIP',
				'linkurl' => 'javascript:Vtiger_Detail_Js.triggerTransferOwnership("index.php?module=' . $this->getModule()->getName() . '&view=TransferOwnership&record=' . $this->record->getId() . '")',
				'linkclass' => 'btn-outline-dark btn-sm',
				'linkicon' => 'yfi yfi-change-of-owner',
			];
			$linkModelList['DETAIL_VIEW_BASIC'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}
		return $linkModelList;
	}

	/** {@inheritdoc} */
	public function getDetailViewRelatedLinks()
	{
		$recordModel = $this->getRecord();
		$moduleName = $recordModel->getModuleName();
		$parentModuleModel = $this->getModule();
		$this->getWidgets();
		$relatedLinks = [];
		if (class_exists($parentModuleModel->getName() . '_ProcessWizard_Model') && $recordModel->isEditable()) {
			$relatedLinks[] = [
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => 'LBL_RECORD_PROCESS_WIZARD',
				'linkKey' => 'LBL_RECORD_PROCESS_WIZARD',
				'linkurl' => $recordModel->getDetailViewUrl() . '&mode=processWizard',
				'linkicon' => '',
				'related' => 'Summary',
			];
		}
		if ($parentModuleModel->isSummaryViewSupported() && $this->widgetsList) {
			$relatedLinks[] = [
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => 'LBL_RECORD_SUMMARY',
				'linkKey' => 'LBL_RECORD_SUMMARY',
				'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showDetailViewByMode&requestMode=summary',
				'linkicon' => '',
				'related' => 'Summary',
			];
		}
		//link which shows the summary information(generally detail of record)
		$relatedLinks[] = [
			'linktype' => 'DETAILVIEWTAB',
			'linklabel' => 'LBL_RECORD_DETAILS',
			'linkKey' => 'LBL_RECORD_DETAILS',
			'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showDetailViewByMode&requestMode=full',
			'linkicon' => '',
			'related' => 'Details',
		];
		if (App\Config::module($moduleName, 'SHOW_SUMMARY_PRODUCTS_SERVICES')) {
			$relations = \Vtiger_Relation_Model::getAllRelations($parentModuleModel, false, true, true, 'modulename');
			if (isset($relations['Products']) || isset($relations['Services']) || isset($relations['OSSOutsourcedServices']) || isset($relations['Assets']) || isset($relations['OSSSoldServices']) || isset($relations['OutsourcedProducts'])) {
				$relatedLinks[] = [
					'linktype' => 'DETAILVIEWTAB',
					'linklabel' => 'LBL_RECORD_SUMMARY_PRODUCTS_SERVICES',
					'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showRelatedProductsServices&requestMode=summary',
					'linkicon' => '',
					'linkKey' => 'LBL_RECORD_SUMMARY',
					'related' => 'ProductsAndServices',
					'countRelated' => App\Config::relation('SHOW_RECORDS_COUNT'),
				];
			}
		}
		$modCommentsModel = Vtiger_Module_Model::getInstance('ModComments');
		if ($parentModuleModel->isCommentEnabled() && $modCommentsModel->isPermitted('DetailView')) {
			$relatedLinks[] = [
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => 'ModComments',
				'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showAllComments',
				'linkicon' => '',
				'related' => $modCommentsModel->getName(),
				'countRelated' => App\Config::relation('SHOW_RECORDS_COUNT'),
			];
		}
		if ($parentModuleModel->isTrackingEnabled() && $parentModuleModel->isPermitted('ModTracker')) {
			$relatedLinks[] = [
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => 'LBL_UPDATES',
				'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showRecentActivities&page=1',
				'linkicon' => '',
				'related' => 'ModTracker',
				'countRelated' => App\Config::module('ModTracker', 'UNREVIEWED_COUNT') && $parentModuleModel->isPermitted('ReviewingUpdates'),
				'badgeClass' => 'bgDanger',
			];
		}
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if ($userPrivilegesModel->hasModulePermission('OpenStreetMap')) {
			$relatedLinks[] = [
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => 'LBL_MAP',
				'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showOpenStreetMap',
				'linkicon' => '',
			];
		}
		if (
			\App\User::getCurrentUserId() === \App\User::getCurrentUserRealId()
			&& \App\Module::isModuleActive('Chat') && !\App\RequestUtil::getBrowserInfo()->ie
			&& false !== \App\ModuleHierarchy::getModuleLevel($parentModuleModel->getName())
		) {
			$relatedLinks[] = [
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => 'LBL_CHAT',
				'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showChat',
				'linkicon' => 'fas fa-comments',
			];
		}
		foreach ($parentModuleModel->getRelations() as $relation) {
			if ($relation->isRelatedViewType('RelatedTab')) {
				$relatedLinks[] = [
					'linktype' => 'DETAILVIEWRELATED',
					'linklabel' => $relation->get('label'),
					'linkurl' => $relation->getListUrl($recordModel),
					'linkicon' => '',
					'relatedModuleName' => $relation->get('relatedModuleName'),
					'relationId' => $relation->getId(),
				];
			}
		}
		return $relatedLinks;
	}
}
