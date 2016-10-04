<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Campaigns_RelationListView_Model extends Vtiger_RelationListView_Model
{

	/**
	 * Function to get the links for related list
	 * @return <Array> List of action models <Vtiger_Link_Model>
	 */
	public function getLinks()
	{
		$relatedLinks = parent::getLinks();
		$relationModel = $this->getRelationModel();
		$relatedModuleName = $relationModel->getRelationModuleModel()->getName();

		if (in_array($relatedModuleName, ['Accounts', 'Leads', 'Vendors', 'Contacts', 'Partners', 'Competition'])) {
			$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
			if (AppConfig::main('isActiveSendingMails') && Users_Privileges_Model::isPermitted('OSSMail') && !Settings_ModuleManager_Library_Model::checkLibrary('roundcube')) {
				$emailLink = Vtiger_Link_Model::getInstanceFromValues(array(
						'linktype' => 'LISTVIEWBASIC',
						'linklabel' => vtranslate('LBL_SEND_EMAIL', $relatedModuleName),
						'linkurl' => "javascript:Campaigns_RelatedList_Js.triggerSendEmail();",
						'linkicon' => ''
				));
				$emailLink->set('_sendEmail', true);
				$relatedLinks['LISTVIEWBASIC'][] = $emailLink;
			}
		}
		return $relatedLinks;
	}

	/**
	 * Function to get list of record models in this relation
	 * @param <Vtiger_Paging_Model> $pagingModel
	 * @return <array> List of record models <Vtiger_Record_Model>
	 */
	public function getEntries($pagingModel)
	{
		$relationModel = $this->getRelationModel();
		$parentRecordModel = $this->getParentRecordModel();
		$relatedModuleName = $relationModel->getRelationModuleModel()->getName();

		$relatedRecordModelsList = parent::getEntries($pagingModel);
		if (in_array($relatedModuleName, ['Accounts', 'Leads', 'Vendors', 'Contacts', 'Partners', 'Competition']) && $relatedRecordModelsList) {
			$db = PearDatabase::getInstance();
			$relatedRecordIdsList = array_keys($relatedRecordModelsList);

			$query = 'SELECT campaignrelstatus, crmid FROM vtiger_campaign_records
						INNER JOIN vtiger_campaignrelstatus ON vtiger_campaignrelstatus.campaignrelstatusid = vtiger_campaign_records.campaignrelstatusid
						WHERE crmid IN (%s) && campaignid = ?';
			$query = sprintf($query, generateQuestionMarks($relatedRecordIdsList));
			array_push($relatedRecordIdsList, $parentRecordModel->getId());

			$result = $db->pquery($query, $relatedRecordIdsList);
			while ($row = $db->getRow($result)) {
				$recordId = $row['crmid'];
				$relatedRecordModel = $relatedRecordModelsList[$recordId];
				$relatedRecordModel->set('status', $row['campaignrelstatus']);
				$relatedRecordModelsList[$recordId] = $relatedRecordModel;
			}
		}
		return $relatedRecordModelsList;
	}
}
