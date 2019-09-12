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
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
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
	public $isVisible = false;
	protected $purifyType = \App\Purifier::HTML;

	/**
	 * Get height.
	 *
	 * @return int
	 */
	public function getHeight(): int
	{
		return $this->getParamsConfig()['height'] ?? 50;
	}

	/**
	 * Get isOpened param.
	 *
	 * @return bool
	 */
	public function isOpened(): bool
	{
		return $this->getParamsConfig()['isOpened'] ?? false;
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
