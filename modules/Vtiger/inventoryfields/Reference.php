<?php

/**
 * Inventory Reference Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Reference_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'Reference';
	protected $defaultLabel = 'LBL_REFERENCE';
	protected $columnName = 'ref';
	protected $dbType = 'int';
	protected $params = ['modules'];
	protected $maximumLength = '-2147483648,2147483647';
	protected $purifyType = \App\Purifier::INTEGER;

	/**
	 * {@inheritdoc}
	 */
	public function getEditTemplateName()
	{
		return 'inventoryTypes/Reference.tpl';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		if (empty($value)) {
			return '';
		}
		$label = \App\Record::getLabel($value);
		$moduleName = \App\Record::getType($value);
		if ($rawText || ($value && !\App\Privilege::isPermitted($moduleName, 'DetailView', $value))) {
			return $label;
		}
		$label = App\TextParser::textTruncate($label, \AppConfig::main('href_max_length'));
		if (\App\Record::getState($value) !== 'Active') {
			$label = '<s>' . $label . '</s>';
		}
		return "<a class='modCT_$moduleName showReferenceTooltip js-popover-tooltip--record' href='index.php?module=$moduleName&view=Detail&record=$value' title='" . App\Language::translateSingularModuleName($moduleName) . "'>$label</a>";
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEditValue($value)
	{
		if (empty($value)) {
			return '';
		}
		return \App\Record::getLabel($value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function isMandatory()
	{
		$config = $this->getParamsConfig();
		return isset($config['mandatory']) ? $config['mandatory'] !== 'false' : true;
	}

	/**
	 * Function to get reference modules.
	 *
	 * @return array
	 */
	public function getReferenceModules()
	{
		$paramsDecoded = $this->getParamsConfig();
		return $paramsDecoded['modules'];
	}

	public function getReferenceModule($record)
	{
		if (!empty($record)) {
			$metadata = vtlib\Functions::getCRMRecordMetadata($record);

			return $metadata['setype'];
		}
		return '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDBValue($value, ?string $name = '')
	{
		return (int) $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, string $columnName, bool $isUserFormat)
	{
		if ((empty($value) && $this->isMandatory()) || ($value && !is_numeric($value))) {
			throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
		}
		$rangeValues = explode(',', $this->maximumLength);
		if ($rangeValues[1] < $value || $rangeValues[0] > $value) {
			throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||$value", 406);
		}
	}
}
