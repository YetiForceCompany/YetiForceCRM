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
	 * Undocumented variable.
	 *
	 * @var \Vtiger_Record_Model
	 */
	protected $parentRecordModel;

	/**
	 * Sequence.
	 *
	 * @var int
	 */
	protected $seq;

	/**
	 * Construct.
	 *
	 * @param string   $moduleName
	 * @param array    $inventory
	 * @param int      $storage
	 * @param int|null $accountId
	 */
	public function __construct(string $moduleName, array $inventory, int $storage, ?int $accountId)
	{
		$this->moduleName = $moduleName;
		$this->inventory = $inventory;
		$this->storage = $storage;
		if (!empty($accountId)) {
			$this->parentRecordModel = \Vtiger_Record_Model::getInstanceById($accountId, 'Accounts');
			$this->pricebookId = $this->parentRecordModel->get('pricebook_id');
		}

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
					if (empty($this->parentRecordModel)) {
						$availableTaxes = 'LBL_GROUP';
						$regionalTaxes = '';
					} else {
						$availableTaxes = $this->parentRecordModel->get('accounts_available_taxes');
						$regionalTaxes = $this->parentRecordModel->get('taxes');
					}
					$item['taxparam'] = \App\Json::encode(\Api\Portal\Record::getTaxParam($availableTaxes, $this->products[$inventoryKey]['taxes'], $regionalTaxes));
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
				'price' => 'unit_price',
				'purchase' => 'purchase'
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

	protected function getInventoryTaxmode(int $inventoryKey)
	{
		return 1;
	}

	/**
	 * Returns sequence.
	 *
	 * @param int $inventoryKey
	 *
	 * @return int
	 */
	protected function getInventorySeq(int $inventoryKey): int
	{
		return ++$this->seq;
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
				'subunit' => new \yii\db\Expression("''"), 'description', 'unit_price', 'purchase', 'taxes',
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
				'subunit', 'description', 'unit_price', 'purchase', 'taxes', 'quantity' => 'u_#__istorages_products.qtyinstock',
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
		$currencyId = \App\Fields\Currency::getDefault()['id'];
		foreach ($dataReader as $row) {
			$row['purchase'] = $multiCurrencyUiType->getValueForCurrency($row['purchase'], $currencyId);
			if (!$isUserPermissions) {
				$row['unit_price'] = $row['listprice'] ?? $multiCurrencyUiType->getValueForCurrency($row['unit_price'], $currencyId);
			} else {
				$row['unit_price'] = $multiCurrencyUiType->getValueForCurrency($row['unit_price'], $currencyId);
			}
			$this->products[$row['id']] = $row;
		}
		$dataReader->close();
	}
}
