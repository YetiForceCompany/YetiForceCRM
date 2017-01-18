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
	 * Function to get Taxes Url
	 * @return string Url
	 */
	public function getTaxesURL()
	{
		return 'index.php?module=Inventory&action=GetTaxes&record=' . $this->getId();
	}

	/**
	 * Function to get values of more currencies listprice
	 * @return <Array> of listprice values
	 */
	static function getListPriceValues($id)
	{
		$db = PearDatabase::getInstance();
		$listPrice = $db->pquery('SELECT * FROM vtiger_productcurrencyrel WHERE productid = ?', [$id]);
		$listpriceValues = [];
		while ($row = $db->fetch_array($listPrice)) {
			$listpriceValues[$row['currencyid']] = CurrencyField::convertToUserFormat($row['actual_price'], null, true);
		}
		return $listpriceValues;
	}

	/**
	 * Function to get subproducts for this record
	 * @return <Array> of subproducts
	 */
	public function getSubProducts()
	{
		$db = PearDatabase::getInstance();

		$result = $db->pquery("SELECT vtiger_products.productid FROM vtiger_products
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_products.productid
			LEFT JOIN vtiger_seproductsrel ON vtiger_seproductsrel.crmid = vtiger_products.productid AND vtiger_products.discontinued = 1 AND vtiger_seproductsrel.setype='Products'
			LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid
			LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			WHERE vtiger_crmentity.deleted = 0 AND vtiger_seproductsrel.productid = ? ", array($this->getId()));

		$subProductList = array();

		$numRowsCount = $db->num_rows($result);
		for ($i = 0; $i < $numRowsCount; $i++) {
			$subProductId = $db->query_result($result, $i, 'productid');
			$subProductList[] = Vtiger_Record_Model::getInstanceById($subProductId, 'Products');
		}

		return $subProductList;
	}

	/**
	 * Function to get price details
	 * @return <Array> List of prices
	 */
	public function getPriceDetails()
	{
		$priceDetails = $this->get('priceDetails');
		if (!empty($priceDetails)) {
			return $priceDetails;
		}
		$priceDetails = $this->getPriceDetailsForProduct($this->getId(), $this->get('unit_price'), 'available', $this->getModuleName());
		$this->set('priceDetails', $priceDetails);
		return $priceDetails;
	}

	/**
	 * Function to get base currency details
	 * @return <Array>
	 */
	public function getBaseCurrencyDetails()
	{
		$baseCurrencyDetails = $this->get('baseCurrencyDetails');
		if (!empty($baseCurrencyDetails)) {
			return $baseCurrencyDetails;
		}

		$recordId = $this->getId();
		if (!empty($recordId)) {
			$baseCurrency = $this->getProductBaseCurrency($recordId, $this->getModuleName());
		} else {
			$currentUserModel = Users_Record_Model::getCurrentUserModel();
			$baseCurrency = \vtlib\Functions::userCurrencyId($currentUserModel->getId());
		}
		$baseCurrencyDetails = array('currencyid' => $baseCurrency);

		$baseCurrencySymbolDetails = \vtlib\Functions::getCurrencySymbolandRate($baseCurrency);
		$baseCurrencyDetails = array_merge($baseCurrencyDetails, $baseCurrencySymbolDetails);
		$this->set('baseCurrencyDetails', $baseCurrencyDetails);

		return $baseCurrencyDetails;
	}

	/**
	 * Function to get Image Details
	 * @return <array> Image Details List
	 */
	public function getImageDetails()
	{
		$db = PearDatabase::getInstance();
		$imageDetails = array();
		$recordId = $this->getId();

		if ($recordId) {
			$sql = "SELECT vtiger_attachments.*, vtiger_crmentity.setype FROM vtiger_attachments
						INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_attachments.attachmentsid
						WHERE vtiger_crmentity.setype = 'Products Image' AND vtiger_seattachmentsrel.crmid = ?";

			$result = $db->pquery($sql, array($recordId));
			$count = $db->num_rows($result);

			for ($i = 0; $i < $count; $i++) {
				$imageIdsList[] = $db->query_result($result, $i, 'attachmentsid');
				$imagePathList[] = $db->query_result($result, $i, 'path');
				$imageName = $db->query_result($result, $i, 'name');

				//decode_html - added to handle UTF-8 characters in file names
				$imageOriginalNamesList[] = decode_html($imageName);

				//urlencode - added to handle special characters like #, %, etc.,
				$imageNamesList[] = $imageName;
			}

			if (is_array($imageOriginalNamesList)) {
				$countOfImages = count($imageOriginalNamesList);
				for ($j = 0; $j < $countOfImages; $j++) {
					$imageDetails[] = array(
						'id' => $imageIdsList[$j],
						'orgname' => $imageOriginalNamesList[$j],
						'path' => $imagePathList[$j] . $imageIdsList[$j],
						'name' => $imageNamesList[$j]
					);
				}
			}
		}
		return $imageDetails;
	}

	/**
	 * Static Function to get the list of records matching the search key
	 * @param string $searchKey
	 * @return <Array> - List of Vtiger_Record_Model or Module Specific Record Model instances
	 */
	public static function getSearchResult($searchKey, $moduleName = false, $limit = false, $operator = false)
	{
		$query = false;
		if ($moduleName !== false && ($moduleName == 'Products' || $moduleName == 'Services' )) {
			$currentUser = \Users_Record_Model::getCurrentUserModel();
			$adb = \PearDatabase::getInstance();
			$params = ['%' . $currentUser->getId() . '%', "%$searchKey%"];
			$queryFrom = 'SELECT u_yf_crmentity_search_label.`crmid`,u_yf_crmentity_search_label.`setype`,u_yf_crmentity_search_label.`searchlabel` FROM `u_yf_crmentity_search_label`';
			$queryWhere = ' WHERE u_yf_crmentity_search_label.`userid` LIKE ? && u_yf_crmentity_search_label.`searchlabel` LIKE ?';
			$orderWhere = '';
			if ($moduleName !== false) {
				$multiMode = is_array($moduleName);
				if ($multiMode) {
					$queryWhere .= sprintf(' AND u_yf_crmentity_search_label.`setype` IN (%s)', $adb->generateQuestionMarks($moduleName));
					$params = array_merge($params, $moduleName);
				} else {
					$queryWhere .= ' && `setype` = ?';
					$params[] = $moduleName;
				}
			} elseif (\AppConfig::search('GLOBAL_SEARCH_SORTING_RESULTS') == 2) {
				$queryFrom .= ' LEFT JOIN vtiger_entityname ON vtiger_entityname.modulename = u_yf_crmentity_search_label.setype';
				$queryWhere .= ' && vtiger_entityname.`turn_off` = 1 ';
				$orderWhere = ' vtiger_entityname.sequence';
			}
			if ($moduleName == 'Products') {
				$queryFrom .= ' INNER JOIN vtiger_products ON vtiger_products.productid = u_yf_crmentity_search_label.crmid';
				$queryWhere .= ' && vtiger_products.discontinued = 1';
			} else if ($moduleName == 'Services') {
				$queryFrom .= ' INNER JOIN vtiger_service ON vtiger_service.serviceid = u_yf_crmentity_search_label.crmid';
				$queryWhere .= ' && vtiger_service.discontinued = 1';
			}
			$query = $queryFrom . $queryWhere;
			if (!empty($orderWhere)) {
				$query .= sprintf(' ORDER BY %s', $orderWhere);
			}
			if (!$limit) {
				$limit = AppConfig::search('GLOBAL_SEARCH_MODAL_MAX_NUMBER_RESULT');
			}
			if ($limit) {
				$query .= ' LIMIT ';
				$query .= $limit;
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
			$result = $adb->pquery($query, $params);
			while ($row = $adb->getRow($result)) {
				$rows[] = $row;
			}
		}
		$ids = $matchingRecords = $leadIdsList = [];
		foreach ($rows as &$row) {
			$ids[] = $row['crmid'];
			if ($row['setype'] === 'Leads') {
				$leadIdsList[] = $row['crmid'];
			}
		}
		$convertedInfo = Leads_Module_Model::getConvertedInfo($leadIdsList);
		$labels = \App\Record::getLabel($ids);

		foreach ($rows as &$row) {
			if ($row['setype'] === 'Leads' && $convertedInfo[$row['crmid']]) {
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
	 * Function to get acive status of record
	 */
	public function getActiveStatusOfRecord()
	{
		$activeStatus = $this->get('discontinued');
		if ($activeStatus) {
			return $activeStatus;
		}
		$recordId = $this->getId();
		$db = PearDatabase::getInstance();
		$result = $db->pquery('SELECT discontinued FROM vtiger_products WHERE productid = ?', array($recordId));
		$activeStatus = $db->query_result($result, 'discontinued');
		return $activeStatus;
	}

	/**
	 * Function updates ListPrice for Product/Service-PriceBook relation
	 * @param <Integer> $relatedRecordId - PriceBook Id
	 * @param <Integer> $price - listprice
	 * @param <Integer> $currencyId - currencyId
	 */
	public function updateListPrice($relatedRecordId, $price, $currencyId)
	{
		$isExists = (new \App\Db\Query())->from('vtiger_pricebookproductrel')->where(['pricebookid' => $relatedRecordId, 'productid' => $this->getId()])->exists();
		if ($isExists) {
			App\Db::getInstance()->createCommand()
				->update('vtiger_pricebookproductrel', ['listprice' => $price], ['pricebookid' => $relatedRecordId, 'productid' => $this->getId()])
				->execute();
		} else {
			App\Db::getInstance()->createCommand()
				->insert('vtiger_pricebookproductrel', [
					'pricebookid' => $relatedRecordId,
					'productid' => $this->getId(),
					'listprice' => $price,
					'usedcurrency' => $currencyId
				])->execute();
		}
	}

	public function getPriceDetailsForProduct($productid, $unit_price, $available = 'available', $itemtype = 'Products')
	{
		$adb = PearDatabase::getInstance();

		\App\Log::trace("Entering into function getPriceDetailsForProduct($productid)");
		if ($productid != '') {
			$product_currency_id = $this->getProductBaseCurrency($productid, $itemtype);
			$product_base_conv_rate = $this->getBaseConversionRateForProduct($productid, 'edit', $itemtype);
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
			$rows_rew = $adb->num_rows($res);
			for ($i = 0; $i < $rows_rew; $i++) {
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
				} else {
					$price_details[$i]['check_value'] = true;
				}
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
				$params = array();

				$res = $adb->pquery($query, $params);
				$rows_res = $adb->num_rows($res);
				for ($i = 0; $i < $rows_res; $i++) {
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

	public function getProductBaseCurrency($productid, $module = 'Products')
	{
		$adb = PearDatabase::getInstance();
		if ($module == 'Services') {
			$sql = 'select currency_id from vtiger_service where serviceid=?';
		} else {
			$sql = 'select currency_id from vtiger_products where productid=?';
		}
		$res = $adb->pquery($sql, [$productid]);
		$currencyid = $adb->query_result($res, 0, 'currency_id');
		return $currencyid;
	}

	public function getBaseConversionRateForProduct($productid, $mode = 'edit', $module = 'Products')
	{
		$adb = PearDatabase::getInstance();
		$current_user = vglobal('current_user');
		if ($mode == 'edit') {
			if ($module == 'Services') {
				$sql = 'select conversion_rate from vtiger_service inner join vtiger_currency_info
					on vtiger_service.currency_id = vtiger_currency_info.id where vtiger_service.serviceid=?';
			} else {
				$sql = 'select conversion_rate from vtiger_products inner join vtiger_currency_info
					on vtiger_products.currency_id = vtiger_currency_info.id where vtiger_products.productid=?';
			}
			$params = array($productid);
		} else {
			$sql = 'select conversion_rate from vtiger_currency_info where id=?';
			$params = array(\vtlib\Functions::userCurrencyId($current_user->id));
		}

		$res = $adb->pquery($sql, $params);
		$conv_rate = $adb->query_result($res, 0, 'conversion_rate');

		return 1 / $conv_rate;
	}

	/**
	 * The function decide about mandatory save record
	 * @return type
	 */
	public function isMandatorySave()
	{
		return $_FILES ? true : false;
	}

	/**
	 * Custom Save for Module
	 */
	public function saveToDb()
	{
		parent::saveToDb();
		//Inserting into product_taxrel table
		if (AppRequest::get('ajxaction') != 'DETAILVIEW' && AppRequest::get('action') != 'MassSave' && AppRequest::get('action') != 'ProcessDuplicates') {
			$this->insertPriceInformation();
		}
		// Update unit price value in vtiger_productcurrencyrel
		$this->updateUnitPrice();
		//Inserting into attachments
		if (AppRequest::get('module') === 'Products') {
			$this->insertAttachment();
		}
	}

	/**
	 * Update unit price 
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
	 * Function to save the product price information in vtiger_productcurrencyrel table
	 */
	public function insertPriceInformation()
	{
		\App\Log::trace('Entering ' . __METHOD__);
		$db = \App\Db::getInstance();
		$productBaseConvRate = getBaseConversionRateForProduct($this->getId(), $this->mode);
		$currencySet = false;
		$currencyDetails = vtlib\Functions::getAllCurrency(true);
		if (!$this->isNew()) {
			$db->createCommand()->delete('vtiger_productcurrencyrel', ['productid' => $this->getId()])->execute();
		}
		foreach ($currencyDetails as $curid => $currency) {
			$curName = $currency['currency_name'];
			$curCheckName = 'cur_' . $curid . '_check';
			$curValue = 'curname' . $curid;
			if (AppRequest::get($curCheckName) === 'on' || AppRequest::get($curCheckName) === 1) {
				$requestPrice = CurrencyField::convertToDBFormat(AppRequest::get('unit_price'), null, true);
				$actualPrice = CurrencyField::convertToDBFormat(AppRequest::get($curValue), null, true);
				$actualConversionRate = $productBaseConvRate * $currency['conversion_rate'];
				$convertedPrice = $actualConversionRate * $requestPrice;
				\App\Log::trace("Going to save the Product - $curName currency relationship");
				\App\Db::getInstance()->createCommand()->insert('vtiger_productcurrencyrel', [
					'productid' => $this->getId(),
					'currencyid' => $curid,
					'converted_price' => $convertedPrice,
					'actual_price' => $actualPrice
				])->execute();
				if (AppRequest::get('base_currency') === $curValue) {
					$currencySet = true;
					$db->createCommand()
						->update($this->getEntity()->table_name, ['currency_id' => $curid, 'unit_price' => $actualPrice], [$this->getEntity()->table_index => $this->getId()])
						->execute();
				}
			}
		}
		if (!$currencySet) {
			reset($currencyDetails);
			$curid = key($currencyDetails);
			$db->createCommand()
				->update($this->getEntity()->table_name, ['currency_id' => $curid], [$this->getEntity()->table_index => $this->getId()])
				->execute();
		}
		\App\Log::trace('Exiting ' . __METHOD__);
	}

	/**
	 * This function is used to add the vtiger_attachments. This will call the function uploadAndSaveFile which will upload the attachment into the server and save that attachment information in the database.
	 */
	public function insertAttachment()
	{
		$db = App\Db::getInstance();
		$id = $this->getId();
		$module = AppRequest::get('module');
		\App\Log::trace("Entering into insertIntoAttachment($id,$module) method.");
		foreach ($_FILES as $fileindex => $files) {
			if (empty($files['tmp_name'])) {
				continue;
			}
			$fileInstance = \App\Fields\File::loadFromRequest($files);
			if ($fileInstance->validate('image')) {
				if (AppRequest::get($fileindex . '_hidden') != '')
					$files['original_name'] = AppRequest::get($fileindex . '_hidden');
				else
					$files['original_name'] = stripslashes($files['name']);
				$files['original_name'] = str_replace('"', '', $files['original_name']);
				$this->uploadAndSaveFile($files);
			}
		}
		//Updating image information in main table of products
		$dataReader = (new App\Db\Query())->select(['name'])->from('vtiger_seattachmentsrel')
				->innerJoin('vtiger_attachments', 'vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid')
				->leftJoin('vtiger_products', 'vtiger_products.productid = vtiger_seattachmentsrel.crmid')
				->where(['vtiger_seattachmentsrel.crmid' => $id])
				->createCommand()->query();
		$productImageMap = [];
		while ($imageName = $dataReader->readColumn(0)) {
			$productImageMap [] = decode_html($imageName);
		}
		$db->createCommand()->update('vtiger_products', ['imagename' => implode(",", $productImageMap)], ['productid' => $id])
			->execute();
		//Remove the deleted vtiger_attachments from db - Products
		if ($module === 'Products' && AppRequest::get('del_file_list') != '') {
			$deleteFileList = explode("###", trim(AppRequest::get('del_file_list'), "###"));
			foreach ($deleteFileList as $fileName) {
				$attachmentId = (new App\Db\Query())->select(['vtiger_attachments.attachmentsid'])
					->from('vtiger_attachments')
					->innerJoin('vtiger_seattachmentsrel', 'vtiger_attachments.attachmentsid = vtiger_seattachmentsrel.attachmentsid')
					->where(['crmid' => $id, 'name' => $fileName])
					->scalar();
				$db->createCommand()->delete('vtiger_attachments', ['attachmentsid' => $attachmentId])->execute();
				$db->createCommand()->delete('vtiger_seattachmentsrel', ['attachmentsid' => $attachmentId])->execute();
			}
		}
		\App\Log::trace("Exiting from insertIntoAttachment($id,$module) method.");
	}
}
