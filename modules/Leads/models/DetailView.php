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

class Leads_DetailView_Model extends Accounts_DetailView_Model
{
	/** {@inheritdoc} */
	public function getDetailViewLinks(array $linkParams): array
	{
		$linkModelList = parent::getDetailViewLinks($linkParams);
		$recordModel = $this->getRecord();
		foreach ($linkModelList['DETAIL_VIEW_BASIC'] as $index => $link) {
			if ('View History' == $link->linklabel) {
				unset($linkModelList['DETAIL_VIEW_BASIC'][$index]);
			} elseif ('LBL_SHOW_ACCOUNT_HIERARCHY' == $link->linklabel) {
				$link->linklabel = 'LBL_SHOW_ACCOUNT_HIERARCHY';
				$linkURL = 'index.php?module=Accounts&view=AccountHierarchy&record=' . $recordModel->getId();
				$link->linkurl = 'javascript:Accounts_Detail_Js.triggerAccountHierarchy("' . $linkURL . '");';
				unset($linkModelList['DETAIL_VIEW_BASIC'][$index]);
				$linkModelList['DETAIL_VIEW_BASIC'][$index] = $link;
			} elseif ('LBL_TRANSFER_OWNERSHIP' == $link->linklabel) {
				unset($linkModelList['DETAIL_VIEW_BASIC'][$index]);
			}
		}
		if (!$recordModel->isReadOnly() && $recordModel->isPermitted('ConvertLead') && $recordModel->isEditable()) {
			$convert = !Leads_Module_Model::checkIfAllowedToConvert($recordModel->get('leadstatus')) ? 'd-none' : '';
			$linkModelList['DETAIL_VIEW_ADDITIONAL'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'DETAIL_VIEW_ADDITIONAL',
				'linklabel' => '',
				'linkclass' => 'btn-sm btn-outline-info btn-convertLead ' . $convert,
				'linkhint' => \App\Language::translate('LBL_CONVERT_LEAD', $this->getModule()->getName()),
				'linkurl' => 'javascript:Leads_Detail_Js.convertLead("' . $recordModel->getConvertLeadUrl() . '",this);',
				'linkicon' => 'fas fa-exchange-alt',
			]);
		}
		return $linkModelList;
	}
}
