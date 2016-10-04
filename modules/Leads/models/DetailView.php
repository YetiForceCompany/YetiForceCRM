<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * *********************************************************************************** */

class Leads_DetailView_Model extends Accounts_DetailView_Model
{

	/**
	 * Function to get the detail view links (links and widgets)
	 * @param <array> $linkParams - parameters which will be used to calicaulate the params
	 * @return <array> - array of link models in the format as below
	 *                   array('linktype'=>list of link models);
	 */
	public function getDetailViewLinks($linkParams)
	{
		$linkModelList = Vtiger_DetailView_Model::getDetailViewLinks($linkParams);
		$recordModel = $this->getRecord();
		$moduleModel = $this->getModule();
		$moduleName = $moduleModel->getName();
		$recordId = $recordModel->getId();

		$index = 0;
		foreach ($linkModelList['DETAILVIEW'] as $link) {
			if ($link->linklabel == 'View History' || $link->linklabel == 'Send SMS') {
				unset($linkModelList['DETAILVIEW'][$index]);
			} else if ($link->linklabel == 'LBL_SHOW_ACCOUNT_HIERARCHY') {
				$link->linklabel = 'LBL_SHOW_ACCOUNT_HIERARCHY';
				$linkURL = 'index.php?module=Accounts&view=AccountHierarchy&record=' . $recordId;
				$link->linkurl = 'javascript:Accounts_Detail_Js.triggerAccountHierarchy("' . $linkURL . '");';
				unset($linkModelList['DETAILVIEW'][$index]);
				$linkModelList['DETAILVIEW'][$index] = $link;
			}
			$index++;
		}

		if (Users_Privileges_Model::isPermitted($moduleModel->getName(), 'ConvertLead', $recordModel->getId()) && Users_Privileges_Model::isPermitted($moduleModel->getName(), 'EditView', $recordModel->getId())) {
			$convert = !Leads_Module_Model::checkIfAllowedToConvert($recordModel->get('leadstatus')) ? 'hide' : '';
			$basicActionLink = array(
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => '',
				'linkclass' => 'btn-info btn-convertLead ' . $convert,
				'linkhint' => vtranslate('LBL_CONVERT_LEAD', $moduleName),
				'linkurl' => 'javascript:Leads_Detail_Js.convertLead("' . $recordModel->getConvertLeadUrl() . '",this);',
				'linkicon' => 'glyphicon glyphicon-transfer',
			);
			$linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}
		return $linkModelList;
	}
}
