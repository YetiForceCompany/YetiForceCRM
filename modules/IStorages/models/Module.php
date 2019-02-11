<?php

/**
 * Module Class for IStorages.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class IStorages_Module_Model extends Vtiger_Module_Model
{
	public static $modulesToCalculate = ['add' => ['IGRN', 'IIDN', 'ISTRN', 'IGRNC'], 'remove' => ['IGDN', 'IGIN', 'IPreOrder', 'ISTDN', 'IGDNC']];

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

	public static function recalculateStock($moduleName = false, $data = false, $storageId = false, $action = false)
	{
		if ($moduleName === false) {
			self::setQtyInStocks(self::getAllQtyInStocks());
		} else {
			self::setQtyInStock($moduleName, $data, $storageId, $action);
		}
	}

	public static function setQtyInStock($moduleName, $data, $storageId, $action)
	{
		$db = App\Db::getInstance();
		$qtyInStock = [];
		foreach ($data as $product) {
			if (!isset($qtyInStock[$product['name']])) {
				$qtyInStock[$product['name']] = 0;
			}
			$qtyInStock[$product['name']] += $product['qty'];
		}
		$operator = self::getOperator($moduleName, $action);
		// Update qtyinstock in Products
		$expresion = 'CASE ';
		foreach ($qtyInStock as $id => $value) {
			$expresion .= " WHEN {$db->quoteColumnName('productid')} = {$db->quoteValue($id)} THEN (qtyinstock {$operator} {$db->quoteValue($value)})";
		}
		$expresion .= ' END';
		$db->createCommand()->update('vtiger_products', ['qtyinstock' => new yii\db\Expression($expresion)], ['productid' => array_keys($qtyInStock)])->execute();
		// Saving the amount of product in stock.
		$referenceInfo = Vtiger_Relation_Model::getReferenceTableInfo('Products', 'IStorages');
		$relData = (new App\Db\Query())->select([$referenceInfo['rel'], 'qtyinstock'])
			->from($referenceInfo['table'])
			->where([$referenceInfo['base'] => $storageId, $referenceInfo['rel'] => array_keys($qtyInStock)])
			->createCommand()->queryAllByGroup(0);
		foreach ($qtyInStock as $id => $value) {
			if (array_key_exists($id, $relData)) {
				$db->createCommand()->update($referenceInfo['table'], [
					'qtyinstock' => new yii\db\Expression('qtyinstock ' . $operator . ' ' . $value),
				], [$referenceInfo['base'] => $storageId, $referenceInfo['rel'] => $id])->execute();
			} else {
				$db->createCommand()->insert($referenceInfo['table'], [
					$referenceInfo['base'] => $storageId,
					$referenceInfo['rel'] => $id,
					'qtyinstock' => $operator == '+' ? $value : $operator . $value,
				])->execute();
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
				if (\App\Module::isModuleActive($moduleName) === false) {
					continue;
				}
				$inventoryTableName = Vtiger_Inventory_Model::getInstance($moduleName)->getDataTableName();
				$focus = CRMEntity::getInstance($moduleName);
				$sql[] = sprintf('SELECT %s.name AS productid, %s.storageid AS storageid,  SUM( DISTINCT %s.qty) AS p_sum FROM  %s LEFT JOIN (%s LEFT JOIN vtiger_crmentity AS cr ON cr.crmid = %s.name) ON %s.%s = %s.crmid LEFT JOIN vtiger_crmentity ON %s.%s = vtiger_crmentity.`crmid` WHERE vtiger_crmentity.`deleted` = 0 && cr.`deleted` = 0 && %s.%s_status = "PLL_ACCEPTED" GROUP BY productid, storageid', $inventoryTableName, $focus->table_name, $inventoryTableName, $focus->table_name, $inventoryTableName, $inventoryTableName, $focus->table_name, $focus->table_index, $inventoryTableName, $focus->table_name, $focus->table_index, $focus->table_name, strtolower($moduleName));
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
			foreach ($sumProduct as $id => $value) {
				$query .= ' WHEN `productid` = ? THEN ?';
				array_push($params, $id, $value);
			}
			$query .= ' END WHERE `productid` IN (' . $db->generateQuestionMarks(array_keys($sumProduct)) . ')';
			$db->pquery($query, array_merge($params, array_keys($sumProduct)));
		}
		$referenceInfo = Vtiger_Relation_Model::getReferenceTableInfo('Products', 'IStorages');
		$db->delete($referenceInfo['table']);
		if (!empty($sumProductInStorage)) {
			foreach ($sumProductInStorage as $id => $values) {
				foreach ($values as $productId => $value) {
					$db->insert($referenceInfo['table'], ['crmid' => $id, 'relcrmid' => $productId, 'qtyinstock' => $value]);
				}
			}
		}
	}
}
