<?php
/**
 * The file contains: SaveInventory class.
 *
 * @package Api
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Api\Portal;

/**
 * Class SaveInventory.
 */
class Inventory
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
	 * @var array|null
	 */
	private $fieldMapping;

	/**
	 * Storage.
	 *
	 * @var int
	 */
	protected $storage;

	/**
	 * Products.
	 *
	 * @var array
	 */
	protected $products = [];

	/**
	 * Arrays with errors.
	 *
	 * @var array
	 */
	protected $errors = [];

	/**
	 * Pricebook id.
	 *
	 * @var int|null
	 */
	protected $pricebookId;

	/**
	 * Construct.
	 *
	 * @param string   $moduleName
	 * @param array    $inventory
	 * @param int      $storage
	 * @param int|null $pricebookId
	 */
	public function __construct(string $moduleName, array $inventory, int $storage, ?int $pricebookId)
	{
		$this->moduleName = $moduleName;
		$this->inventory = $inventory;
		$this->storage = $storage;
		$this->pricebookId = $pricebookId;
		$this->getProductsByInventory();
	}

	/**
	 * Get errors.
	 *
	 * @return array
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}

	/**
	 * Validate inventory.
	 *
	 * @return bool
	 */
	public function validate(): bool
	{
		$inventoryErrors = [];
		foreach ($this->inventory as $inventoryKey => $inventoryItem) {
			if ('Products' === $this->products[$inventoryKey]['module']) {
				$quantityInStorage = $this->products[$inventoryKey]['quantity'] ?? 0.0;
				if ($quantityInStorage < (float) $inventoryItem['qty']) {
					$inventoryErrors[$inventoryKey] = ['params' => ['quantity' => $quantityInStorage]];
				}
			}
		}
		if ($inventoryErrors) {
			$this->errors['inventory'] = $inventoryErrors;
		}
		return !$inventoryErrors;
	}

	/**
	 * Get Inventory from record.
	 *
	 * @param int    $recordId
	 * @param string $moduleName
	 *
	 * @return array
	 */
	public function getInventoryFromRecord(int $recordId, string $moduleName): array
	{
		$inventoryData = \Vtiger_Inventory_Model::getInventoryDataById($recordId, $moduleName);
		foreach ($inventoryData as &$inventoryRow) {
			$inventoryRow['qty'] = $this->inventory[$inventoryRow['name']]['qty'];
		}
		return $inventoryData;
	}

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
				if ('tax' === $columnName) {
					$taxes = explode(',', $this->products[$inventoryKey]['taxes']);
					$taxes = current($taxes);
					if ($taxes) {
						$allTaxes = \Vtiger_Inventory_Model::getGlobalTaxes();
						$item['taxparam'] = \App\Json::encode([
							'aggregationType' => 'individual',
							'individualTax' => $allTaxes[$taxes]['value']
						]);
					}
					continue;
				}
				if (\in_array($fieldModel->getColumnName(), ['total', 'margin', 'marginp', 'net', 'gross', 'discount'])) {
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
	 * @param int    $inventoryKey
	 *
	 * @return mixed
	 */
	protected function getValue(string $columnName, int $inventoryKey)
	{
		$fromRow = $this->getFieldMapping();
		if (!isset($fromRow[$columnName])) {
			$method = 'getInventory' . ucfirst($columnName);
			return \method_exists($this, $method) ? $this->{$method}($inventoryKey) : null;
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
	 * @param int $inventoryKey
	 *
	 * @return int
	 */
	protected function getInventoryCurrency(int $inventoryKey): int
	{
		return (int) \App\Fields\Currency::getDefault()['id'];
	}

	protected function getInventoryPurchase(int $inventoryKey)
	{
		return $this->products[$inventoryKey]['unit_price'];
	}

	protected function getInventoryTaxmode(int $inventoryKey)
	{
		return 1;
	}

	/**
	 * Get products by inventory.
	 */
	private function getProductsByInventory()
	{
		$isUserPermissions = \Api\Portal\Privilege::USER_PERMISSIONS === $this->permissionType;
		$this->products = [];
		$crmIds = array_keys($this->inventory);
		$queryService = (new \App\Db\Query())
			->select([
				'module' => new \yii\db\Expression("'Service'"), 'id' => 'serviceid', 'service_usageunit',
				'subunit' => new \yii\db\Expression("''"), 'currency_id', 'description', 'unit_price', 'taxes',
				'quantity' => new \yii\db\Expression('0'),
				'vtiger_pricebookproductrel.listprice'
			])
			->from('vtiger_service')
			->innerJoin('vtiger_crmentity', 'vtiger_service.serviceid = vtiger_crmentity.crmid')
			->leftJoin('vtiger_pricebookproductrel', "vtiger_pricebookproductrel.pricebookid={$this->pricebookId} AND vtiger_pricebookproductrel.productid = vtiger_service.serviceid")
			->where(['vtiger_crmentity.deleted' => 0])
			->andWhere(['discontinued' => 1])
			->andWhere(['vtiger_service.serviceid' => $crmIds]);
		$dataReader = (new \App\Db\Query())
			->select([
				'module' => new \yii\db\Expression("'Products'"), 'id' => 'vtiger_products.productid', 'usageunit',
				'subunit', 'currency_id', 'description', 'unit_price', 'taxes', 'quantity' => 'u_#__istorages_products.qtyinstock',
				'vtiger_pricebookproductrel.listprice'
			])
			->from('vtiger_products')
			->innerJoin('vtiger_crmentity', 'vtiger_products.productid = vtiger_crmentity.crmid')
			->leftJoin('u_#__istorages_products', "u_#__istorages_products.crmid={$this->storage} AND u_#__istorages_products.relcrmid = vtiger_products.productid")
			->leftJoin('vtiger_pricebookproductrel', "vtiger_pricebookproductrel.pricebookid={$this->pricebookId} AND vtiger_pricebookproductrel.productid = vtiger_products.productid")
			->where(['vtiger_crmentity.deleted' => 0])
			->andWhere(['discontinued' => 1])
			->andWhere(['vtiger_products.productid' => $crmIds])
			->union($queryService, true)
			->createCommand()->query();
		$multiCurrencyUiType = new \Vtiger_MultiCurrency_UIType();
		foreach ($dataReader as $row) {
			if (!$isUserPermissions) {
				$row['unit_price'] = $row['listprice'] ?? $multiCurrencyUiType->getValueForCurrency($row['unit_price'], \App\Fields\Currency::getDefault()['id']);
			} else {
				$row['unit_price'] = $multiCurrencyUiType->getValueForCurrency($row['unit_price'], \App\Fields\Currency::getDefault()['id']);
			}
			$this->products[$row['id']] = $row;
		}
		$dataReader->close();
	}
}
