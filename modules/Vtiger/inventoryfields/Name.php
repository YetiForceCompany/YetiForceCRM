<?php

/**
 * Inventory Name Field Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Vtiger_Name_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $name = 'Name';
	protected $defaultLabel = 'LBL_ITEM_NAME';
	protected $columnName = 'name';
	protected $dbType = 'int DEFAULT 0';
	protected $params = ['modules', 'limit'];
	protected $colSpan = 30;
	protected $maximumLength = '-2147483648,2147483647';

	/**
	 * Getting value to display.
	 *
	 * @param type $value
	 *
	 * @return type
	 */
	public function getDisplayValue($value, $rawText = false)
	{
		if (empty($value)) {
			return '';
		}
		$name = \App\Record::getLabel($value);
		$moduleName = \App\Record::getType($value);
		if ($rawText || ($value && !\App\Privilege::isPermitted($moduleName, 'DetailView', $value))) {
			return $name;
		}
		$name = App\TextParser::textTruncate($name, \AppConfig::main('href_max_length'));
		if (\App\Record::getState($value) !== 'Active') {
			$name = '<s>' . $name . '</s>';
		}

		return "<a class='modCT_$moduleName showReferenceTooltip' href='index.php?module=$moduleName&view=Detail&record=$value' title='" . App\Language::translateSingularModuleName($moduleName) . "'>$name</a>";
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEditValue($value)
	{
		return \App\Record::getLabel($value);
	}

	/**
	 * Getting value to display.
	 *
	 * @return array
	 */
	public function limitValues()
	{
		return [
			['id' => 0, 'name' => 'LBL_NO'],
			['id' => 1, 'name' => 'LBL_YES'],
		];
	}

	public function getConfig()
	{
		return \App\Json::decode($this->get('params'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getValueFromRequest(&$insertData, \App\Request $request, $i)
	{
		$column = $this->getColumnName();
		if (empty($column) || $column === '-' || !$request->has($column . $i)) {
			return false;
		}
		$value = $request->getInteger($column . $i);
		$this->validate($value, $column, true);
		$insertData[$column] = $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, $columnName, $isUserFormat = false)
	{
		if (!is_numeric($value)) {
			throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
		}
		$rangeValues = explode(',', $this->maximumLength);
		if ($rangeValues[1] < $value || $rangeValues[0] > $value) {
			throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||$value", 406);
		}
	}
}
