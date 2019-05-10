<?php
/**
 * The file contains: SaveInventory class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Api\Portal\BaseModel;

/**
 * Class SaveInventory.
 */
class SaveInventory
{
	/**
	 * Module name.
	 *
	 * @var string
	 */
	protected $moduleName;

	/**
	 * Inventory items passed from request.
	 *
	 * @var array
	 */
	protected $inventory;

	/**
	 * Field mapping.
	 *
	 * @var null|array
	 */
	private $fieldMapping;

	/**
	 * Construct.
	 *
	 * @param string $moduleName
	 * @param array  $inventory
	 */
	public function __construct(string $moduleName, array $inventory)
	{
		$this->moduleName = $moduleName;
		$this->inventory = $inventory;
		$this->getProductsByInventory();
	}

	/**
	 * Products.
	 *
	 * @var array
	 */
	private $products = [];

	/**
	 * Get inventory data.
	 *
	 * @return array
	 */
	public function getInventoryData(): array
	{
		$inventoryData = [];
		foreach ($this->inventory as $inventoryKey => $inventoryItem) {
			foreach (\Vtiger_Inventory_Model::getInstance($this->moduleName)->getFields() as $columnName => $fieldModel) {
				if ($fieldModel->getIsAutomaticValue()) {
					continue;
				}
				$item[$columnName] = $this->getValue($columnName, $inventoryKey) ?? $inventoryItem[$columnName] ?? $fieldModel->getDefaultValue();
			}
			$inventoryData[] = $item;
		}
		return $inventoryData;
	}

	/**
	 * Get the value for the column. Return null if it does not apply to this column.
	 *
	 * @param string $columnName
	 * @param string $inventoryKey
	 *
	 * @return mixed
	 */
	protected function getValue(string $columnName, string $inventoryKey)
	{
		$fromRow = $this->getFieldMapping();
		if (!isset($fromRow[$columnName])) {
			$method = 'getInventory' . ucfirst($columnName);
			return \method_exists($this, $method) ? $this->{$method}() : null;
		}
		return $this->products[$inventoryKey][$fromRow[$columnName]];
	}

	/**
	 * Get field mapping.
	 *
	 * @return array
	 */
	protected function getFieldMapping(): array
	{
		if (empty($this->fieldMapping)) {
			$this->fieldMapping = [
				'name' => 'id',
				'comment1' => 'description',
				'price' => 'unit_price'
			];
			foreach ((\Vtiger_Inventory_Model::getInstance($this->moduleName)->getAutoCompleteFields()['Products'] ?? []) as $row) {
				$this->fieldMapping[$row['tofield']] = $row['field'];
			}
		}
		return $this->fieldMapping;
	}

	/**
	 * Get currency.
	 *
	 * @return int
	 */
	protected function getInventoryCurrency(): int
	{
		return (int) \App\Fields\Currency::getDefault()['id'];
	}

	/**
	 * Get products by inventory.
	 *
	 * @return void
	 */
	private function getProductsByInventory()
	{
		$this->products = [];
		$crmIds = array_keys($this->inventory);
		$queryService = (new \App\Db\Query())
			->select([
				'module' => new \yii\db\Expression("'Service'"), 'id' => 'serviceid', 'service_usageunit',
				'subunit' => new \yii\db\Expression("''"), 'currency_id', 'description', 'unit_price'
			])
			->from('vtiger_service')
			->innerJoin('vtiger_crmentity', 'vtiger_service.serviceid = vtiger_crmentity.crmid')
			->where(['vtiger_crmentity.deleted' => 0])
			->andWhere(['discontinued' => 1])
			->andWhere(['vtiger_service.serviceid' => $crmIds]);
		$dataReader = (new \App\Db\Query())
			->select([
				'module' => new \yii\db\Expression("'Products'"), 'id' => 'productid', 'usageunit',
				'subunit', 'currency_id', 'description', 'unit_price'
			])
			->from('vtiger_products')
			->innerJoin('vtiger_crmentity', 'vtiger_products.productid = vtiger_crmentity.crmid')
			->where(['vtiger_crmentity.deleted' => 0])
			->andWhere(['discontinued' => 1])
			->andWhere(['vtiger_products.productid' => $crmIds])
			->union($queryService, true)
			->createCommand()->query();
		foreach ($dataReader as $row) {
			$this->products[$row['id']] = $row;
		}
		$dataReader->close();
	}
}
