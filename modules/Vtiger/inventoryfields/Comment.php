<?php

/**
 * Inventory Comment Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Comment_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'Comment';
	protected $defaultLabel = 'LBL_COMMENT';
	protected $colSpan = 0;
	protected $columnName = 'comment';
	protected $dbType = 'text';
	protected $onlyOne = false;
	protected $blocks = [2];
	public $height = 50;
	public $isVisible = false;
	protected $purifyType = \App\Purifier::HTML;

	/**
	 * {@inheritdoc}
	 */
	public function setFieldModuleConfig(string $moduleName)
	{
		$this->height = App\Config::module($moduleName, 'INVENTORY_COMMENTS_HEIGHT') ?? $this->height;
		$this->isVisible = App\Config::module($moduleName, 'INVENTORY_IS_VISIBLE') ?? $this->isVisible;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		return \App\Utils\Completions::decode(\App\Purifier::purifyHtml($value));
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, string $columnName, bool $isUserFormat, $originalValue = null)
	{
		if (!\is_string($value)) {
			throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDBValue($value, ?string $name = '')
	{
		return \App\Utils\Completions::encodeAll(\App\Purifier::decodeHtml($value));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getValue($value)
	{
		if ('' == $value) {
			$value = $this->getDefaultValue();
		}
		return \App\Utils\Completions::encode(\App\Purifier::decodeHtml($value));
	}
}
