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

/** 	function used to get the price type for the entity (PO or Invoice)
 * 	@param string $module - module name
 * 	@param int $id - id of the PO or Invoice
 * 	@return string $pricetype - pricetype for the given entity which will be unitprice or secondprice
 */
function getInventoryCurrencyInfo($module, $id)
{
	$adb = PearDatabase::getInstance();


	\App\Log::trace("Entering into function getInventoryCurrencyInfo($module, $id).");

	$focus = new $module();

	$res = $adb->pquery("select currency_id, {$focus->table_name}.conversion_rate as conv_rate, vtiger_currency_info.* from {$focus->table_name} "
		. "inner join vtiger_currency_info on {$focus->table_name}.currency_id = vtiger_currency_info.id where {$focus->table_index}=?", array($id), true);

	$currency_info = [];
	$currency_info['currency_id'] = $adb->query_result($res, 0, 'currency_id');
	$currency_info['conversion_rate'] = $adb->query_result($res, 0, 'conv_rate');
	$currency_info['currency_name'] = $adb->query_result($res, 0, 'currency_name');
	$currency_info['currency_code'] = $adb->query_result($res, 0, 'currency_code');
	$currency_info['currency_symbol'] = $adb->query_result($res, 0, 'currency_symbol');

	\App\Log::trace("Exit from function getInventoryCurrencyInfo($module, $id).");

	return $currency_info;
}

/** 	Function used to get the list of all Currencies as a array
 *  @param string available - if 'all' returns all the currencies, default value 'available' returns only the currencies which are available for use.
 * 	return array $currency_details - return details of all the currencies as a array
 */
function getAllCurrencies($available = 'available')
{
	return vtlib\Functions::getAllCurrency($available != 'all');
}

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
		$product_currency_id = getProductBaseCurrency($productid, $itemtype);
		$product_base_conv_rate = getBaseConversionRateForProduct($productid, 'edit', $itemtype);
		// Detail View
		if ($available == 'available_associated') {
			$query = "select vtiger_currency_info.*, vtiger_productcurrencyrel.converted_price, vtiger_productcurrencyrel.actual_price
					from vtiger_currency_info
					inner join vtiger_productcurrencyrel on vtiger_currency_info.id = vtiger_productcurrencyrel.currencyid
					where vtiger_currency_info.currency_status = 'Active' and vtiger_currency_info.deleted=0
					and vtiger_productcurrencyrel.productid = ? and vtiger_currency_info.id != ?";
			$params = array($productid, $product_currency_id);
		} else { // Edit View
			$query = "select vtiger_currency_info.*, vtiger_productcurrencyrel.converted_price, vtiger_productcurrencyrel.actual_price
					from vtiger_currency_info
					left join vtiger_productcurrencyrel
					on vtiger_currency_info.id = vtiger_productcurrencyrel.currencyid and vtiger_productcurrencyrel.productid = ?
					where vtiger_currency_info.currency_status = 'Active' and vtiger_currency_info.deleted=0";
			$params = array($productid);
		}

		$res = $adb->pquery($query, $params);
		$rows_res = $adb->num_rows($res);
		for ($i = 0; $i < $rows_res; $i++) {
			$price_details[$i]['productid'] = $productid;
			$price_details[$i]['currencylabel'] = $adb->query_result($res, $i, 'currency_name');
			$price_details[$i]['currencycode'] = $adb->query_result($res, $i, 'currency_code');
			$price_details[$i]['currencysymbol'] = $adb->query_result($res, $i, 'currency_symbol');
			$currency_id = $adb->query_result($res, $i, 'id');
			$price_details[$i]['curid'] = $currency_id;
			$price_details[$i]['curname'] = 'curname' . $adb->query_result($res, $i, 'id');
			$cur_value = $adb->query_result($res, $i, 'actual_price');

			// Get the conversion rate for the given currency, get the conversion rate of the product currency to base currency.
			// Both together will be the actual conversion rate for the given currency.
			$conversion_rate = $adb->query_result($res, $i, 'conversion_rate');
			$actual_conversion_rate = $product_base_conv_rate * $conversion_rate;

			$is_basecurrency = false;
			if ($currency_id == $product_currency_id) {
				$is_basecurrency = true;
			}
			if ($cur_value === null || $cur_value == '') {
				$price_details[$i]['check_value'] = false;
				if ($unit_price != null) {
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
		if ($available == 'available') { // Create View
			$current_user = vglobal('current_user');

			$user_currency_id = \vtlib\Functions::userCurrencyId($current_user->id);

			$query = "select vtiger_currency_info.* from vtiger_currency_info
					where vtiger_currency_info.currency_status = 'Active' and vtiger_currency_info.deleted=0";
			$params = [];

			$res = $adb->pquery($query, $params);
			$rows = $adb->num_rows($res);
			for ($i = 0; $i < $rows; $i++) {
				$price_details[$i]['currencylabel'] = $adb->query_result($res, $i, 'currency_name');
				$price_details[$i]['currencycode'] = $adb->query_result($res, $i, 'currency_code');
				$price_details[$i]['currencysymbol'] = $adb->query_result($res, $i, 'currency_symbol');
				$currency_id = $adb->query_result($res, $i, 'id');
				$price_details[$i]['curid'] = $currency_id;
				$price_details[$i]['curname'] = 'curname' . $adb->query_result($res, $i, 'id');

				// Get the conversion rate for the given currency, get the conversion rate of the product currency(logged in user's currency) to base currency.
				// Both together will be the actual conversion rate for the given currency.
				$conversion_rate = $adb->query_result($res, $i, 'conversion_rate');
				$user_cursym_convrate = \vtlib\Functions::getCurrencySymbolandRate($user_currency_id);
				$product_base_conv_rate = 1 / $user_cursym_convrate['rate'];
				$actual_conversion_rate = $product_base_conv_rate * $conversion_rate;

				$price_details[$i]['check_value'] = false;
				$price_details[$i]['curvalue'] = '0';
				$price_details[$i]['conversionrate'] = $actual_conversion_rate;

				$is_basecurrency = false;
				if ($currency_id == $user_currency_id) {
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

/** 	Function used to get the base currency used for the given Product
 * 	@param int $productid - product id for which we want to get the id of the base currency
 *  @return int $currencyid - id of the base currency for the given product
 */
function getProductBaseCurrency($productid, $module = 'Products')
{
	$adb = PearDatabase::getInstance();

	if ($module == 'Services') {
		$sql = "select currency_id from vtiger_service where serviceid=?";
	} else {
		$sql = "select currency_id from vtiger_products where productid=?";
	}
	$params = array($productid);
	$res = $adb->pquery($sql, $params);
	$currencyid = $adb->query_result($res, 0, 'currency_id');
	return $currencyid;
}

/** 	Function used to get the conversion rate for the product base currency with respect to the CRM base currency
 * 	@param int $productid - product id for which we want to get the conversion rate of the base currency
 *  @param string $mode - Mode in which the function is called
 *  @return number $conversion_rate - conversion rate of the base currency for the given product based on the CRM base currency
 */
function getBaseConversionRateForProduct($productid, $mode = 'edit', $module = 'Products')
{
	$adb = PearDatabase::getInstance();
	$nameCache = $productid . $mode . $module;
	$convRate = Vtiger_Cache::get('getBaseConversionRateForProduct', $nameCache);
	if ($convRate !== false) {
		return $convRate;
	}
	$current_user = vglobal('current_user');
	if ($mode == 'edit') {
		if ($module == 'Services') {
			$sql = "select conversion_rate from vtiger_service inner join vtiger_currency_info
					on vtiger_service.currency_id = vtiger_currency_info.id where vtiger_service.serviceid=?";
		} else {
			$sql = "select conversion_rate from vtiger_products inner join vtiger_currency_info
					on vtiger_products.currency_id = vtiger_currency_info.id where vtiger_products.productid=?";
		}
		$params = array($productid);
	} else {
		$sql = "select conversion_rate from vtiger_currency_info where id=?";
		$params = array(\vtlib\Functions::userCurrencyId($current_user->id));
	}

	$result = $adb->pquery($sql, $params);
	$convRate = $adb->getSingleValue($result);
	$convRate = 1 / $convRate;
	Vtiger_Cache::set('getBaseConversionRateForProduct', $nameCache, $convRate);
	return $convRate;
}

/** 	Function used to get the prices for the given list of products based in the specified currency
 * 	@param int $currencyid - currency id based on which the prices have to be provided
 * 	@param array $productIds - List of product id's for which we want to get the price based on given currency
 *  @return array $prices_list - List of prices for the given list of products based on the given currency in the form of 'product id' mapped to 'price value'
 */
function getPricesForProducts($currencyid, $productIds, $module = 'Products')
{
	$priceList = [];
	if (count($productIds) > 0) {
		if ($module == 'Services') {
			$dataReader = (new \App\Db\Query())->select(['vtiger_currency_info.id', 'vtiger_currency_info.conversion_rate',
						'productid' => 'vtiger_service.serviceid', 'vtiger_service.unit_price', 'vtiger_productcurrencyrel.actual_price'])
					->from('vtiger_service')
					->leftJoin('vtiger_productcurrencyrel', 'vtiger_service.serviceid = vtiger_productcurrencyrel.productid')
					->leftJoin('vtiger_currency_info', 'vtiger_currency_info.id = vtiger_productcurrencyrel.currencyid')
					->where(['vtiger_service.serviceid' => $productIds, 'vtiger_currency_info.id' => $currencyid])
					->createCommand()->query();
		} else {
			$dataReader = (new \App\Db\Query())->select(['vtiger_currency_info.id', 'vtiger_currency_info.conversion_rate',
						'vtiger_products.productid', 'vtiger_products.unit_price', 'vtiger_productcurrencyrel.actual_price'])
					->from('vtiger_products')
					->leftJoin('vtiger_productcurrencyrel', 'vtiger_products.productid = vtiger_productcurrencyrel.productid')
					->leftJoin('vtiger_currency_info', 'vtiger_currency_info.id = vtiger_productcurrencyrel.currencyid')
					->where(['vtiger_products.productid' => $productIds, 'vtiger_currency_info.id' => $currencyid])
					->createCommand()->query();
		}

		while ($row = $dataReader->read()) {
			$productId = $row['productid'];
			if (\App\Field::getFieldPermission($module, 'unit_price')) {
				$actualPrice = (float) $row['actual_price'];
				if ($actualPrice === null || $actualPrice == '') {
					$actualPrice = $row['unit_price'] * $row['conversion_rate'] * getBaseConversionRateForProduct($productId, 'edit', $module);
				}
				$priceList[$productId] = $actualPrice;
			} else {
				$priceList[$productId] = '';
			}
		}
	}
	return $priceList;
}
