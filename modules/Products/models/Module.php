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

class Products_Module_Model extends Vtiger_Module_Model
{
	/**
	 * Function to get list view query for popup window.
	 *
	 * @param string              $sourceModule   Parent module
	 * @param string              $field          parent fieldname
	 * @param string              $record         parent id
	 * @param \App\QueryGenerator $queryGenerator
	 * @param bool                $skipSelected
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, \App\QueryGenerator $queryGenerator)
	{
		$supportedModulesList = [$this->getName(), 'Vendors', 'Leads', 'Accounts'];
		if (($sourceModule == 'PriceBooks' && $field == 'priceBookRelatedList') || in_array($sourceModule, $supportedModulesList) || Vtiger_Module_Model::getInstance($sourceModule)->isInventory()) {
			$condition = ['and', ['vtiger_products.discontinued' => 1]];
			if ($sourceModule === $this->getName()) {
				$subQuery = (new App\Db\Query())
					->select(['productid'])
					->from('vtiger_seproductsrel')
					->where(['setype' => $sourceModule]);
				$condition[] = ['not in', 'vtiger_products.productid', $subQuery];
				$subQuery = (new App\Db\Query())
					->select(['crmid'])
					->from('vtiger_seproductsrel')
					->where(['productid' => $record]);
				$condition[] = ['not in', 'vtiger_products.productid', $subQuery];
				$condition[] = ['<>', 'vtiger_products.productid', $record];
			} elseif ($sourceModule === 'PriceBooks') {
				$subQuery = (new App\Db\Query())
					->select(['productid'])
					->from('vtiger_pricebookproductrel')
					->where(['pricebookid' => $record]);
				$condition[] = ['not in', 'vtiger_products.productid', $subQuery];
			} elseif ($sourceModule === 'Vendors') {
				$condition[] = ['<>', 'vtiger_products.vendor_id', $record];
			}
			$queryGenerator->addNativeCondition($condition);
		}
	}

	/**
	 * Function to get prices for specified products with specific currency.
	 *
	 * @param <Integer> $currenctId
	 * @param <Array>   $productIdsList
	 *
	 * @return <Array>
	 */
	public function getPricesForProducts($currencyId, $productIdsList)
	{
		$priceList = [];
		$moduleName = $this->getName();
		if (count($productIdsList) > 0) {
			if ($moduleName === 'Services') {
				$dataReader = (new \App\Db\Query())->select(['vtiger_currency_info.id', 'vtiger_currency_info.conversion_rate',
					'productid' => 'vtiger_service.serviceid', 'vtiger_service.unit_price', 'vtiger_productcurrencyrel.actual_price', ])
					->from('vtiger_service')
					->leftJoin('vtiger_productcurrencyrel', 'vtiger_service.serviceid = vtiger_productcurrencyrel.productid')
					->leftJoin('vtiger_currency_info', 'vtiger_currency_info.id = vtiger_productcurrencyrel.currencyid')
					->where(['vtiger_service.serviceid' => $productIdsList, 'vtiger_currency_info.id' => $currencyId])
					->createCommand()->query();
			} else {
				$dataReader = (new \App\Db\Query())->select(['vtiger_currency_info.id', 'vtiger_currency_info.conversion_rate',
					'vtiger_products.productid', 'vtiger_products.unit_price', 'vtiger_productcurrencyrel.actual_price', ])
					->from('vtiger_products')
					->leftJoin('vtiger_productcurrencyrel', 'vtiger_products.productid = vtiger_productcurrencyrel.productid')
					->leftJoin('vtiger_currency_info', 'vtiger_currency_info.id = vtiger_productcurrencyrel.currencyid')
					->where(['vtiger_products.productid' => $productIdsList, 'vtiger_currency_info.id' => $currencyId])
					->createCommand()->query();
			}
			while ($row = $dataReader->read()) {
				$productId = $row['productid'];
				if (\App\Field::getFieldPermission($moduleName, 'unit_price')) {
					$actualPrice = (float) $row['actual_price'];
					if (empty($row['actual_price'])) {
						$actualPrice = $row['unit_price'] * $row['conversion_rate'] * Products_Record_Model::getBaseConversionRateForProduct($productId, 'edit', $moduleName);
					}
					$priceList[$productId] = $actualPrice;
				} else {
					$priceList[$productId] = 0;
				}
			}
			$dataReader->close();
		}
		return $priceList;
	}

	/**
	 * Function to check whether the module is summary view supported.
	 *
	 * @return bool - true/false
	 */
	public function isSummaryViewSupported()
	{
		return false;
	}
}
