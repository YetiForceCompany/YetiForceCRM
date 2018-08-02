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
	/**
	 * Function that deletes PriceBooks related records information.
	 *
	 * @param <Integer> $sourceRecordId  - Product/Service Id
	 * @param <Integer> $relatedRecordId - Related Record Id
	 */
	public function deleteRelation($sourceRecordId, $relatedRecordId)
	{
		$sourceModuleName = $this->getParentModuleModel()->get('name');
		$relatedModuleName = $this->getRelationModuleModel()->get('name');
		if (($sourceModuleName == 'Products' || $sourceModuleName == 'Services') && $relatedModuleName == 'PriceBooks') {
			//Description: deleteListPrice function is deleting the relation between Pricebook and Product/Service
			return Vtiger_Record_Model::getInstanceById($relatedRecordId, $relatedModuleName)->deleteListPrice($sourceRecordId);
		} elseif ($sourceModuleName == $relatedModuleName) {
			return $this->deleteProductToProductRelation($sourceRecordId, $relatedRecordId);
		} else {
			return parent::deleteRelation($sourceRecordId, $relatedRecordId);
		}
	}

	/**
	 * Function to delete the product to product relation(product bundles).
	 *
	 * @param type $sourceRecordId
	 * @param type $relatedRecordId true / false
	 *
	 * @return <boolean>
	 */
	public function deleteProductToProductRelation($sourceRecordId, $relatedRecordId)
	{
		if (!empty($sourceRecordId) && !empty($relatedRecordId)) {
			App\Db::getInstance()->createCommand()->delete('vtiger_seproductsrel', ['crmid' => $relatedRecordId, 'productid' => $sourceRecordId])->execute();

			return true;
		}
		return false;
	}

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
	 * Get products.
	 */
	public function getProducts()
	{
		$queryGenerator = $this->getQueryGenerator();
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_seproductsrel', 'vtiger_seproductsrel.crmid = vtiger_products.productid AND vtiger_seproductsrel.setype=:module', [':module' => 'Products']]);
		$queryGenerator->addNativeCondition(['vtiger_seproductsrel.productid' => $this->get('parentRecord')->getId()]);
	}

	/**
	 * Get products pricebooks.
	 */
	public function getProductPricebooks()
	{
		$queryGenerator = $this->getQueryGenerator();
		$queryGenerator->setCustomColumn('vtiger_pricebookproductrel.productid as prodid');
		$queryGenerator->setCustomColumn('vtiger_pricebookproductrel.listprice');
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_pricebookproductrel', 'vtiger_pricebook.pricebookid = vtiger_pricebookproductrel.pricebookid']);
		$queryGenerator->addNativeCondition(['vtiger_pricebookproductrel.productid' => $this->get('parentRecord')->getId()]);
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

	/**
	 * Get leads.
	 */
	public function getLeads()
	{
		$queryGenerator = $this->getQueryGenerator();
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_seproductsrel', 'vtiger_seproductsrel.crmid = vtiger_leaddetails.leadid']);
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_products', 'vtiger_seproductsrel.productid = vtiger_products.productid']);
		$queryGenerator->addNativeCondition(['vtiger_products.productid' => $this->get('parentRecord')->getId()]);
	}

	/**
	 * Get accounts.
	 */
	public function getAccounts()
	{
		$queryGenerator = $this->getQueryGenerator();
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_seproductsrel', 'vtiger_seproductsrel.crmid = vtiger_account.accountid']);
		$queryGenerator->addJoin(['INNER JOIN', 'vtiger_products', 'vtiger_seproductsrel.productid = vtiger_products.productid']);
		$queryGenerator->addNativeCondition(['vtiger_products.productid' => $this->get('parentRecord')->getId()]);
	}

	/**
	 * Get many to many.
	 */
	public function getManyToMany()
	{
		if ($this->getRelationModuleName() === 'IStorages') {
			$queryGenerator = $this->getQueryGenerator();
			$queryGenerator->setCustomColumn('u_#__istorages_products.qtyinstock');
		}
		parent::getManyToMany();
	}
}
