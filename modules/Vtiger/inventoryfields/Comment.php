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
	protected $params = ['width', 'height', 'isOpened'];
	/** {@inheritdoc} */
	protected $onlyOne = false;
	/** {@inheritdoc} */
	protected $blocks = [2];
	/** {@inheritdoc} */
	public $isVisible = false;
	/** {@inheritdoc} */
	protected $purifyType = \App\Purifier::HTML;

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
		$value = \App\Utils\Completions::decode(\App\Purifier::decodeHtml(\App\Purifier::purifyHtml($value)));
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
	public function getConfigFieldsData(): array
	{
		$data = parent::getConfigFieldsData();
		unset($data['colspan']);
		$data['width'] = [
			'name' => 'width',
			'label' => 'LBL_COLSPAN',
			'uitype' => 7,
			'maximumlength' => '0,100',
			'typeofdata' => 'N~M',
			'purifyType' => \App\Purifier::INTEGER,
			'tooltip' => 'LBL_MAX_WIDTH_COLUMN_INFO',
			'defaultvalue' => '100',
		];
		$data['height'] = [
			'name' => 'height',
			'label' => 'LBL_HEIGHT',
			'uitype' => 7,
			'maximumlength' => '0,1000',
			'typeofdata' => 'N~M',
			'purifyType' => \App\Purifier::INTEGER,
			'defaultvalue' => '50',
		];
		$data['isOpened'] = [
			'name' => 'isOpened',
			'label' => 'LBL_COMMENT_IS_OPENED',
			'uitype' => 56,
			'maximumlength' => '0,127',
			'typeofdata' => 'C~O',
			'tooltip' => 'LBL_COMMENT_IS_OPENED_INFO',
			'purifyType' => \App\Purifier::BOOL,
		];

		return $data;
	}
}
