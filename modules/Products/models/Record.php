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
			} elseif (2 === \App\Config::search('GLOBAL_SEARCH_SORTING_RESULTS')) {
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
				$limit = App\Config::search('GLOBAL_SEARCH_MODAL_MAX_NUMBER_RESULT');
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
	 * The function decide about mandatory save record.
	 *
	 * @return type
	 */
	public function isMandatorySave()
	{
		return true;
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
