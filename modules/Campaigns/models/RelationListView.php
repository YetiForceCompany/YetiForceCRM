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
	 * Function to get the links for related list.
	 *
	 * @return Vtiger_Link_Model[] List of action models Vtiger_Link_Model
	 */
	public function getLinks()
	{
		$relatedLinks = parent::getLinks();
		$relationModel = $this->getRelationModel();
		$relatedModuleModel = $relationModel->getRelationModuleModel();
		$relatedModuleName = $relatedModuleModel->getName();
		if (in_array($relatedModuleName, ['Accounts', 'Leads', 'Vendors', 'Contacts', 'Partners', 'Competition']) && $relatedModuleModel->isPermitted('MassComposeEmail') && AppConfig::main('isActiveSendingMails') && App\Mail::getDefaultSmtp()) {
			$emailLink = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => \App\Language::translate('LBL_SEND_EMAIL', $relatedModuleName),
				'linkurl' => 'javascript:Campaigns_RelatedList_Js.triggerSendEmail();',
				'linkicon' => 'fas fa-envelope',
			]);
			$emailLink->set('_sendEmail', true);
			$relatedLinks['LISTVIEWBASIC'][] = $emailLink;
		}
		return $relatedLinks;
	}
}
