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
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery, $skipSelected = false)
	{
		$supportedModulesList = array($this->getName(), 'Vendors', 'Leads', 'Accounts', 'Contacts');
		if (($sourceModule == 'PriceBooks' && $field == 'priceBookRelatedList') || in_array($sourceModule, $supportedModulesList) || in_array($sourceModule, getInventoryModules())) {

			$condition = " vtiger_products.discontinued = 1 ";
			if ($sourceModule === $this->getName()) {
				$condition .= " && vtiger_products.productid NOT IN (SELECT productid FROM vtiger_seproductsrel WHERE crmid = '$record' UNION SELECT crmid FROM vtiger_seproductsrel WHERE productid = '$record') && vtiger_products.productid <> '$record' ";
			} elseif ($sourceModule === 'PriceBooks') {
				$condition .= " && vtiger_products.productid NOT IN (SELECT productid FROM vtiger_pricebookproductrel WHERE pricebookid = '$record') ";
			} elseif ($sourceModule === 'Vendors') {
				$condition .= " && vtiger_products.vendor_id != '$record' ";
			} elseif (in_array($sourceModule, $supportedModulesList) && $skipSelected === false) {
				$condition .= " && vtiger_products.productid NOT IN (SELECT productid FROM vtiger_seproductsrel WHERE crmid = '$record')";
			}

			$pos = stripos($listQuery, 'where');
			if ($pos) {
				$overRideQuery = $listQuery . ' && ' . $condition;
			} else {
				$overRideQuery = $listQuery . ' WHERE ' . $condition;
			}
			return $overRideQuery;
		}
	}

	/**
	 * Function to get Specific Relation Query for this Module
	 * @param <type> $relatedModule
	 * @return <type>
	 */
	public function getSpecificRelationQuery($relatedModule)
	{
		if ($relatedModule === 'Leads') {
			$specificQuery = 'AND vtiger_leaddetails.converted = 0';
			return $specificQuery;
		}
		return parent::getSpecificRelationQuery($relatedModule);
	}

	/**
	 * Function to get prices for specified products with specific currency
	 * @param <Integer> $currenctId
	 * @param <Array> $productIdsList
	 * @return <Array>
	 */
	public function getPricesForProducts($currencyId, $productIdsList)
	{
		return getPricesForProducts($currencyId, $productIdsList, $this->getName());
	}

	/**
	 * Function to check whether the module is summary view supported
	 * @return <Boolean> - true/false
	 */
	public function isSummaryViewSupported()
	{
		return false;
	}

	/**
	 * Function searches the records in the module, if parentId & parentModule
	 * is given then searches only those records related to them.
	 * @param <String> $searchValue - Search value
	 * @param <Integer> $parentId - parent recordId
	 * @param <String> $parentModule - parent module name
	 * @return <Array of Vtiger_Record_Model>
	 */
	public function searchRecord($searchValue, $parentId = false, $parentModule = false, $relatedModule = false)
	{
		if (!empty($searchValue) && empty($parentId) && empty($parentModule) && (in_array($relatedModule, getInventoryModules()))) {
			$matchingRecords = Products_Record_Model::getSearchResult($searchValue, $this->getName());
		} else {
			return parent::searchRecord($searchValue);
		}

		return $matchingRecords;
	}

	/**
	 * Function returns query for Product-PriceBooks relation
	 * @param <Vtiger_Record_Model> $recordModel
	 * @param <Vtiger_Record_Model> $relatedModuleModel
	 * @return <String>
	 */
	public function get_product_pricebooks($recordModel, $relatedModuleModel)
	{
		$query = 'SELECT vtiger_pricebook.pricebookid, vtiger_pricebook.bookname, vtiger_pricebook.active, vtiger_crmentity.crmid, 
						vtiger_crmentity.smownerid, vtiger_pricebookproductrel.listprice, vtiger_products.unit_price
					FROM vtiger_pricebook
					INNER JOIN vtiger_pricebookproductrel ON vtiger_pricebook.pricebookid = vtiger_pricebookproductrel.pricebookid
					INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_pricebook.pricebookid
					INNER JOIN vtiger_products on vtiger_products.productid = vtiger_pricebookproductrel.productid
					INNER JOIN vtiger_pricebookcf on vtiger_pricebookcf.pricebookid = vtiger_pricebook.pricebookid
					LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid
					LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid '
			. Users_Privileges_Model::getNonAdminAccessControlQuery($relatedModuleModel->getName()) . '
					WHERE vtiger_products.productid = ' . $recordModel->getId() . ' and vtiger_crmentity.deleted = 0';

		return $query;
	}
}
