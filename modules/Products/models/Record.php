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

class Products_Record_Model extends Vtiger_Record_Model
{
	/**
	 * Function to get Taxes Url.
	 *
	 * @return string Url
	 */
	public function getTaxesURL()
	{
		return 'index.php?module=Inventory&action=GetTaxes&record=' . $this->getId();
	}

	/**
	 * Function to get subproducts for this record.
	 *
	 * @return array of subproducts
	 */
	public function getSubProducts()
	{
		$subProducts = (new \App\Db\Query())->select(['vtiger_products.productid'])->from(['vtiger_products'])
			->innerJoin('vtiger_crmentity', 'vtiger_crmentity.crmid = vtiger_products.productid')
			->leftJoin('vtiger_seproductsrel', 'vtiger_seproductsrel.crmid = vtiger_products.productid AND vtiger_products.discontinued = :p1 AND vtiger_seproductsrel.setype= :p2', [':p1' => 1, ':p2' => 'Products'])
			->where(['vtiger_crmentity.deleted' => 0, 'vtiger_seproductsrel.productid' => $this->getId()])
			->column();
		$subProductList = [];
		foreach ($subProducts as $productId) {
			$subProductList[] = Vtiger_Record_Model::getInstanceById($productId, 'Products');
		}
		return $subProductList;
	}

	/**
	 * Function updates ListPrice for Product/Service-PriceBook relation.
	 *
	 * @param <Integer> $relatedRecordId - PriceBook Id
	 * @param <Integer> $price           - listprice
	 * @param <Integer> $currencyId      - currencyId
	 */
	public function updateListPrice($relatedRecordId, $price, $currencyId)
	{
		$isExists = (new \App\Db\Query())->from('vtiger_pricebookproductrel')->where(['pricebookid' => $relatedRecordId, 'productid' => $this->getId()])->exists();
		if ($isExists) {
			$status = App\Db::getInstance()->createCommand()
				->update('vtiger_pricebookproductrel', ['listprice' => $price], ['pricebookid' => $relatedRecordId, 'productid' => $this->getId()])
				->execute();
		} else {
			$status = App\Db::getInstance()->createCommand()
				->insert('vtiger_pricebookproductrel', [
					'pricebookid' => $relatedRecordId,
					'productid' => $this->getId(),
					'listprice' => $price,
					'usedcurrency' => $currencyId,
				])->execute();
		}
		return $status;
	}

	/**
	 * The function decide about mandatory save record.
	 *
	 * @return type
	 */
	public function isMandatorySave()
	{
		return true;
	}

	/** {@inheritdoc} */
	public function delete()
	{
		parent::delete();
		\App\Db::getInstance()->createCommand()->delete('vtiger_seproductsrel', ['or', ['productid' => $this->getId()], ['crmid' => $this->getId()]])->execute();
	}
}
