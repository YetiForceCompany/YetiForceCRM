<?php
/**
 * The file contains: SaveInventory class.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Api\Portal\SSingleOrdersModel;

/**
 * Class SaveInventory.
 */
class SaveInventory extends \Api\Portal\BaseModel\AbstractSaveInventory
{
	/**
	 * Products.
	 *
	 * @var array
	 */
	private $products = [];

	/**
	 * {@inheritdoc}
	 */
	public function __construct(string $moduleName, array $inventory)
	{
		parent::__construct($moduleName, $inventory);
		$this->getProductsByInventory();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getDefaultValues(): array
	{
		return parent::getDefaultValues() + ['taxparam' => ''];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getValue(string $columnName, string $inventoryKey)
	{
		$fromRow = [
			'name' => 'id',
			'unit' => 'usageunit',
			'subunit' => 'subunit',
			'currency' => 'currency_id',
			'comment1' => 'description',
			'price' => 'unit_price'
		];
		if (!isset($fromRow[$columnName])) {
			$method = 'getInventory' . ucfirst($columnName);
			return \method_exists($this, $method) ? $this->{$method}() : null;
		}
		$key = $fromRow[$columnName];
		return $this->products[$inventoryKey][$key];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function ignore(string $columnName): bool
	{
		return \in_array($columnName, ['total', 'margin', 'marginp', 'net', 'gross', 'tax']);
	}

	/**
	 * Get value for taxmode.
	 *
	 * @return int
	 */
	protected function getInventoryTaxmode(): int
	{
		return \Vtiger_Inventory_Model::getTaxesConfig()['active'];
	}

	/**
	 * Get value for taxparam.
	 *
	 * @return string
	 */
	protected function getInventoryTaxparam(): string
	{
		$activeTax = \Vtiger_Inventory_Model::getGlobalTaxes()[$this->getInventoryTaxmode()];
		return '{"aggregationType":"global","globalTax":' . $activeTax['value'] . '}';
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
			->select(['id' => 'serviceid', 'service_usageunit', 'subunit' => new \yii\db\Expression("''"), 'currency_id', 'description', 'unit_price'])
			->from('vtiger_service')
			->innerJoin('vtiger_crmentity', 'vtiger_service.serviceid = vtiger_crmentity.crmid')
			->where(['vtiger_crmentity.deleted' => 0])
			->andWhere(['discontinued' => 1])
			->andWhere(['vtiger_service.serviceid' => $crmIds]);
		$dataReader = (new \App\Db\Query())
			->select(['id' => 'productid', 'usageunit', 'subunit', 'currency_id', 'description', 'unit_price'])
			->from('vtiger_products')
			->innerJoin('vtiger_crmentity', 'vtiger_products.productid = vtiger_crmentity.crmid')
			->where(['vtiger_crmentity.deleted' => 0])
			->andWhere(['discontinued' => 1])
			->andWhere(['vtiger_products.productid' => $crmIds])
			->union($queryService, true)
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$this->products[$row['id']] = $row;
		}
		$dataReader->close();
	}
}
