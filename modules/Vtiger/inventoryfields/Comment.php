<?php

/**
 * Inventory Comment Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 * @author    Tomasz Poradzewski <t.poradzewski@yetiforce.com>
 */
class Vtiger_Comment_InventoryField extends Vtiger_Basic_InventoryField
{
	/** {@inheritdoc} */
	protected $type = 'Comment';
	/** {@inheritdoc} */
	protected $defaultLabel = 'LBL_COMMENT';
	/** {@inheritdoc} */
	protected $colSpan = 0;
	/** {@inheritdoc} */
	protected $columnName = 'comment';
	/** {@inheritdoc} */
	protected $dbType = 'text';
	/** {@inheritdoc} */
	protected $params = ['width', 'height'];
	/** {@inheritdoc} */
	protected $onlyOne = false;
	/** {@inheritdoc} */
	protected $blocks = [2];
	/** {@inheritdoc} */
	public $isVisible = false;
	/** {@inheritdoc} */
	protected $purifyType = \App\Purifier::HTML;

	/** {@inheritdoc} */
	public function getEditTemplateName()
	{
		return 'inventoryTypes/Comment.tpl';
	}

	/**
	 * Get width.
	 *
	 * @return int
	 */
	public function getWidth(): int
	{
		return $this->getParamsConfig()['width'] ?? 100;
	}

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

	/** {@inheritdoc} */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		$conf = App\Config::module($this->getModuleName(), 'inventoryCommentIframeContent', null);
		$value = \App\Utils\Completions::decode(\App\Purifier::purifyHtml($value));
		if (!$rawText && false !== $conf) {
			return \App\Layout::truncateHtml($value, 'mini', 300);
		}
		return $rawText ? $value : \App\Layout::truncateHtml($value, 'full');
	}

	/** {@inheritdoc} */
	public function getListViewDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		$value = \App\Utils\Completions::decode(\App\Purifier::purifyHtml($value));
		return $rawText ? $value : \App\Layout::truncateHtml($value, 'mini', 50);
	}

	/** {@inheritdoc} */
	public function validate($value, string $columnName, bool $isUserFormat, $originalValue = null)
	{
		if (!\is_string($value)) {
			throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
		}
	}

	/** {@inheritdoc} */
	public function getDBValue($value, ?string $name = '')
	{
		return \App\Utils\Completions::encodeAll(\App\Purifier::decodeHtml($value));
	}

	/** {@inheritdoc} */
	public function getValue($value)
	{
		if ('' == $value) {
			$value = $this->getDefaultValue();
		}
		return \App\Utils\Completions::encode(\App\Purifier::decodeHtml($value));
	}
}
