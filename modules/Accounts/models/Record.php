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

class Accounts_Record_Model extends Vtiger_Record_Model
{
	/**
	 * Function returns the details of Accounts Hierarchy.
	 *
	 * @return array
	 */
	public function getAccountHierarchy()
	{
		$focus = CRMEntity::getInstance($this->getModuleName());
		$hierarchy = $focus->getAccountHierarchy($this->getId());
		foreach ($hierarchy['entries'] as $accountId => $accountInfo) {
			$link = $accountInfo[0]['data'];
			preg_match('/<a href="+/', $link, $matches);
			if (!empty($matches)) {
				preg_match('/[.\s]+/', $link, $dashes);
				preg_match('/<a(.*)>(.*)<\\/a>/i', $link, $name);

				$recordModel = Vtiger_Record_Model::getCleanInstance('Accounts');
				$recordModel->setId($accountId);
				$hierarchy['entries'][$accountId][0]['data'] = $dashes[0] . '<a href=' . $recordModel->getDetailViewUrl() . '>' . $name[2] . '</a>';
			}
		}
		return $hierarchy;
	}
}
