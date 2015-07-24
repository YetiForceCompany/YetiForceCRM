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
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$linkModelList = Vtiger_DetailView_Model::getDetailViewLinks($linkParams);
		$recordModel = $this->getRecord();
		$moduleModel = $this->getModule();
		$moduleName = $moduleModel->getName();
		$recordId = $recordModel->getId();

		$emailModuleModel = Vtiger_Module_Model::getInstance('OSSMail');
		if ($currentUserModel->hasModulePermission($emailModuleModel->getId())) {
			$config = $emailModuleModel->getComposeParameters();
			$basicActionLink = array(
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => '',
				'linkurl' => $emailModuleModel->getComposeUrl($moduleName, $recordId, 'Detail', $config['popup']),
				'linkicon' => 'glyphicon glyphicon-envelope',
				'linktarget' => $config['target'],
				'linkPopup' => $config['popup'],
				'linkhint' => 'LBL_SEND_EMAIL'
			);
			$linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}

		//TODO: update the database so that these separate handlings are not required
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

		$CalendarActionLinks[] = array();
		$CalendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
		if ($currentUserModel->hasModuleActionPermission($CalendarModuleModel->getId(), 'EditView')) {
			$CalendarActionLinks[] = array(
				'linktype' => 'DETAILVIEW',
				'linklabel' => 'LBL_ADD_EVENT',
				'linkurl' => $recordModel->getCreateEventUrl(),
				'linkicon' => 'glyphicon glyphicon-time'
			);

			$CalendarActionLinks[] = array(
				'linktype' => 'DETAILVIEW',
				'linklabel' => 'LBL_ADD_TASK',
				'linkurl' => $recordModel->getCreateTaskUrl(),
				'linkicon' => 'glyphicon glyphicon-calendar'
			);
		}

		$SMSNotifierModuleModel = Vtiger_Module_Model::getInstance('SMSNotifier');
		if (!empty($SMSNotifierModuleModel) && $currentUserModel->hasModulePermission($SMSNotifierModuleModel->getId())) {
			$basicActionLink = array(
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => 'LBL_SEND_SMS',
				'linkurl' => 'javascript:Vtiger_Detail_Js.triggerSendSms("index.php?module=' . $this->getModule()->getName() .
				'&view=MassActionAjax&mode=showSendSMSForm","SMSNotifier");',
				'linkicon' => 'glyphicon glyphicon-comment',
				'title' => vtranslate('LBL_SEND_SMS')
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}

		foreach ($CalendarActionLinks as $basicLink) {
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicLink);
		}

		if (Users_Privileges_Model::isPermitted($moduleModel->getName(), 'ConvertLead', $recordModel->getId()) && Users_Privileges_Model::isPermitted($moduleModel->getName(), 'EditView', $recordModel->getId())) {
			$moduleInstance = CRMEntity::getInstance($moduleName);
			$basicActionLink = array(
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => '',
				'linkclass' => 'btn-info',
				'linkhint' => vtranslate('LBL_CONVERT_LEAD', $moduleName),
				'linkurl' => 'javascript:Leads_Detail_Js.convertLead("' . $recordModel->getConvertLeadUrl() . '",this);',
				'linkicon' => 'glyphicon glyphicon-transfer',
				'linkgrupclass' => (!in_array($recordModel->get('leadstatus'), $moduleInstance->conversion_available_status)) ? 'hide' : '',
			);
			$linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}
		return $linkModelList;
	}
}
