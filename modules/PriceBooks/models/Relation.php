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

class PriceBooks_Relation_Model extends Vtiger_Relation_Model
{

	/**
	 * Function to add PriceBook-Products/Services Relation
	 * @param <Integer> $sourceRecordId
	 * @param <Integer> $destinationRecordId
	 * @param <Integer> $listPrice
	 */
	public function addListPrice($sourceRecordId, $destinationRecordId, $listPrice)
	{
		$sourceModuleName = $this->getParentModuleModel()->get('name');

		$priceBookModel = Vtiger_Record_Model::getInstanceById($sourceRecordId, $sourceModuleName);
		$priceBookModel->updateListPrice($destinationRecordId, $listPrice);
	}

	/**
	 * Function that deletes PriceBooks related records information
	 * @param <Integer> $sourceRecordId - PriceBook Id
	 * @param <Integer> $relatedRecordId - Related Record Id
	 */
	public function deleteRelation($sourceRecordId, $relatedRecordId)
	{
		$sourceModuleName = $this->getParentModuleModel()->get('name');
		$destinationModuleName = $this->getRelationModuleModel()->get('name');
		if ($sourceModuleName == 'PriceBooks' && ($destinationModuleName == 'Products' || $destinationModuleName == 'Services')) {
			$priceBookModel = Vtiger_Record_Model::getInstanceById($sourceRecordId, $sourceModuleName);
			$priceBookModel->deleteListPrice($relatedRecordId);
		} else {
			parent::deleteRelation($sourceRecordId, $relatedRecordId);
		}
	}
}
