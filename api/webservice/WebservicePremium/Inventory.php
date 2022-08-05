<?php
/**
 * The file contains: SaveInventory class.
 *
 * @package API
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\WebservicePremium;

/**
 * Class SaveInventory.
 */
class Inventory
{
	/** @var string Module name. */
	protected $moduleName;

	/** @var array Inventory items passed from request. */
	protected $inventory;

	/** @var array|null Field mapping. */
	private $fieldMapping;

	/** @var int Storage ID */
	protected $storage;

	/** @var array Products */
	protected $products = [];

	/** @var array Arrays with errors. */
	protected $errors = [];

	/** @var int|null Price book id. */
	protected $priceBookId;

	/** @var \Vtiger_Record_Model Parent record model */
	protected $parentRecordModel;

	/** @var int Sequence. */
	protected $seq;

	/** @var int Permission type. */
	protected $permissionType;

	/**
	 * Construct.
	 *
	 * @param string               $moduleName
	 * @param \Api\Core\BaseAction $actionModel
	 */
	public function __construct(string $moduleName, \Api\Core\BaseAction $actionModel)
	{
		$this->moduleName = $moduleName;
		$this->inventory = $actionModel->controller->request->getArray('inventory');
		$this->storage = $actionModel->getUserStorageId();
		$this->permissionType = $actionModel->getPermissionType();
		$accountId = $actionModel->getParentCrmId();
		if (!empty($accountId)) {
			$this->parentRecordModel = \Vtiger_Record_Model::getInstanceById($accountId, 'Accounts');
			$this->priceBookId = $this->parentRecordModel->get('pricebook_id');
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
					$item['taxparam'] = \App\Json::encode(\Api\WebservicePremium\Record::getTaxParam($availableTaxes, $this->products[$inventoryKey]['taxes'], $regionalTaxes));
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
		$isUserPermissions = \Api\WebservicePremium\Privilege::USER_PERMISSIONS === $this->permissionType;
		$this->products = [];
		$crmIds = array_keys($this->inventory);
		$queryService = (new \App\Db\Query())
			->select([
				'module' => new \yii\db\Expression("'Service'"), 'id' => 'serviceid', 'service_usageunit',
				'subunit' => new \yii\db\Expression("''"), 'description', 'unit_price', 'purchase', 'taxes',
			])
			->from('vtiger_service')
			->innerJoin('vtiger_crmentity', 'vtiger_service.serviceid = vtiger_crmentity.crmid')
			->where(['vtiger_crmentity.deleted' => 0])
			->andWhere(['discontinued' => 1])
			->andWhere(['vtiger_service.serviceid' => $crmIds]);
		if (!empty($this->storage)) {
			$queryService->addSelect(['quantity' => new \yii\db\Expression('0')]);
		}
		if (!$isUserPermissions && !empty($this->priceBookId)) {
			$queryService->addSelect(['vtiger_pricebookproductrel.listprice']);
			$queryService->leftJoin('vtiger_pricebookproductrel', "vtiger_pricebookproductrel.pricebookid={$this->priceBookId} AND vtiger_pricebookproductrel.productid = vtiger_service.serviceid");
		}
		$query = (new \App\Db\Query())
			->select([
				'module' => new \yii\db\Expression("'Products'"), 'id' => 'vtiger_products.productid', 'usageunit',
				'subunit', 'description', 'unit_price', 'purchase', 'taxes'
			])
			->from('vtiger_products')
			->innerJoin('vtiger_crmentity', 'vtiger_products.productid = vtiger_crmentity.crmid')

			->where(['vtiger_crmentity.deleted' => 0])
			->andWhere(['discontinued' => 1])
			->andWhere(['vtiger_products.productid' => $crmIds])
			->union($queryService, true);
		if (!empty($this->storage)) {
			$query->addSelect(['quantity' => 'u_#__istorages_products.qtyinstock']);
			$query->leftJoin('u_#__istorages_products', "u_#__istorages_products.crmid={$this->storage} AND u_#__istorages_products.relcrmid = vtiger_products.productid");
		}
		if (!$isUserPermissions && !empty($this->priceBookId)) {
			$query->addSelect(['vtiger_pricebookproductrel.listprice']);
			$query->leftJoin('vtiger_pricebookproductrel', "vtiger_pricebookproductrel.pricebookid={$this->priceBookId} AND vtiger_pricebookproductrel.productid = vtiger_products.productid");
		}
		$dataReader = $query->createCommand()->query();
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
