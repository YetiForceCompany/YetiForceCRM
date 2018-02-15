<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vendors_Record_Model extends Vtiger_Record_Model
{
	/**
	 * Function to get List of Fields which are related from Vendors to Inventyory Record.
	 *
	 * @return <array>
	 */
	public function getInventoryMappingFields()
	{
		return [
			//array('parentField'=>'buildingnumbera', 'inventoryField'=>'buildingnumbera', 'defaultValue'=>''),
			//array('parentField'=>'localnumbera', 'inventoryField'=>'localnumbera', 'defaultValue'=>''),
			['parentField' => 'addresslevel1a', 'inventoryField' => 'addresslevel1a', 'defaultValue' => ''],
			['parentField' => 'addresslevel2a', 'inventoryField' => 'addresslevel2a', 'defaultValue' => ''],
			['parentField' => 'addresslevel3a', 'inventoryField' => 'addresslevel3a', 'defaultValue' => ''],
			['parentField' => 'addresslevel4a', 'inventoryField' => 'addresslevel4a', 'defaultValue' => ''],
			['parentField' => 'addresslevel5a', 'inventoryField' => 'addresslevel5a', 'defaultValue' => ''],
			['parentField' => 'addresslevel6a', 'inventoryField' => 'addresslevel6a', 'defaultValue' => ''],
			['parentField' => 'addresslevel7a', 'inventoryField' => 'addresslevel7a', 'defaultValue' => ''],
			['parentField' => 'addresslevel8a', 'inventoryField' => 'addresslevel8a', 'defaultValue' => ''],
			//array('parentField'=>'buildingnumberb', 'inventoryField'=>'buildingnumberb', 'defaultValue'=>''),
			//array('parentField'=>'localnumberb', 'inventoryField'=>'localnumberb', 'defaultValue'=>''),
			['parentField' => 'addresslevel1b', 'inventoryField' => 'addresslevel1b', 'defaultValue' => ''],
			['parentField' => 'addresslevel2b', 'inventoryField' => 'addresslevel2b', 'defaultValue' => ''],
			['parentField' => 'addresslevel3b', 'inventoryField' => 'addresslevel3b', 'defaultValue' => ''],
			['parentField' => 'addresslevel4b', 'inventoryField' => 'addresslevel4b', 'defaultValue' => ''],
			['parentField' => 'addresslevel5b', 'inventoryField' => 'addresslevel5b', 'defaultValue' => ''],
			['parentField' => 'addresslevel6b', 'inventoryField' => 'addresslevel6b', 'defaultValue' => ''],
			['parentField' => 'addresslevel7b', 'inventoryField' => 'addresslevel7b', 'defaultValue' => ''],
			['parentField' => 'addresslevel8b', 'inventoryField' => 'addresslevel8b', 'defaultValue' => ''],
		];
	}
}
