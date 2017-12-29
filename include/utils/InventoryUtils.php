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

/** 	Function used to get all the price details for different currencies which are associated to the given product
 * 	@param int $productid - product id to which we want to get all the associated prices
 *  @param decimal $unit_price - Unit price of the product
 *  @param string $available - available or available_associated where as default is available, if available then the prices in the currencies which are available now will be returned, otherwise if the value is available_associated then prices of all the associated currencies will be retruned
 * 	@return array $price_details - price details as a array with productid, curid, curname
 */
function getPriceDetailsForProduct($productid, $unit_price, $available = 'available', $itemtype = 'Products')
{
	$adb = PearDatabase::getInstance();

	\App\Log::trace("Entering into function getPriceDetailsForProduct($productid)");
	if ($productid != '') {
		$currencyId = \App\Fields\Currency::getCurrencyByModule($productid, $itemtype);
		$product_base_conv_rate = getBaseConversionRateForProduct($productid, 'edit', $itemtype);
		// Detail View
		if ($available == 'available_associated') {
			$query = "select vtiger_currency_info.*, vtiger_productcurrencyrel.converted_price, vtiger_productcurrencyrel.actual_price
					from vtiger_currency_info
					inner join vtiger_productcurrencyrel on vtiger_currency_info.id = vtiger_productcurrencyrel.currencyid
					where vtiger_currency_info.currency_status = 'Active' and vtiger_currency_info.deleted=0
					and vtiger_productcurrencyrel.productid = ? and vtiger_currency_info.id != ?";
			$params = [$productid, $currencyId];
		} else { // Edit View
			$query = "select vtiger_currency_info.*, vtiger_productcurrencyrel.converted_price, vtiger_productcurrencyrel.actual_price
					from vtiger_currency_info
					left join vtiger_productcurrencyrel
					on vtiger_currency_info.id = vtiger_productcurrencyrel.currencyid and vtiger_productcurrencyrel.productid = ?
					where vtiger_currency_info.currency_status = 'Active' and vtiger_currency_info.deleted=0";
			$params = [$productid];
		}

		$res = $adb->pquery($query, $params);
		$rows_res = $adb->numRows($res);
		for ($i = 0; $i < $rows_res; $i++) {
			$price_details[$i]['productid'] = $productid;
			$price_details[$i]['currencylabel'] = $adb->queryResult($res, $i, 'currency_name');
			$price_details[$i]['currencycode'] = $adb->queryResult($res, $i, 'currency_code');
			$price_details[$i]['currencysymbol'] = $adb->queryResult($res, $i, 'currency_symbol');
			$currency_id = $adb->queryResult($res, $i, 'id');
			$price_details[$i]['curid'] = $currency_id;
			$price_details[$i]['curname'] = 'curname' . $adb->queryResult($res, $i, 'id');
			$cur_value = $adb->queryResult($res, $i, 'actual_price');

			// Get the conversion rate for the given currency, get the conversion rate of the product currency to base currency.
			// Both together will be the actual conversion rate for the given currency.
			$conversion_rate = $adb->queryResult($res, $i, 'conversion_rate');
			$actual_conversion_rate = $product_base_conv_rate * $conversion_rate;

			$is_basecurrency = false;
			if ($currency_id == $currencyId) {
				$is_basecurrency = true;
			}
			if ($cur_value === null || $cur_value == '') {
				$price_details[$i]['check_value'] = false;
				if ($unit_price !== null) {
					$cur_value = CurrencyField::convertFromMasterCurrency($unit_price, $actual_conversion_rate);
				} else {
					$cur_value = '0';
				}
			}
			$price_details[$i]['check_value'] = true;
			$price_details[$i]['curvalue'] = CurrencyField::convertToUserFormat($cur_value, null, true);
			$price_details[$i]['conversionrate'] = $actual_conversion_rate;
			$price_details[$i]['is_basecurrency'] = $is_basecurrency;
		}
	} else {
		if ($available === 'available') { // Create View
			$userCurrencyId = \App\User::getCurrentUserModel()->getDetail('currency_id');

			$query = "select vtiger_currency_info.* from vtiger_currency_info
					where vtiger_currency_info.currency_status = 'Active' and vtiger_currency_info.deleted=0";
			$params = [];

			$res = $adb->pquery($query, $params);
			$rows = $adb->numRows($res);
			for ($i = 0; $i < $rows; $i++) {
				$price_details[$i]['currencylabel'] = $adb->queryResult($res, $i, 'currency_name');
				$price_details[$i]['currencycode'] = $adb->queryResult($res, $i, 'currency_code');
				$price_details[$i]['currencysymbol'] = $adb->queryResult($res, $i, 'currency_symbol');
				$currency_id = $adb->queryResult($res, $i, 'id');
				$price_details[$i]['curid'] = $currency_id;
				$price_details[$i]['curname'] = 'curname' . $adb->queryResult($res, $i, 'id');

				// Get the conversion rate for the given currency, get the conversion rate of the product currency(logged in user's currency) to base currency.
				// Both together will be the actual conversion rate for the given currency.
				$conversion_rate = $adb->queryResult($res, $i, 'conversion_rate');
				$user_cursym_convrate = \vtlib\Functions::getCurrencySymbolandRate($userCurrencyId);
				$product_base_conv_rate = 1 / $user_cursym_convrate['rate'];
				$actual_conversion_rate = $product_base_conv_rate * $conversion_rate;

				$price_details[$i]['check_value'] = false;
				$price_details[$i]['curvalue'] = '0';
				$price_details[$i]['conversionrate'] = $actual_conversion_rate;

				$is_basecurrency = false;
				if ($currency_id === $userCurrencyId) {
					$is_basecurrency = true;
				}
				$price_details[$i]['is_basecurrency'] = $is_basecurrency;
			}
		} else {
			\App\Log::trace("Product id is empty. we cannot retrieve the associated prices.");
		}
	}

	\App\Log::trace("Exit from function getPriceDetailsForProduct($productid)");
	return $price_details;
}

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
