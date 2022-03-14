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

class PriceBooks_Relation_Model extends Vtiger_Relation_Model
{
	/**
	 * Function to add PriceBook-Products/Services Relation.
	 *
	 * @param int    $sourceRecordId
	 * @param int    $destinationRecordId
	 * @param mixed  $value
	 * @param string $name
	 */
	public function addListPrice(int $sourceRecordId, int $destinationRecordId, $value, string $name = 'listprice')
	{
		$priceBookModel = PriceBooks_Record_Model::getInstanceById($sourceRecordId, $this->getParentModuleModel()->get('name'));
		return $priceBookModel->updateListPrice($destinationRecordId, $value, $name);
	}
}
