<?php

/**
 * Inventory aggregation of discounts field file.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * Inventory aggregation of discounts field class.
 */
class Vtiger_DiscountAggregation_InventoryField extends Vtiger_Basic_InventoryField
{
	/** {@inheritdoc} */
	protected $type = 'DiscountAggregation';

	/** {@inheritdoc} */
	protected $defaultLabel = 'LBL_DISCOUNT_AGGREGATION';

	/** {@inheritdoc} */
	protected $defaultValue = '';

	/** {@inheritdoc} */
	protected $columnName = 'discount_aggreg';

	/** {@inheritdoc} */
	protected $dbType = [\yii\db\Schema::TYPE_TINYINT, 1];

	/** @var string[] List of available selections */
	protected $values = ['LBL_CANNOT_BE_COMBINED', 'LBL_IN_TOTAL', 'LBL_CASCADE'];

	/** {@inheritdoc} */
	protected $blocks = [0];

	/** {@inheritdoc} */
	protected $maximumLength = '127';

	/** {@inheritdoc} */
	protected $purifyType = \App\Purifier::INTEGER;

	/** {@inheritdoc} */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		if (null === $value) {
			$value = Vtiger_Inventory_Model::getInstance($this->getModuleName())->getDiscountsConfig('aggregation');
		}
		return \App\Language::translate($this->values[$value], $this->getModuleName());
	}

	/** {@inheritdoc} */
	public function getDBValue($value, ?string $name = '')
	{
		return (int) $value;
	}

	/** {@inheritdoc} */
	public function validate($value, string $columnName, bool $isUserFormat, $originalValue = null)
	{
		if (null !== $value && (!\is_int($value) || !isset($this->values[$value]))) {
			throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
		}
	}

	/**
	 * Get picklist values.
	 *
	 * @return string[]
	 */
	public function getPicklistValues(): array
	{
		return $this->values;
	}
}
