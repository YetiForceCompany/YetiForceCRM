<?php
/* +*******************************************************************************
 *  The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * ******************************************************************************* */

require_once "include/Webservices/VtigerActorOperation.php";
require_once "include/Webservices/LineItem/VtigerInventoryOperation.php";
require_once("include/events/include.inc");
require_once 'modules/com_vtiger_workflow/VTEntityCache.inc';
require_once 'include/CRMEntity.php';
require_once 'include/events/SqlResultIterator.inc';
require_once 'include/Webservices/LineItem/VtigerLineItemMeta.php';
require_once 'include/Webservices/Retrieve.php';
require_once 'include/Webservices/Update.php';
require_once 'include/Webservices/Utils.php';
require_once 'modules/Emails/mail.php';
require_once 'include/utils/InventoryUtils.php';

/**
 * Description of VtigerLineItemOperation
 */
class VtigerLineItemOperation extends VtigerActorOperation
{

	private static $lineItemCache = [];
	private $taxType = null;
	private $Individual = 'Individual';
	private $Group = 'Group';
	private $newId = null;
	private $taxList = null;
	private static $parentCache = [];

	public function __construct($webserviceObject, $user, $adb, $log)
	{
		$this->user = $user;
		$this->log = $log;
		$this->webserviceObject = $webserviceObject;
		$this->pearDB = $adb;
		$this->entityTableName = $this->getActorTables();
		if ($this->entityTableName === null) {
			throw new WebServiceException(WebServiceErrorCode::$UNKOWNENTITY, "Entity is not associated with any tables");
		}
		$this->meta = new VtigerLineItemMeta($this->entityTableName, $webserviceObject, $adb, $user);
		$this->moduleFields = null;
		$this->taxList = [];
	}

	protected function getNextId($elementType, $element)
	{
		$sql = sprintf('SELECT MAX(%s) as maxvalue_lineitem_id FROM %s', $this->meta->getIdColumn(), $this->entityTableName);
		$result = $this->pearDB->pquery($sql, []);
		$numOfRows = $this->pearDB->num_rows($result);

		for ($i = 0; $i < $numOfRows; $i++) {
			$row = $this->pearDB->query_result($result, $i, 'maxvalue_lineitem_id');
		}

		$id = $row + 1;
		return $id;
	}

	public function recreate($lineItem, $parent)
	{
		$components = vtws_getIdComponents($lineItem['id']);
		$this->newId = $components[1];
		$elementType = 'LineItem';
		$this->initTax($lineItem, $parent);
		$this->_create($elementType, $lineItem);
	}

	/**
	 * Function gives all the line items related to inventory records
	 * @param $parentId - record id or array of the inventory record id's
	 * @return <Array> - list of line items
	 * @throws WebServiceException - Database error
	 */
	public function getAllLineItemForParent($parentId)
	{
		if (is_array($parentId)) {
			$result = null;
			$query = "SELECT * FROM {$this->entityTableName} WHERE id IN (" . generateQuestionMarks($parentId) . ")";
			$transactionSuccessful = vtws_runQueryAsTransaction($query, array($parentId), $result);
			if (!$transactionSuccessful) {
				throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR, "Database error while performing required operation");
			}
			$lineItemList = [];
			if ($result) {
				$rowCount = $this->pearDB->num_rows($result);
				for ($i = 0; $i < $rowCount; ++$i) {
					$element = $this->pearDB->query_result_rowdata($result, $i);
					$element['parent_id'] = $parentId;
					$lineItemList[$element['id']][] = DataTransform::filterAndSanitize($element, $this->meta);
				}
			}
			return $lineItemList;
		} else {
			$result = null;
			$query = "select * from {$this->entityTableName} where id=?";
			$transactionSuccessful = vtws_runQueryAsTransaction($query, array($parentId), $result);
			if (!$transactionSuccessful) {
				throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR, "Database error while performing required operation");
			}
			$lineItemList = [];
			if ($result) {
				$rowCount = $this->pearDB->num_rows($result);
				for ($i = 0; $i < $rowCount; ++$i) {
					$element = $this->pearDB->query_result_rowdata($result, $i);
					$element['parent_id'] = $parentId;
					$lineItemList[] = DataTransform::filterAndSanitize($element, $this->meta);
				}
			}
			return $lineItemList;
		}
	}

	public function _create($elementType, $element)
	{
		$createdElement = parent::create($elementType, $element);
		$productId = vtws_getIdComponents($element['productid']);
		$productId = $productId[1];

		$parentTypeHandler = vtws_getModuleHandlerFromId($element['parent_id'], $this->user);
		$parentTypeMeta = $parentTypeHandler->getMeta();
		$parentType = $parentTypeMeta->getEntityName();
		$parent = $this->getParentById($element['parent_id']);
		updateStk($productId, $element['quantity'], '', [], $parentType);

		$this->initTax($element, $parent);
		$this->updateTaxes($createdElement);
		$createdElement['incrementondel'] = '1';
		if (strcasecmp($parent['hdnTaxType'], $this->Individual) === 0) {
			$createdElement = $this->appendTaxInfo($createdElement);
		}
		return $createdElement;
	}

	private function appendTaxInfo($element)
	{
		$meta = $this->getMeta();
		$moduleFields = $meta->getModuleFields();
		foreach ($moduleFields as $fieldName => $field) {
			if (preg_match('/tax\d+/', $fieldName) != 0) {
				if (is_array($this->taxList[$fieldName])) {
					$element[$fieldName] = $this->taxList[$fieldName]['percentage'];
				} else {
					$element[$fieldName] = '0.000';
				}
			}
		}
		return $element;
	}

	private function resetTaxInfo($element, $parent)
	{
		$productTaxInfo = [];
		if (empty($this->taxType)) {
			list($typeId, $recordId) = vtws_getIdComponents($element['productid']);
			$productTaxInfo = $this->getProductTaxList($recordId);
		}
		if (count($productTaxInfo) == 0 &&
			strcasecmp($parent['hdnTaxType'], $this->Individual) !== 0) {
			$meta = $this->getMeta();
			$moduleFields = $meta->getModuleFields();
			foreach ($moduleFields as $fieldName => $field) {
				if (preg_match('/tax\d+/', $fieldName) != 0) {
					$element[$fieldName] = '0.000';
				}
			}
		}
		return $element;
	}

	private function updateTaxes($createdElement)
	{
		if (count($this->taxList) > 0) {
			$id = vtws_getIdComponents($createdElement['id']);
			$id = $id[1];
			$sql = 'UPDATE vtiger_inventoryproductrel set ';
			$sql .= implode('=?,', array_keys($this->taxList));
			$sql .= '=? WHERE lineitem_id = ?';
			$params = [];
			foreach ($this->taxList as $taxInfo) {
				$params[] = $taxInfo['percentage'];
			}
			$params[] = $id;
			$result = null;
			$transactionSuccessful = vtws_runQueryAsTransaction($sql, $params, $result);
			if (!$transactionSuccessful) {
				throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR, "Database error while performing required operation");
			}
		}
	}

	private function initTax($element, $parent)
	{
		if (!empty($element['parent_id'])) {
			$this->taxType = $parent['hdnTaxType'];
		}
		$productId = vtws_getIdComponents($element['productid']);
		$productId = $productId[1];
		if (strcasecmp($parent['hdnTaxType'], $this->Individual) === 0) {
			$found = false;
			$meta = $this->getMeta();
			$moduleFields = $meta->getModuleFields();
			$productTaxList = $this->getProductTaxList($productId);
			if (count($productTaxList) > 0) {
				foreach ($moduleFields as $fieldName => $field) {
					if (preg_match('/tax\d+/', $fieldName) != 0) {
						if (!empty($element[$fieldName])) {
							$found = true;
							if (is_array($productTaxList[$fieldName])) {
								$this->taxList[$fieldName] = array(
									'label' => $field->getFieldLabelKey(),
									'percentage' => $element[$fieldName]
								);
							}
						}
					}
				}
			} elseif ($found == false) {
				array_merge($this->taxList, $productTaxList);
			}
		} else {
			$meta = $this->getMeta();
			$moduleFields = $meta->getModuleFields();
			$availableTaxes = getAllTaxes('available');
			$found = false;
			foreach ($moduleFields as $fieldName => $field) {
				if (preg_match('/tax\d+/', $fieldName) != 0) {
					$found = true;
					if (!empty($element[$fieldName])) {
						$this->taxList[$fieldName] = array(
							'label' => $field->getFieldLabelKey(),
							'percentage' => $element[$fieldName]
						);
					}
				}
			}
			if (!$found) {
				foreach ($availableTaxes as $taxInfo) {
					$this->taxList[$taxInfo['taxname']] = array(
						'label' => $field->getFieldLabelKey(),
						'percentage' => $taxInfo['percentage']
					);
				}
			}
		}
		$this->taxList;
	}

	public function cleanLineItemList($parentId)
	{
		$components = vtws_getIdComponents($parentId);
		$pId = $components[1];

		$parentTypeHandler = vtws_getModuleHandlerFromId($parentId, $this->user);
		$parentTypeMeta = $parentTypeHandler->getMeta();
		$parentType = $parentTypeMeta->getEntityName();

		$parentObject = CRMEntity::getInstance($parentType);
		$parentObject->id = $pId;
		$lineItemList = $this->getAllLineItemForParent($pId);
		deleteInventoryProductDetails($parentObject);
		$this->resetInventoryStockById($parentId);
	}

	public function setLineItems($elementType, $lineItemList, $parent)
	{
		foreach ($lineItemList as $lineItem) {
			$lineItem['parent_id'] = $parent['id'];
			$this->initTax($lineItem, $parent);
			$id = vtws_getIdComponents($lineItem['parent_id']);
			$this->newId = $id[1];
			$this->create($elementType, $lineItem);
		}
	}

	public function create($elementType, $element)
	{
		$parentId = vtws_getIdComponents($element['parent_id']);
		$parentId = $parentId[1];

		$parent = $this->getParentById($element['parent_id']);
		if (empty($element['listprice'])) {
			$productId = vtws_getIdComponents($element['productid']);
			$productId = $productId[1];
			$element['listprice'] = $this->getProductPrice($productId);
		}
		$id = vtws_getIdComponents($element['parent_id']);
		$this->newId = $id[1];
		$createdLineItem = $this->_create($elementType, $element);
		$updatedLineItemList = $createdLineItem;
		$updatedLineItemList['parent_id'] = $element['parent_id'];
		$this->setCache($parentId, $updatedLineItemList);
		$this->updateInventoryStock($element, $parent);
		return $createdLineItem;
	}

	public function retrieve($id)
	{
		$element = parent::retrieve($id);
		$parent = $this->getParentById($element['parent_id']);
		return $this->resetTaxInfo($element, $parent);
	}

	public function update($element)
	{
		$parentId = vtws_getIdComponents($element['parent_id']);
		$parentId = $parentId[1];
		$parentTypeHandler = vtws_getModuleHandlerFromId($element['parent_id'], $this->user);
		$parentTypeMeta = $parentTypeHandler->getMeta();
		$parentType = $parentTypeMeta->getEntityName();
		$parentObject = CRMEntity::getInstance($parentType);
		$parentObject->id = $parentId;
		$lineItemList = $this->getAllLineItemForParent($parentId);
		$parent = $this->getParentById($element['parent_id']);
		$location = $this->getLocationById($lineItemList, $element['id']);
		if ($location === false) {
			throw new WebserviceException('UNKOWN_CHILD', 'given line  item is not child of parent');
		}
		if (empty($element['listprice'])) {
			$productId = vtws_getIdComponents($element['productid']);
			$productId = $productId[1];
			$element['listprice'] = $this->getProductPrice($productId);
		}
		$lineItemList[$location] = $element;
		deleteInventoryProductDetails($parentObject);
		$this->resetInventoryStock($element, $parent);
		$updatedLineItemList = [];
		foreach ($lineItemList as $lineItem) {
			$id = vtws_getIdComponents($lineItem['id']);
			$this->newId = $id[1];
			$updatedLineItemList[] = $this->_create($elementType, $lineItem);
			if ($element == $lineItem) {
				$createdElement = $updatedLineItemList[count($updatedLineItemList) - 1];
			}
		}
		$this->setCache($parentId, $updatedLineItemList);
		$this->updateInventoryStock($element, $parent);
		$this->updateParent($element, $parent);
		return $createdElement;
	}

	public function getProductPrice($productId)
	{
		$db = PearDatabase::getInstance();
		$sql = "select unit_price from vtiger_products where productid=?";
		$params = array($productId);
		$result = null;
		$transactionSuccessful = vtws_runQueryAsTransaction($sql, $params, $result);
		if (!$transactionSuccessful) {
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR, "Database error while performing required operation");
		}
		$price = 0;
		$it = new SqlResultIterator($db, $result);
		foreach ($it as $row) {
			$price = $row->unit_price;
		}
		return $price;
	}

	private function getLocationById($lineItemList, $id)
	{
		foreach ($lineItemList as $index => $lineItem) {
			if ($lineItem['id'] == $id) {
				return $index;
			}
		}
		return false;
	}

	public function delete($id)
	{
		$element = vtws_retrieve($id, $this->user);
		if (!empty($element['parent_id'])) {
			$parent = $this->getParentById($element['parent_id']);
		}
		$parentId = vtws_getIdComponents($element['parent_id']);
		$parentId = $parentId[1];
		$lineItemList = $this->getAllLineItemForParent($parentId);
		$this->cleanLineItemList($element['parent_id']);
		$this->initTax($element, $parent);
		$result = parent::delete($id);
		$updatedList = [];
		$element = null;
		foreach ($lineItemList as $lineItem) {
			if ($id != $lineItem['id']) {
				$updatedList[] = $lineItem;
			} else {
				$element = $lineItem;
			}
		}
		$this->setLineItems('LineItem', $updatedList, $parent);
		$this->resetCacheForParent($parentId);
		$this->updateParent($element, $parent);
		$this->updateInventoryStock($element, $parent);
		return $result;
	}

	private function resetCacheForParent($parentId)
	{
		self::$lineItemCache[$parentId] = null;
	}

	public function updateParent($createdElement, $parent)
	{
		$discount = 0;
		$parentId = vtws_getIdComponents($parent['id']);
		$parentId = $parentId[1];
		$lineItemList = $this->getAllLineItemForParent($parentId);
		$parent['hdnSubTotal'] = 0;
		$taxAmount = 0;
		foreach ($lineItemList as $lineItem) {
			$discount = 0;
			$lineItemTotal = $lineItem['listprice'] * $lineItem['quantity'];
			$lineItem['discount_amount'] = (float) ($lineItem['discount_amount']);
			$lineItem['discount_percent'] = (float) ($lineItem['discount_percent']);
			if (!empty($lineItem['discount_amount'])) {
				$discount = ($lineItem['discount_amount']);
			} elseif (!empty($lineItem['discount_percent'])) {
				$discount = ($lineItem['discount_percent']) / 100 * $lineItemTotal;
			}
			$this->initTax($lineItem, $parent);
			$lineItemTotal = $lineItemTotal - $discount;
			$parent['hdnSubTotal'] = ($parent['hdnSubTotal'] ) + $lineItemTotal;
			if (strcasecmp($parent['hdnTaxType'], $this->Individual) === 0) {
				foreach ($this->taxList as $taxInfo) {
					$lineItemTaxAmount = ($taxInfo['percentage']) / 100 * $lineItemTotal;
					$parent['hdnSubTotal'] += $lineItemTaxAmount;
				}
			}
		}

		if (!empty($parent['hdnDiscountAmount']) && ((double) $parent['hdnDiscountAmount']) > 0) {
			$discount = ($parent['hdnDiscountAmount']);
		} elseif (!empty($parent['hdnDiscountPercent'])) {
			$discount = ($parent['hdnDiscountPercent'] / 100 * $parent['hdnSubTotal']);
		}
		$parent['pre_tax_total'] = $total = $parent['hdnSubTotal'] - $discount;
		$taxTotal = $parent['hdnSubTotal'] - $discount;
		if (strcasecmp($parent['hdnTaxType'], $this->Individual) !== 0) {
			$this->initTax($createdElement, $parent);
			foreach ($this->taxList as $taxInfo) {
				$taxAmount += ($taxInfo['percentage']) / 100 * $taxTotal;
			}
		}
		$parent['hdnGrandTotal'] = $total + $taxAmount;

		$parentTypeHandler = vtws_getModuleHandlerFromId($parent['id'], $this->user);
		$parentTypeMeta = $parentTypeHandler->getMeta();
		$parentType = $parentTypeMeta->getEntityName();

		$parentInstance = CRMEntity::getInstance($parentType);
		$sql = sprintf('update %s set subtotal=?, total=?, pre_tax_total=? where %s = ?', $parentInstance->table_name, $parentInstance->tab_name_index[$parentInstance->table_name]);
		$params = array($parent['hdnSubTotal'], $parent['hdnGrandTotal'], $parent['pre_tax_total'], $parentId);
		$transactionSuccessful = vtws_runQueryAsTransaction($sql, $params, $result);
		self::$parentCache[$parent['id']] = $parent;
		if (!$transactionSuccessful) {
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR, "Database error while performing required operation");
		}
	}

	public function getCollectiveTaxList()
	{
		$db = PearDatabase::getInstance();
		$sql = 'select * from vtiger_inventorytaxinfo where deleted=0';
		$params = [];
		$result = null;
		$transactionSuccessful = vtws_runQueryAsTransaction($sql, $params, $result);
		if (!$transactionSuccessful) {
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR, "Database error while performing required operation");
		}
		$it = new SqlResultIterator($db, $result);
		$this->taxList = [];
		foreach ($it as $row) {
			$this->taxList[$row->taxname] = array('label' => $row->taxlabel,
				'percentage' => $row->percentage);
		}
		return $this->taxList;
	}

	private function getProductTaxList($productId)
	{
		$db = PearDatabase::getInstance();
		$sql = 'select * from vtiger_producttaxrel inner join vtiger_inventorytaxinfo on
			vtiger_producttaxrel.taxid=vtiger_inventorytaxinfo.taxid and deleted=0
			where productid=?';
		$params = array($productId);
		$result = null;
		$transactionSuccessful = vtws_runQueryAsTransaction($sql, $params, $result);
		if (!$transactionSuccessful) {
			throw new WebServiceException(WebServiceErrorCode::$DATABASEQUERYERROR, "Database error while performing required operation");
		}
		$it = new SqlResultIterator($db, $result);
		$this->taxList = [];
		foreach ($it as $row) {
			$this->taxList[$row->taxname] = array('label' => $row->taxlabel,
				'percentage' => $row->taxpercentage);
		}
		return $this->taxList;
	}

	private function updateInventoryStock($element, $parent)
	{
		global $updateInventoryProductRel_update_product_array;
		$updateInventoryProductRel_update_product_array = [];
		$entityCache = new VTEntityCache($this->user);
		$entityData = $entityCache->forId($element['parent_id']);
		updateInventoryProductRel($entityData);
	}

	private function resetInventoryStock($element, $parent)
	{
		if (!empty($parent['id'])) {
			$this->resetInventoryStockById($parent['id']);
		}
	}

	private function resetInventoryStockById($parentId)
	{
		if (!empty($parentId)) {
			$entityCache = new VTEntityCache($this->user);
			$entityData = $entityCache->forId($parentId);
			updateInventoryProductRel($entityData);
		}
	}

	public function getParentById($parentId)
	{
		if (empty(self::$parentCache[$parentId])) {
			return vtws_retrieve($parentId, $this->user);
		} else {
			return self::$parentCache[$parentId];
		}
	}

	public function setCache($parentId, $updatedList)
	{
		self::$lineItemCache[$parentId] = $updatedList;
	}

	public function __create($elementType, $element)
	{
		$element['id'] = $element['parent_id'];
		unset($element['parent_id']);
		$success = parent::__create($elementType, $element);
		return $success;
	}

	protected function getElement()
	{
		if (!empty($this->element['id'])) {
			$this->element['parent_id'] = $this->element['id'];
		}
		return $this->element;
	}

	public function describe($elementType)
	{
		$describe = parent::describe($elementType);
		foreach ($describe['fields'] as $key => $list) {
			if ($list["name"] == 'description') {
				unset($describe['fields'][$key]);
			}
		}
		return $describe;
	}
}
