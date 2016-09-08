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

class Products_Relation_Model extends Vtiger_Relation_Model
{

	/**
	 * Function returns the Query for the relationhips
	 * @param <Vtiger_Record_Model> $recordModel
	 * @param type $actions
	 * @return <String>
	 */
	public function getQuery($recordModel, $actions = false, $relationListView_Model = false)
	{
		$parentModuleModel = $this->getParentModuleModel();
		$relatedModuleModel = $this->getRelationModuleModel();
		$relatedModuleName = $relatedModuleModel->get('name');
		$parentModuleName = $parentModuleModel->get('name');
		$functionName = $this->get('name');
		$focus = CRMEntity::getInstance($parentModuleName);
		$focus->id = $recordModel->getId();
		if (method_exists($parentModuleModel, $functionName)) {
			$query = $parentModuleModel->$functionName($recordModel, $relatedModuleModel);
		} else {
			$query = $parentModuleModel->getRelationQuery($recordModel->getId(), $functionName, $relatedModuleModel, $this, $relationListView_Model);
		}

		//modify query if any module has summary fields, those fields we are displayed in related list of that module
		$relatedListFields = $this->getRelationFields(true, true);
		if (count($relatedListFields) == 0) {
			$relatedListFields = $relatedModuleModel->getConfigureRelatedListFields();
		}
		if (count($relatedListFields) > 0) {
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$queryGenerator = new QueryGenerator($relatedModuleName, $currentUser);
			$queryGenerator->setFields($relatedListFields);
			$selectColumnSql = $queryGenerator->getSelectClauseColumnSQL();
			$newQuery = explode('FROM', $query);
			$selectColumnSql = sprintf('SELECT DISTINCT vtiger_crmentity.crmid, %s', $selectColumnSql);
		}
		if ($functionName == 'get_product_pricebooks') {
			$selectColumnSql = $selectColumnSql . ' ,vtiger_pricebookproductrel.listprice, vtiger_pricebook.currency_id, vtiger_products.unit_price';
		} elseif ($functionName == 'get_service_pricebooks') {
			$selectColumnSql = $selectColumnSql . ' ,vtiger_pricebookproductrel.listprice, vtiger_pricebook.currency_id, vtiger_service.unit_price';
		} elseif ($functionName == 'get_many_to_many' && $relatedModuleName == 'IStorages') {
			$referenceInfo = Vtiger_Relation_Model::getReferenceTableInfo($relatedModuleName, $parentModuleName);
			$selectColumnSql = $selectColumnSql . ' ,' . $referenceInfo['table'] . '.qtyinstock';
		}
		if ($selectColumnSql && $newQuery[1])
			$query = $selectColumnSql . ' FROM ' . $newQuery[1];
		if ($relationListView_Model) {
			$queryGenerator = $relationListView_Model->get('query_generator');
			$joinTable = $queryGenerator->getFromClause(true);
			if ($joinTable) {
				$queryComponents = preg_split('/WHERE/i', $query);
				$query = $queryComponents[0] . $joinTable . ' WHERE ' . $queryComponents[1];
			}
			$where = $queryGenerator->getWhereClause(true);
			$query .= $where;
		}
		return $query;
	}

	/**
	 * Function that deletes PriceBooks related records information
	 * @param <Integer> $sourceRecordId - Product/Service Id
	 * @param <Integer> $relatedRecordId - Related Record Id
	 */
	public function deleteRelation($sourceRecordId, $relatedRecordId)
	{
		$sourceModuleName = $this->getParentModuleModel()->get('name');
		$relatedModuleName = $this->getRelationModuleModel()->get('name');
		if (($sourceModuleName == 'Products' || $sourceModuleName == 'Services') && $relatedModuleName == 'PriceBooks') {
			//Description: deleteListPrice function is deleting the relation between Pricebook and Product/Service 
			$priceBookModel = Vtiger_Record_Model::getInstanceById($relatedRecordId, $relatedModuleName);
			$priceBookModel->deleteListPrice($sourceRecordId);
		} else if ($sourceModuleName == $relatedModuleName) {
			$this->deleteProductToProductRelation($sourceRecordId, $relatedRecordId);
		} else {
			parent::deleteRelation($sourceRecordId, $relatedRecordId);
		}
	}

	/**
	 * Function to delete the product to product relation(product bundles)
	 * @param type $sourceRecordId
	 * @param type $relatedRecordId true / false
	 * @return <boolean>
	 */
	public function deleteProductToProductRelation($sourceRecordId, $relatedRecordId)
	{
		$db = PearDatabase::getInstance();
		if (!empty($sourceRecordId) && !empty($relatedRecordId)) {
			$db->pquery('DELETE FROM vtiger_seproductsrel WHERE crmid = ? && productid = ?', array($relatedRecordId, $sourceRecordId));
			return true;
		}
	}

	public function isSubProduct($subProductId)
	{
		if (!empty($subProductId)) {
			$db = PearDatabase::getInstance();
			$result = $db->pquery('SELECT crmid FROM vtiger_seproductsrel WHERE crmid = ?', array($subProductId));
			if ($db->num_rows($result) > 0) {
				return true;
			}
		}
	}

	/**
	 * Function to add Products/Services-PriceBooks Relation
	 * @param <Integer> $sourceRecordId
	 * @param <Integer> $destinationRecordId
	 * @param <Integer> $listPrice
	 */
	public function addListPrice($sourceRecordId, $destinationRecordId, $listPrice)
	{
		$sourceModuleName = $this->getParentModuleModel()->get('name');
		$relatedModuleName = $this->getRelationModuleModel()->get('name');
		$relationModuleModel = Vtiger_Record_Model::getInstanceById($destinationRecordId, $relatedModuleName);

		$productModel = Vtiger_Record_Model::getInstanceById($sourceRecordId, $sourceModuleName);
		$productModel->updateListPrice($destinationRecordId, $listPrice, $relationModuleModel->get('currency_id'));
	}
}
