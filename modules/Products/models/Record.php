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
	 * Function to get values of more currencies listprice.
	 *
	 * @param int $id
	 *
	 * @return array of listprice values
	 */
	public function getListPriceValues($id)
	{
		$dataReader = (new App\Db\Query())->from('vtiger_productcurrencyrel')->where(['productid' => $id])->createCommand()->query();
		$listpriceValues = [];
		while ($row = $dataReader->read()) {
			$listpriceValues[$row['currencyid']] = CurrencyField::convertToUserFormat($row['actual_price'], null, true);
		}
		$dataReader->close();

		return $listpriceValues;
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
	 * Get price details.
	 *
	 * @return array
	 */
	public function getPriceDetails()
	{
		if (empty($this->ext['priceDetails'])) {
			$baseCurrencyId = $userCurrencyId = (int) \App\User::getCurrentUserModel()->getDetail('currency_id');
			$fieldInfo = $this->getField('unit_price')->getFieldInfo();
			$data = $priceDetails = [];
			$new = $this->isNew();
			$unitPrice = $this->get('unit_price');
			$baseRate = 1 / \App\Fields\Currency::getById($userCurrencyId)['conversion_rate'];
			if (!$new) {
				$data = (new App\Db\Query())->select(['currencyid', 'actual_price'])->from('vtiger_productcurrencyrel')->where(['productid' => $this->getId()])->createCommand()->queryAllByGroup();
				$baseRate = self::getBaseConversionRateForProduct($this->getId(), 'edit', $this->getModuleName());
				$baseCurrencyId = $this->get('baseCurrencyDetails')['currencyid'] ?? \App\Fields\Currency::getCurrencyByModule($this->getId(), $this->getModuleName());
			}
			foreach (\App\Fields\Currency::getAll(true) as $id => $currency) {
				$currency['productid'] = $this->getId();
				$check = false;
				$value = 0;
				$conversionrRate = $baseRate * $currency['conversion_rate'];
				if (!$new) {
					if (isset($data[$currency['id']])) {
						$value = $data[$currency['id']];
						$check = true;
					} elseif ($unitPrice) {
						$value = $unitPrice * $conversionrRate;
					}
				}
				$priceDetails[] = array_merge(
					$currency,
					[
						'curname' => 'curname' . $currency['id'],
						'conversionrate' => $conversionrRate,
						'check_value' => $check,
						'curvalue' => \App\Fields\Currency::formatToDisplay($value),
						'is_basecurrency' => $baseCurrencyId === (int) $id,
						'fieldInfo' => array_merge($fieldInfo, [
							'name' => 'curname' . $currency['id'],
							'currency_symbol' => $currency['currency_symbol'],
						]),
					]
				);
			}
			$this->ext['priceDetails'] = $priceDetails;
		}
		return $this->ext['priceDetails'];
	}

	/**
	 * Function to get base currency details.
	 *
	 * @return array
	 */
	public function getBaseCurrencyDetails()
	{
		$baseCurrencyDetails = $this->get('baseCurrencyDetails');
		if (!empty($baseCurrencyDetails)) {
			return $baseCurrencyDetails;
		}

		$recordId = $this->getId();
		if (!empty($recordId)) {
			$baseCurrency = \App\Fields\Currency::getCurrencyByModule($recordId, $this->getModuleName());
		} else {
			$baseCurrency = \App\User::getCurrentUserModel()->getDetail('currency_id');
		}
		$baseCurrencyDetails = ['currencyid' => $baseCurrency];

		$baseCurrencySymbolDetails = \App\Fields\Currency::getById($baseCurrency);
		$baseCurrencyDetails = array_merge($baseCurrencyDetails, $baseCurrencySymbolDetails);
		$this->set('baseCurrencyDetails', $baseCurrencyDetails);

		return $baseCurrencyDetails;
	}

	/**
	 * Static Function to get the list of records matching the search key.
	 *
	 * @param string $searchKey
	 * @param mixed  $moduleName
	 * @param mixed  $limit
	 * @param mixed  $operator
	 *
	 * @return <Array> - List of Vtiger_Record_Model or Module Specific Record Model instances
	 */
	public static function getSearchResult($searchKey, $moduleName = false, $limit = false, $operator = false)
	{
		$query = false;
		if (false !== $moduleName && ('Products' === $moduleName || 'Services' === $moduleName)) {
			$currentUserId = \Users_Record_Model::getCurrentUserModel()->getId();
			$query = (new App\Db\Query())->select(['u_#__crmentity_search_label.crmid', 'u_#__crmentity_search_label.setype', 'u_#__crmentity_search_label.searchlabel'])
				->from('u_#__crmentity_search_label')
				->where(['and', ['like', 'u_#__crmentity_search_label.userid', ",{$currentUserId},"], ['like', 'u_#__crmentity_search_label.searchlabel', $searchKey]]);
			if (false !== $moduleName) {
				$query->andWhere(['u_#__crmentity_search_label.setype' => $moduleName]);
			} elseif (2 === \AppConfig::search('GLOBAL_SEARCH_SORTING_RESULTS')) {
				$query->leftJoin('vtiger_entityname', 'vtiger_entityname.modulename = u_#__crmentity_search_label.setype')
					->andWhere(['vtiger_entityname.turn_off' => 1])
					->orderBy('vtiger_entityname.sequence');
			}
			if ('Products' === $moduleName) {
				$query->innerJoin('vtiger_products', 'vtiger_products.productid = u_#__crmentity_search_label.crmid')
					->andWhere(['vtiger_products.discontinued' => 1]);
			} elseif ('Services' === $moduleName) {
				$query->innerJoin('vtiger_service', 'vtiger_service.serviceid = u_#__crmentity_search_label.crmid')
					->andWhere(['vtiger_service.discontinued' => 1]);
			}
			if (!$limit) {
				$limit = AppConfig::search('GLOBAL_SEARCH_MODAL_MAX_NUMBER_RESULT');
			}
			if ($limit) {
				$query->limit($limit);
			}
		}

		$rows = [];
		if (!$query) {
			$recordSearch = new \App\RecordSearch($searchKey, $moduleName, $limit);
			if ($operator) {
				$recordSearch->operator = $operator;
			}
			$rows = $recordSearch->search();
		} else {
			while ($row = $query->createCommand()->read()) {
				$rows[] = $row;
			}
		}
		$ids = $matchingRecords = $leadIdsList = [];
		foreach ($rows as &$row) {
			$ids[] = $row['crmid'];
			if ('Leads' === $row['setype']) {
				$leadIdsList[] = $row['crmid'];
			}
		}
		$convertedInfo = Leads_Module_Model::getConvertedInfo($leadIdsList);
		$labels = \App\Record::getLabel($ids);

		foreach ($rows as &$row) {
			if ('Leads' === $row['setype'] && $convertedInfo[$row['crmid']]) {
				continue;
			}
			$recordMeta = \vtlib\Functions::getCRMRecordMetadata($row['crmid']);
			$row['id'] = $row['crmid'];
			$row['label'] = $labels[$row['crmid']];
			$row['smownerid'] = $recordMeta['smownerid'];
			$row['createdtime'] = $recordMeta['createdtime'];
			$row['permitted'] = \App\Privilege::isPermitted($row['setype'], 'DetailView', $row['crmid']);
			$moduleName = $row['setype'];
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $moduleName);
			$recordInstance = new $modelClassName();
			$matchingRecords[$moduleName][$row['id']] = $recordInstance->setData($row)->setModuleFromInstance($moduleModel);
		}
		return $matchingRecords;
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
	 * nction used to get the conversion rate for the product base currency with respect to the CRM base currency.
	 *
	 * @param int    $productId product id for which we want to get the conversion rate of the base currency
	 * @param string $mode      Mode in which the function is called
	 * @param string $module
	 *
	 * @return int conversion rate of the base currency for the given product based on the CRM base currency
	 */
	public static function getBaseConversionRateForProduct($productId, $mode = 'edit', $module = 'Products')
	{
		$nameCache = $productId . $mode . $module;
		if (\App\Cache::has('getBaseConversionRateForProduct', $nameCache)) {
			return \App\Cache::get('getBaseConversionRateForProduct', $nameCache);
		}
		$query = (new \App\Db\Query());
		if ('edit' === $mode) {
			if ('Services' === $module) {
				$convRate = $query->select(['conversion_rate'])->from('vtiger_service')->innerJoin('vtiger_currency_info', 'vtiger_service.currency_id = vtiger_currency_info.id')->where(['vtiger_service.serviceid' => $productId])->scalar();
			} else {
				$convRate = $query->select(['conversion_rate'])->from('vtiger_products')->innerJoin('vtiger_currency_info', 'vtiger_products.currency_id = vtiger_currency_info.id')->where(['vtiger_products.productid' => $productId])->scalar();
			}
		} else {
			$convRate = $query->select(['conversion_rate'])->from('vtiger_currency_info')->where(['id' => \App\User::getCurrentUserModel()->getDetail('currency_id')])->scalar();
		}
		if ($convRate) {
			$convRate = 1 / $convRate;
		}
		\App\Cache::save('getBaseConversionRateForProduct', $nameCache, $convRate);

		return $convRate;
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

	/**
	 * Custom Save for Module.
	 */
	public function saveToDb()
	{
		parent::saveToDb();
		//Inserting into product_taxrel table
		if ('DETAIL_VIEW_BASIC' !== \App\Request::_get('ajxaction') && 'MassSave' !== \App\Request::_get('action')) {
			$this->insertPriceInformation();
		}
		// Update unit price value in vtiger_productcurrencyrel
		$this->updateUnitPrice();
	}

	/**
	 * Update unit price.
	 */
	public function updateUnitPrice()
	{
		$productInfo = (new App\Db\Query())->select(['unit_price', 'currency_id'])
			->from($this->getEntity()->table_name)
			->where([$this->getEntity()->table_index => $this->getId()])
			->one();
		App\Db::getInstance()->createCommand()->update('vtiger_productcurrencyrel', ['actual_price' => $productInfo['unit_price']], ['productid' => $this->getId(), 'currencyid' => $productInfo['currency_id']])->execute();
	}

	/**
	 * Function to save the product price information in vtiger_productcurrencyrel table.
	 */
	public function insertPriceInformation()
	{
		$db = \App\Db::getInstance()->createCommand();
		$request = App\Request::init();
		$productBaseConvRate = self::getBaseConversionRateForProduct($this->getId(), $this->isNew() ? 'new' : 'edit');
		$currencySet = false;
		$currencyDetails = \App\Fields\Currency::getAll(true);
		if (!$this->isNew()) {
			$db->delete('vtiger_productcurrencyrel', ['productid' => $this->getId()])->execute();
		}
		foreach ($currencyDetails as $curid => $currency) {
			$curName = $currency['currency_name'];
			$curCheckName = 'cur_' . $curid . '_check';
			$curValue = 'curname' . $curid;
			if ($request->getBoolean($curCheckName)) {
				$actualPrice = $request->getByType($curValue, 'NumberInUserFormat');
				$actualConversionRate = $productBaseConvRate * $currency['conversion_rate'];
				$convertedPrice = $actualConversionRate * ($request->isEmpty('unit_price') ? 0 : $request->getByType('unit_price', 'NumberInUserFormat'));
				\App\Log::trace("Going to save the Product - $curName currency relationship");
				$db->insert('vtiger_productcurrencyrel', [
					'productid' => $this->getId(),
					'currencyid' => $curid,
					'converted_price' => $convertedPrice,
					'actual_price' => $actualPrice,
				])->execute();
				if ($request->getByType('base_currency', 2) === $curValue) {
					$currencySet = true;
					$db->update($this->getEntity()->table_name, ['currency_id' => $curid, 'unit_price' => $actualPrice], [$this->getEntity()->table_index => $this->getId()])
						->execute();
				}
			}
		}
		if (!$currencySet) {
			reset($currencyDetails);
			$curid = key($currencyDetails);
			$db->update($this->getEntity()->table_name, ['currency_id' => $curid], [$this->getEntity()->table_index => $this->getId()])
				->execute();
		}
		\App\Log::trace('Exiting ' . __METHOD__);
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete()
	{
		parent::delete();
		\App\Db::getInstance()->createCommand()->delete('vtiger_seproductsrel', ['or', ['productid' => $this->getId()], ['crmid' => $this->getId()]])->execute();
	}
}
