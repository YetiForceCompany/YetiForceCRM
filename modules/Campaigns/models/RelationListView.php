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
			if (AppConfig::main('isActiveSendingMails')) {
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
	 * @param Vtiger_Paging_Model $pagingModel
	 * @return Vtiger_Record_Model[] List of record models 
	 */
	public function getEntries(Vtiger_Paging_Model $pagingModel)
	{
		$relationModel = $this->getRelationModel();
		$parentRecordModel = $this->getParentRecordModel();
		$relatedModuleName = $relationModel->getRelationModuleModel()->getName();

		$relatedRecordModelsList = parent::getEntries($pagingModel);
		if (in_array($relatedModuleName, ['Accounts', 'Leads', 'Vendors', 'Contacts', 'Partners', 'Competition']) && $relatedRecordModelsList) {
			$dataReader = (new App\Db\Query())->select(['campaignrelstatus', 'crmid'])->from('vtiger_campaign_records')
					->innerJoin('vtiger_campaignrelstatus', 'vtiger_campaignrelstatus.campaignrelstatusid = vtiger_campaign_records.campaignrelstatusid')
					->where(['crmid' => array_keys($relatedRecordModelsList), 'campaignid' => $parentRecordModel->getId()])
					->createCommand()->query();
			while ($row = $dataReader->read()) {
				$recordId = $row['crmid'];
				$relatedRecordModel = $relatedRecordModelsList[$recordId];
				$relatedRecordModel->set('status', $row['campaignrelstatus']);
				$relatedRecordModelsList[$recordId] = $relatedRecordModel;
			}
		}
		return $relatedRecordModelsList;
	}
}
