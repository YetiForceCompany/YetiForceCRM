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
 * PriceBooks Record Model Class.
 */
class PriceBooks_Record_Model extends Vtiger_Record_Model
{
	/**
	 * Function returns the List Price for PriceBook-Product/Service relation.
	 *
	 * @param <Integer> $relatedRecordId - Product/Service Id
	 *
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
	 * Function updates ListPrice for PriceBook-Product/Service relation.
	 *
	 * @param <Integer> $relatedRecordId - Product/Service Id
	 * @param <Integer> $price           - listprice
	 */
	public function updateListPrice($relatedRecordId, $price)
	{
		$isExists = (new \App\Db\Query())->from('vtiger_pricebookproductrel')->where(['pricebookid' => $this->getId(), 'productid' => $relatedRecordId])->exists();
		if ($isExists) {
			$status = App\Db::getInstance()->createCommand()
				->update('vtiger_pricebookproductrel', ['listprice' => $price], ['pricebookid' => $this->getId(), 'productid' => $relatedRecordId])
				->execute();
		} else {
			$status = App\Db::getInstance()->createCommand()
				->insert('vtiger_pricebookproductrel', [
					'pricebookid' => $this->getId(),
					'productid' => $relatedRecordId,
					'listprice' => $price,
					'usedcurrency' => $this->get('currency_id'),
				])->execute();
		}
		return $status;
	}

	/**
	 * Function deletes the List Price for PriceBooks-Product/Services relationship.
	 *
	 * @param <Integer> $relatedRecordId - Product/Service Id
	 */
	public function deleteListPrice($relatedRecordId)
	{
		return App\Db::getInstance()->createCommand()
			->delete('vtiger_pricebookproductrel', ['pricebookid' => $this->getId(), 'productid' => $relatedRecordId])
			->execute();
	}

	public function saveToDb()
	{
		parent::saveToDb();
		$this->updateListPrices();
	}

	public function updateListPrices()
	{
		\App\Log::trace('Entering function updateListPrices...');
		$pricebookCurrency = $this->get('currency_id');
		$dataReader = (new App\Db\Query())->from('vtiger_pricebookproductrel')
			->where(['and', ['pricebookid' => $this->getId()], ['<>', 'usedcurrency', $pricebookCurrency]])
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$productCurrencyInfo = \App\Fields\Currency::getById($row['usedcurrency']);
			$pricebookCurrencyInfo = \App\Fields\Currency::getById($pricebookCurrency);
			$computedListPrice = $row['listprice'] * $pricebookCurrencyInfo['conversion_rate'] / $productCurrencyInfo['conversion_rate'];
			App\Db::getInstance()->createCommand()
				->update('vtiger_pricebookproductrel', ['listprice' => $computedListPrice, 'usedcurrency' => $pricebookCurrency], ['pricebookid' => $this->getId(), 'productid' => $row['productid']])
				->execute();
		}
		$dataReader->close();
		\App\Log::trace('Exiting function updateListPrices...');
	}
}
