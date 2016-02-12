<?php

/**
 * Module Class for IStorages
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class IStorages_Module_Model extends Vtiger_Module_Model
{

	public static $modulesToCalculate = ['add' => ['IGRN', 'IIDN'], 'remove' => ['IGDN', 'IGIN']];

	public static function getOperator($moduleName, $action)
	{
		if (in_array($moduleName, self::$modulesToCalculate['add'])) {
			if ('add' == $action) {
				return '+';
			}
			return '-';
		}
		if (in_array($moduleName, self::$modulesToCalculate['remove'])) {
			if ('add' == $action) {
				return '-';
			}
			return '+';
		}
	}

	public static function RecalculateStock($moduleName = false, $data = false, $storageId = false, $action = false)
	{
		if ($moduleName === false) {
			self::setQtyInStocks(self::getAllQtyInStocks());
		} else {
			self::setQtyInStock($moduleName, $data, $storageId, $action);
		}
	}

	public static function setQtyInStock($moduleName, $data, $storageId, $action)
	{
		$db = PearDatabase::getInstance();
		foreach ($data as $product) {
			$qtyInStock[$product['name']] += $product['qty'];
		}
		$operator = self::getOperator($moduleName, $action);
		$qty = '(qtyinstock ' . $operator . ' ?)';

		// Update qtyinstock in Products
		$params = [];
		$query = 'UPDATE vtiger_products SET qtyinstock = CASE ';
		foreach ($qtyInStock as $ID => $value) {
			$query .= ' WHEN `productid` = ? THEN ' . $qty;
			array_push($params, $ID, $value);
		}
		$query .= ' END WHERE `productid` IN (' . $db->generateQuestionMarks(array_keys($qtyInStock)) . ')';
		$db->pquery($query, array_merge($params, array_keys($qtyInStock)));

		// Saving the amount of product in stock.
		$referenceInfo = Vtiger_Relation_Model::getReferenceTableInfo('Products', 'IStorages');
		$result = $db->pquery('SELECT ' . $referenceInfo['rel'] . ',qtyinstock FROM ' . $referenceInfo['table']
			. ' WHERE `' . $referenceInfo['base'] . '` = ? AND `' . $referenceInfo['rel'] . '` IN (' . $db->generateQuestionMarks(array_keys($qtyInStock)) . ');', array_merge([$storageId], array_keys($qtyInStock)));
		$relData = $result->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);
		foreach ($qtyInStock as $ID => $value) {
			if (array_key_exists($ID, $relData)) {
				$db->pquery('UPDATE ' . $referenceInfo['table'] . ' SET `qtyinstock` = ' . $qty
					. ' WHERE `' . $referenceInfo['base'] . '` = ? AND `' . $referenceInfo['rel'] . '` = ?;', [$value, $storageId, $ID]);
			} else {
				$db->insert($referenceInfo['table'], [$referenceInfo['base'] => $storageId, $referenceInfo['rel'] => $ID, 'qtyinstock' => $operator == '+' ? $value : $operator . $value]);
			}
		}
	}

	public static function getAllQtyInStocks()
	{
		$db = PearDatabase::getInstance();
		$sumProduct = [];
		$sumProductInStorage = [];
		foreach (self::$modulesToCalculate as $type => $modules) {
			$sql = [];
			foreach ($modules as $moduleName) {
				if (vtlib_isModuleActive($moduleName) == false) {
					continue;
				}
				$inventoryTableName = Vtiger_InventoryField_Model::getInstance($moduleName)->getTableName();
				$focus = CRMEntity::getInstance($moduleName);
				$sql[] = 'SELECT ' . $inventoryTableName . '.name AS productid, ' . $focus->table_name . '.storageid AS storageid,  SUM( DISTINCT ' . $inventoryTableName . '.qty) AS p_sum FROM  ' . $focus->table_name . ' LEFT JOIN (' . $inventoryTableName . ' LEFT JOIN vtiger_crmentity AS cr ON cr.crmid = ' . $inventoryTableName . '.name) ON ' . $focus->table_name . '.' . $focus->table_index . ' = ' . $inventoryTableName . '.id LEFT JOIN vtiger_crmentity ON ' . $focus->table_name . '.' . $focus->table_index . ' = vtiger_crmentity.`crmid` WHERE vtiger_crmentity.`deleted` = 0 AND cr.`deleted` = 0 AND ' . $focus->table_name . '.' . strtolower($moduleName) . '_status = "PLL_ACCEPTED" GROUP BY productid, storageid';
			}
			if (!empty($sql)) {
				$result = $db->query(implode(' UNION ALL ', $sql));
				if ($type == 'add') {
					while ($row = $db->getRow($result)) {
						$sumProduct[$row['productid']] += $row['p_sum'];
						$sumProductInStorage[$row['storageid']][$row['productid']] += $row['p_sum'];
					}
				} else {
					while ($row = $db->getRow($result)) {
						$sumProduct[$row['productid']] -= $row['p_sum'];
						$sumProductInStorage[$row['storageid']][$row['productid']] -= $row['p_sum'];
					}
				}
			}
		}
		return [$sumProduct, $sumProductInStorage];
	}

	public static function setQtyInStocks($stock)
	{
		$db = PearDatabase::getInstance();
		list($sumProduct, $sumProductInStorage) = $stock;
		if (empty($sumProduct)) {
			$db->update('vtiger_products', ['qtyinstock' => 0]);
		} else {
			$query = 'UPDATE vtiger_products SET qtyinstock = CASE ';
			$params = [];
			foreach ($sumProduct as $ID => $value) {
				$query .= ' WHEN `productid` = ? THEN ?';
				array_push($params, $ID, $value);
			}
			$query .= ' END WHERE `productid` IN (' . $db->generateQuestionMarks(array_keys($sumProduct)) . ')';
			$db->pquery($query, array_merge($params, array_keys($sumProduct)));
		}
		$referenceInfo = Vtiger_Relation_Model::getReferenceTableInfo('Products', 'IStorages');
		$db->delete($referenceInfo['table']);
		if (!empty($sumProductInStorage)) {
			foreach ($sumProductInStorage as $ID => $values) {
				foreach ($values as $productId => $value) {
					$db->insert($referenceInfo['table'], ['crmid' => $ID, 'relcrmid' => $productId, 'qtyinstock' => $value]);
				}
			}
		}
	}
}
