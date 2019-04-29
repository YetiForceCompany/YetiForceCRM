<?php
/**
 * The file contains: SaveInventory abstract.
 *
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Adach <a.adach@yetiforce.com>
 */

namespace Api\Portal\BaseModel;

/**
 * Abstract SaveInventory.
 */
abstract class AbstractSaveInventory
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
	 * Construct.
	 *
	 * @param string $moduleName
	 * @param array  $inventory
	 */
	public function __construct(string $moduleName, array $inventory)
	{
		$this->moduleName = $moduleName;
		$this->inventory = $inventory;
	}

	/**
	 * Get inventory data.
	 *
	 * @return array
	 */
	public function getInventoryData(): array
	{
		$defaultValues = $this->getDefaultValues();
		$inventoryData = [];
		foreach ($this->inventory as $inventoryKey => $inventoryItem) {
			foreach ($defaultValues as $columnName => $defaultValue) {
				if ($this->ignore($columnName)) {
					continue;
				}
				$item[$columnName] = $this->getValue($columnName, $inventoryKey) ?? $inventoryItem[$columnName] ?? $defaultValue;
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
	 * @return void
	 */
	abstract protected function getValue(string $columnName, string $inventoryKey);

	/**
	 * Ignore columns and do not set their values.
	 *
	 * @param string $columnName
	 *
	 * @return bool
	 */
	abstract protected function ignore(string $columnName): bool;

	/**
	 * Get default values.
	 *
	 * @return array
	 */
	protected function getDefaultValues(): array
	{
		$defaultValues = [];
		foreach (\Vtiger_Inventory_Model::getInstance($this->moduleName)->getFields() as $columnName => $fieldModel) {
			$defaultValues[$columnName] = $fieldModel->getDefaultValue();
		}
		return $defaultValues;
	}
}
