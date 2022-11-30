<?php

/**
 * Inventory DiscountMode Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_DiscountMode_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'DiscountMode';
	protected $defaultLabel = 'LBL_DISCOUNT_MODE';
	protected $defaultValue = '0';
	protected $columnName = 'discountmode';
	protected $dbType = 'smallint(1) DEFAULT 0';
	protected $modes = [
		\Vtiger_Inventory_Model::DISCOUT_MODE_GLOBAL => 'LBL_INV_DISCOUNT_MODE_GLOBAL',
		\Vtiger_Inventory_Model::DISCOUT_MODE_INDIVIDUAL => 'LBL_INDIVIDUAL',
		\Vtiger_Inventory_Model::DISCOUT_MODE_GROUP => 'LBL_INV_DISCOUNT_MODE_GROUP'
	];
	protected $blocks = [0];
	protected $maximumLength = '-32768,32767';
	protected $purifyType = \App\Purifier::INTEGER;

	/** {@inheritdoc} */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		return '' !== $value && null !== $value ? \App\Language::translate($this->modes[$value], $this->getModuleName()) : '';
	}

	/** {@inheritdoc} */
	public function getDBValue($value, ?string $name = '')
	{
		return (int) $value;
	}

	/** {@inheritdoc} */
	public function validate($value, string $columnName, bool $isUserFormat, $originalValue = null)
	{
		if (!is_numeric($value) || !isset($this->modes[$value])) {
			throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
		}
	}

	/** {@inheritdoc} */
	public function getEditValue(array $itemData, string $column = '')
	{
		$value = parent::getEditValue($itemData, $column);
		return is_numeric($value) ? (int) $value : Vtiger_Inventory_Model::getDiscountsConfig('default_mode');
	}

	/** {@inheritdoc} */
	public function compare($value, $prevValue, string $column): bool
	{
		return (int) $value === (int) $prevValue;
	}

	/** {@inheritdoc} */
	public function getConfigFieldsData(): array
	{
		$data = parent::getConfigFieldsData();
		$data['defaultvalue'] = [
			'name' => 'defaultvalue',
			'label' => 'LBL_INV_DEFAULT_VALUE',
			'uitype' => 16,
			'maximumlength' => '1',
			'typeofdata' => 'V~M',
			'purifyType' => \App\Purifier::INTEGER,
			'defaultvalue' => \Vtiger_Inventory_Model::DISCOUT_MODE_INDIVIDUAL,
			'picklistValues' => array_map(fn ($label) => \App\Language::translate($label, $this->getModuleName()), $this->modes)
		];

		return $data;
	}

	/**
	 * Get available modes.
	 *
	 * @return array
	 */
	public function getModes(): array
	{
		return $this->modes;
	}
}
