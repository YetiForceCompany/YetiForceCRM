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

class Products_Relation_Model extends Vtiger_Relation_Model
{

	public function getQueuury($recordModel, $actions = false, $relationListView_Model = false)
	{

		if ($functionName == 'get_product_pricebooks') {
			$selectColumnSql = $selectColumnSql . ' ,vtiger_pricebookproductrel.listprice, vtiger_pricebook.currency_id, vtiger_products.unit_price';
		} elseif ($functionName == 'get_service_pricebooks') {
			$selectColumnSql = $selectColumnSql . ' ,vtiger_pricebookproductrel.listprice, vtiger_pricebook.currency_id, vtiger_service.unit_price';
		} elseif ($functionName == 'get_many_to_many' && $relatedModuleName == 'IStorages') {
			$referenceInfo = Vtiger_Relation_Model::getReferenceTableInfo($relatedModuleName, $parentModuleName);
			$selectColumnSql = $selectColumnSql . ' ,' . $referenceInfo['table'] . '.qtyinstock';
		}
		return $query;
	}

	/**
	 * Function that deletes PriceBooks related records information
	 * @param <Integer> $sourceRecordId - Product/Service Id
	 * @param <Integer> $relatedRecordId - Related Record Id
	 */
	public function deleteRelation($sourceRecordId, $relatedRecordId)
	{
		$sourceModuleName = $this->getParentModuleModel()->get('name');
		$relatedModuleName = $this->getRelationModuleModel()->get('name');
		if (($sourceModuleName == 'Products' || $sourceModuleName == 'Services') && $relatedModuleName == 'PriceBooks') {
			//Description: deleteListPrice function is deleting the relation between Pricebook and Product/Service 
			$priceBookModel = Vtiger_Record_Model::getInstanceById($relatedRecordId, $relatedModuleName);
			$priceBookModel->deleteListPrice($sourceRecordId);
		} else if ($sourceModuleName == $relatedModuleName) {
			$this->deleteProductToProductRelation($sourceRecordId, $relatedRecordId);
		} else {
			parent::deleteRelation($sourceRecordId, $relatedRecordId);
		}
	}

	/**
	 * Function to delete the product to product relation(product bundles)
	 * @param type $sourceRecordId
	 * @param type $relatedRecordId true / false
	 * @return <boolean>
	 */
	public function deleteProductToProductRelation($sourceRecordId, $relatedRecordId)
	{
		$db = PearDatabase::getInstance();
		if (!empty($sourceRecordId) && !empty($relatedRecordId)) {
			$db->pquery('DELETE FROM vtiger_seproductsrel WHERE crmid = ? && productid = ?', array($relatedRecordId, $sourceRecordId));
			return true;
		}
	}

	public function isSubProduct($subProductId)
	{
		if (!empty($subProductId)) {
			$db = PearDatabase::getInstance();
			$result = $db->pquery('SELECT crmid FROM vtiger_seproductsrel WHERE crmid = ?', array($subProductId));
			if ($db->num_rows($result) > 0) {
				return true;
			}
		}
	}

	/**
	 * Function to add Products/Services-PriceBooks Relation
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
		$productModel->updateListPrice($destinationRecordId, $listPrice, $relationModuleModel->get('currency_id'));
	}

	/**
	 * Get products
	 */
	public function getProducts()
	{
		$queryGenerator = $this->getQueryGenerator();
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_seproductsrel', 'vtiger_seproductsrel.crmid = vtiger_products.productid AND vtiger_seproductsrel.setype=:module', [':module' => 'Products']]);
		$queryGenerator->addAndConditionNative(['vtiger_seproductsrel.productid' => $this->get('parentRecord')->getId()]);
	}

	/**
	 * Get products pricebooks
	 */
	public function getProductPricebooks()
	{
		$queryGenerator = $this->getQueryGenerator();
		$queryGenerator->setCustomColumn('vtiger_pricebookproductrel.productid as prodid');
		$queryGenerator->setCustomColumn('vtiger_pricebookproductrel.listprice');
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_pricebookproductrel', 'vtiger_pricebook.pricebookid = vtiger_pricebookproductrel.pricebookid']);
		$queryGenerator->addAndConditionNative(['vtiger_pricebookproductrel.productid' => $this->get('parentRecord')->getId()]);
	}

	/**
	 * Get parent products
	 */
	public function getParentProducts()
	{
		$queryGenerator = $this->getQueryGenerator();
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_seproductsrel', 'vtiger_products.productid = vtiger_seproductsrel.productid']);
		$queryGenerator->addAndConditionNative(['vtiger_seproductsrel.setype' => 'Products', 'vtiger_seproductsrel.crmid' => $this->get('parentRecord')->getId()]);
	}
}
