<?php
/* * *******************************************************************************
 * * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 * ****************************************************************************** */

/** 	Function used to get the conversion rate for the product base currency with respect to the CRM base currency
 * 	@param int $productid - product id for which we want to get the conversion rate of the base currency
 *  @param string $mode - Mode in which the function is called
 *  @return number $conversion_rate - conversion rate of the base currency for the given product based on the CRM base currency
 */
function getBaseConversionRateForProduct($productid, $mode = 'edit', $module = 'Products')
{
	$nameCache = $productid . $mode . $module;
	if (\App\Cache::has('getBaseConversionRateForProduct', $nameCache)) {
		return \App\Cache::get('getBaseConversionRateForProduct', $nameCache);
	}
	$query = (new \App\Db\Query());
	if ($mode === 'edit') {
		if ($module === 'Services') {
			$convRate = $query->select(['conversion_rate'])->from('vtiger_service')
					->innerJoin('vtiger_currency_info', 'vtiger_service.currency_id = vtiger_currency_info.id')
					->where(['tiger_service.serviceid' => $productid])->scalar();
		} else {
			$convRate = $query->select(['conversion_rate'])->from('vtiger_products')
					->innerJoin('vtiger_currency_info', 'vtiger_products.productid = vtiger_currency_info.id')
					->where(['vtiger_products.productid' => $productid])->scalar();
		}
	} else {
		$convRate = $query->select(['conversion_rate'])->from('vtiger_currency_info')
				->where(['id' => App\User::getCurrentUserModel()->getDetail('currency_id')])->scalar();
	}
	if ($convRate) {
		$convRate = 1 / $convRate;
	}
	\App\Cache::save('getBaseConversionRateForProduct', $nameCache, $convRate);
	return $convRate;
}
