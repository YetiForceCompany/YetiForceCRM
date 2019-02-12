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

class Accounts_Record_Model extends Vtiger_Record_Model
{
	/**
	 * Function returns the details of Accounts Hierarchy.
	 *
	 * @return <Array>
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
				preg_match("/<a(.*)>(.*)<\/a>/i", $link, $name);

				$recordModel = Vtiger_Record_Model::getCleanInstance('Accounts');
				$recordModel->setId($accountId);
				$hierarchy['entries'][$accountId][0]['data'] = $dashes[0] . '<a href=' . $recordModel->getDetailViewUrl() . '>' . $name[2] . '</a>';
			}
		}
		return $hierarchy;
	}

	/**
	 * Function to get List of Fields which are related from Accounts to Inventory Record.
	 *
	 * @return <array>
	 */
	public function getInventoryMappingFields()
	{
		return [
			['parentField' => 'buildingnumbera', 'inventoryField' => 'buildingnumbera', 'defaultValue' => ''],
			['parentField' => 'localnumbera', 'inventoryField' => 'localnumbera', 'defaultValue' => ''],
			['parentField' => 'addresslevel1a', 'inventoryField' => 'addresslevel1a', 'defaultValue' => ''],
			['parentField' => 'addresslevel2a', 'inventoryField' => 'addresslevel2a', 'defaultValue' => ''],
			['parentField' => 'addresslevel3a', 'inventoryField' => 'addresslevel3a', 'defaultValue' => ''],
			['parentField' => 'addresslevel4a', 'inventoryField' => 'addresslevel4a', 'defaultValue' => ''],
			['parentField' => 'addresslevel5a', 'inventoryField' => 'addresslevel5a', 'defaultValue' => ''],
			['parentField' => 'addresslevel6a', 'inventoryField' => 'addresslevel6a', 'defaultValue' => ''],
			['parentField' => 'addresslevel7a', 'inventoryField' => 'addresslevel7a', 'defaultValue' => ''],
			['parentField' => 'addresslevel8a', 'inventoryField' => 'addresslevel8a', 'defaultValue' => ''],
			['parentField' => 'buildingnumberc', 'inventoryField' => 'buildingnumberb', 'defaultValue' => ''],
			['parentField' => 'localnumberc', 'inventoryField' => 'localnumberb', 'defaultValue' => ''],
			['parentField' => 'addresslevel1c', 'inventoryField' => 'addresslevel1b', 'defaultValue' => ''],
			['parentField' => 'addresslevel2c', 'inventoryField' => 'addresslevel2b', 'defaultValue' => ''],
			['parentField' => 'addresslevel3c', 'inventoryField' => 'addresslevel3b', 'defaultValue' => ''],
			['parentField' => 'addresslevel4c', 'inventoryField' => 'addresslevel4b', 'defaultValue' => ''],
			['parentField' => 'addresslevel5c', 'inventoryField' => 'addresslevel5b', 'defaultValue' => ''],
			['parentField' => 'addresslevel6c', 'inventoryField' => 'addresslevel6b', 'defaultValue' => ''],
			['parentField' => 'addresslevel7c', 'inventoryField' => 'addresslevel7b', 'defaultValue' => ''],
			['parentField' => 'addresslevel8c', 'inventoryField' => 'addresslevel8b', 'defaultValue' => ''],
		];
	}
}
