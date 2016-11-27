<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/**
 * PriceBooks Record Model Class
 */
class PriceBooks_Record_Model extends Vtiger_Record_Model
{

	/**
	 * Function return the url to fetch List Price of the Product for the current PriceBook
	 * @return string
	 */
	public function getProductUnitPriceURL()
	{
		$url = 'module=PriceBooks&action=ProductListPrice&record=' . $this->getId();
		$rawData = $this->getRawData();
		$src_record = $rawData['src_record'];
		if (!empty($src_record)) {
			$url .= '&itemId=' . $src_record;
		}
		return $url;
	}

	/**
	 * Function returns the List Price for PriceBook-Product/Service relation
	 * @param <Integer> $relatedRecordId - Product/Service Id
	 * @return <Integer>
	 */
	public function getProductsListPrice($relatedRecordId)
	{
		
		return (new \App\Db\Query())->select(['listprice'])
				->from('vtiger_pricebookproductrel')
				->where(['pricebookid' => $this->getId(), 'productid' => $relatedRecordId])
				->scalar();
	}

	/**
	 * Function updates ListPrice for PriceBook-Product/Service relation
	 * @param <Integer> $relatedRecordId - Product/Service Id
	 * @param <Integer> $price - listprice
	 */
	public function updateListPrice($relatedRecordId, $price)
	{
		$isExists = (new \App\Db\Query())->from('vtiger_pricebookproductrel')->where(['pricebookid' => $this->getId(), 'productid' => $relatedRecordId])->exists();
		if ($isExists) {
			App\Db::getInstance()->createCommand()
					->update('vtiger_pricebookproductrel', ['listprice' => $price], ['pricebookid' => $this->getId(), 'productid' => $relatedRecordId])
					->execute();
		} else {
			App\Db::getInstance()->createCommand()
					->insert('vtiger_pricebookproductrel', [
						'pricebookid' => $this->getId(),
						'productid' => $relatedRecordId,
						'listprice' => $price,
						'usedcurrency' => $this->get('currency_id')
					])->execute();
		}
	}

	/**
	 * Function deletes the List Price for PriceBooks-Product/Services relationship
	 * @param <Integer> $relatedRecordId - Product/Service Id
	 */
	public function deleteListPrice($relatedRecordId)
	{
		App\Db::getInstance()->createCommand()
				->delete('vtiger_pricebookproductrel', ['pricebookid' => $this->getId(), 'productid' => $relatedRecordId])
				->execute();
	}
}
