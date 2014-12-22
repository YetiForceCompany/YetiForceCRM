<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class SMSNotifier_DetailView_Model extends Vtiger_DetailView_Model {
	/**
	 * Function to get the detail view links (links and widgets)
	 * @param <array> $linkParams - parameters which will be used to calicaulate the params
	 * @return <array> - array of link models in the format as below
	 *                   array('linktype'=>list of link models);
	 */
	public function getDetailViewLinks($linkParams) {
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$recordModel = $this->getRecord();

		$linkModelList = parent::getDetailViewLinks($linkParams);
		unset($linkModelList['DETAILVIEWBASIC']);
		$linkModelDetailViewList = $linkModelList['DETAILVIEW'];
		$countOfList = count($linkModelDetailViewList);
		
		for ($i=0; $i<$countOfList; $i++) {
			$linkModel = $linkModelDetailViewList[$i];
			if ($linkModel->get('linklabel') == 'LBL_CHECK_STATUS') {
				$linkModelList['DETAILVIEW'][$i]->set('linklabel', vtranslate('LBL_CHECK_STATUS', 'SMSNotifier'));
				$linkModelList['DETAILVIEW'][$i]->set('linkurl', $recordModel->getCheckStatusUrl());
				break;
			}
		}
		
		return $linkModelList;
	}
}
