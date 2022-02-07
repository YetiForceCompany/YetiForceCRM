<?php

/**
 * Module Class for IStorages.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class IStorages_Module_Model extends Vtiger_Module_Model
{
	public static $modulesToCalculate = ['add' => ['IGRN', 'IIDN', 'ISTRN', 'IGRNC'], 'remove' => ['IGDN', 'IGIN', 'IPreOrder', 'ISTDN', 'IGDNC']];

	public static function getOperator($moduleName, $action)
	{
		if (\in_array($moduleName, self::$modulesToCalculate['add'])) {
			if ('add' == $action) {
				return '+';
			}
			return '-';
		}
		if (\in_array($moduleName, self::$modulesToCalculate['remove'])) {
			if ('add' == $action) {
				return '-';
			}
			return '+';
		}
	}

	public static function setQtyInStock(string $moduleName, array $data, int $storageId, string $action)
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
		$expression = 'CASE ';
		foreach ($qtyInStock as $id => $value) {
			$expression .= " WHEN {$db->quoteColumnName('productid')} = {$db->quoteValue($id)} THEN (qtyinstock {$operator} {$db->quoteValue($value)})";
		}
		$expression .= ' END';
		$db->createCommand()->update('vtiger_products', ['qtyinstock' => new yii\db\Expression($expression)], ['productid' => array_keys($qtyInStock)])->execute();
		// Saving the amount of product in stock.
		$referenceInfo = Vtiger_Relation_Model::getReferenceTableInfo('Products', 'IStorages');
		$relData = (new App\Db\Query())->select([$referenceInfo['rel'], 'qtyinstock'])
			->from($referenceInfo['table'])
			->where([$referenceInfo['base'] => $storageId, $referenceInfo['rel'] => array_keys($qtyInStock)])
			->createCommand()->queryAllByGroup(0);
		foreach ($qtyInStock as $id => $value) {
			if (\array_key_exists($id, $relData)) {
				$db->createCommand()->update($referenceInfo['table'], [
					'qtyinstock' => new yii\db\Expression('qtyinstock ' . $operator . ' ' . $value),
				], [$referenceInfo['base'] => $storageId, $referenceInfo['rel'] => $id])->execute();
			} else {
				$db->createCommand()->insert($referenceInfo['table'], [
					$referenceInfo['base'] => $storageId,
					$referenceInfo['rel'] => $id,
					'qtyinstock' => '+' === $operator ? $value : $operator . $value,
				])->execute();
			}
		}
		$eventHandler = new App\EventHandler();
		$eventHandler->setModuleName('IStorages');
		$eventHandler->setParams([
			'storageId' => $storageId,
			'products' => $qtyInStock,
		]);
		$eventHandler->trigger('IStoragesAfterUpdateStock');
	}
}
