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

class Products_Relation_Model extends Vtiger_Relation_Model
{
	public function isSubProduct($subProductId)
	{
		if ($subProductId) {
			return (new \App\Db\Query())->select(['crmid'])->from('vtiger_seproductsrel')->where(['crmid' => $subProductId])->exists();
		}
	}

	/**
	 * Function to add Products/Services-PriceBooks Relation.
	 *
	 * @param <Integer> $sourceRecordId
	 * @param <Integer> $destinationRecordId
	 * @param <Integer> $listPrice
	 */
	public function addListPrice($sourceRecordId, $destinationRecordId, $listPrice)
	{
		$sourceModuleName = $this->getParentModuleModel()->get('name');
		$relatedModuleName = $this->getRelationModuleModel()->get('name');
		$relationModuleModel = Vtiger_Record_Model::getInstanceById($destinationRecordId, $relatedModuleName);

		$productModel = Vtiger_Record_Model::getInstanceById($sourceRecordId, $sourceModuleName);
		return $productModel->updateListPrice($destinationRecordId, $listPrice, $relationModuleModel->get('currency_id'));
	}

	/**
	 * Get parent products.
	 */
	public function getParentProducts()
	{
		$queryGenerator = $this->getQueryGenerator();
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_seproductsrel', 'vtiger_products.productid = vtiger_seproductsrel.productid']);
		$queryGenerator->addNativeCondition(['vtiger_seproductsrel.setype' => 'Products', 'vtiger_seproductsrel.crmid' => $this->get('parentRecord')->getId()]);
	}
}
