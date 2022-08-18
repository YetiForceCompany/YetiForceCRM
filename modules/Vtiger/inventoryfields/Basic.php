<?php

/**
 * Inventory Basic Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Basic_InventoryField extends \App\Base
{
	/**
	 * Field visible everywhere.
	 */
	private const FIELD_VISIBLE_EVERYWHERE = 0;
	/**
	 * Field visible in detail view.
	 */
	private const FIELD_VISIBLE_IN_DETAIL = 2;
	/**
	 * Field hidden.
	 */
	private const FIELD_HIDDEN = 5;
	/**
	 * Field read-only.
	 */
	private const FIELD_READONLY = 10;

	protected $columnName = '';
	protected $moduleName = '';

	protected $type;
	protected $colSpan = 10;
	protected $defaultValue = '';
	protected $params = [];
	protected $dbType = 'string';
	protected $customColumn = [];
	protected $summationValue = false;
	protected $onlyOne = true;
	protected $displayType = self::FIELD_VISIBLE_EVERYWHERE;
	protected $displayTypeBase = [
		'LBL_DISPLAYTYPE_ALL' => self::FIELD_VISIBLE_EVERYWHERE,
		'LBL_DISPLAYTYPE_ONLY_DETAIL' => self::FIELD_VISIBLE_IN_DETAIL,
		'LBL_DISPLAYTYPE_HIDDEN' => self::FIELD_HIDDEN,
		'LBL_DISPLAYTYPE_READONLY' => self::FIELD_READONLY
	];
	protected $blocks = [1];
	protected $fieldDataType = 'inventory';
	protected $maximumLength = 255;
	protected $defaultLabel = '';
	protected $purifyType = '';
	protected $customPurifyType = [];

	/**
	 * Gets inventory field instance.
	 *
	 * @param string      $moduleName
	 * @param string|null $type
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return self
	 */
	public static function getInstance(string $moduleName, ?string $type = 'Basic')
	{
		$cacheName = "$moduleName:$type";
		if (\App\Cache::has(__METHOD__, $cacheName)) {
			$instance = \App\Cache::get(__METHOD__, $cacheName);
		} else {
			$className = Vtiger_Loader::getComponentClassName('InventoryField', $type, $moduleName);
			$instance = new $className();
			$instance->setModuleName($moduleName);
			\App\Cache::save(__METHOD__, $cacheName, $instance);
		}
		return clone $instance;
	}

	/**
	 * Function returns module name.
	 *
	 * @return string
	 */
	public function getModuleName(): string
	{
		return $this->moduleName;
	}

	/**
	 * Sets module name.
	 *
	 * @param string $moduleName
	 *
	 * @return \Vtiger_Basic_InventoryField
	 */
	public function setModuleName(string $moduleName): self
	{
		$this->moduleName = $moduleName;
		return $this;
	}

	/**
	 * Getting onlyOne field.
	 *
	 * @return bool
	 */
	public function isOnlyOne()
	{
		return $this->onlyOne;
	}

	public function getBlocks()
	{
		return $this->blocks;
	}

	/**
	 * Getting database-type of field.
	 *
	 * @return string dbType
	 */
	public function getDBType()
	{
		return $this->dbType;
	}

	/**
	 * Gets value for save.
	 *
	 * @param array  $item
	 * @param bool   $userFormat
	 * @param string $column
	 *
	 * @return mixed
	 */
	public function getValueForSave(array $item, bool $userFormat = false, string $column = null)
	{
		if (null === $column) {
			$column = $this->getColumnName();
		}
		$value = $item[$column] ?? null;
		return $userFormat ? $this->getDBValue($value) : $value;
	}

	/**
	 * Getting all params values.
	 *
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}

	public function getParamsConfig()
	{
		return \App\Json::decode($this->get('params'));
	}

	/**
	 * Getting all values display Type.
	 *
	 * @return array
	 */
	public function displayTypeBase()
	{
		return $this->displayTypeBase;
	}

	/**
	 * Gets display type.
	 *
	 * @return int
	 */
	public function getDisplayType(): int
	{
		return $this->has('displayType') ? $this->get('displayType') : $this->displayType;
	}

	public function getColSpan()
	{
		return $this->has('colSpan') ? $this->get('colSpan') : $this->colSpan;
	}

	public function getRangeValues()
	{
		return $this->maximumLength;
	}

	/**
	 * Get template name for edit.
	 *
	 * @return string
	 */
	public function getEditTemplateName()
	{
		return 'inventoryTypes/Base.tpl';
	}

	/**
	 * Getting template name.
	 *
	 * @param mixed $view
	 * @param mixed $moduleName
	 *
	 * @return string templateName
	 */
	public function getTemplateName($view, $moduleName)
	{
		$tpl = $view . $this->type . '.tpl';
		$filename = 'layouts' . DIRECTORY_SEPARATOR . \App\Layout::getActiveLayout() . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'inventoryfields' . DIRECTORY_SEPARATOR . $tpl;
		if (is_file($filename)) {
			return $tpl;
		}
		$filename = 'layouts' . DIRECTORY_SEPARATOR . \App\Layout::getActiveLayout() . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Vtiger' . DIRECTORY_SEPARATOR . 'inventoryfields' . DIRECTORY_SEPARATOR . $tpl;
		if (is_file($filename)) {
			return $tpl;
		}
		$filename = 'layouts' . DIRECTORY_SEPARATOR . Vtiger_Viewer::getDefaultLayoutName() . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'inventoryfields' . DIRECTORY_SEPARATOR . $tpl;
		if (is_file($filename)) {
			return $tpl;
		}
		$filename = 'layouts' . DIRECTORY_SEPARATOR . Vtiger_Viewer::getDefaultLayoutName() . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Vtiger' . DIRECTORY_SEPARATOR . 'inventoryfields' . DIRECTORY_SEPARATOR . $tpl;
		if (is_file($filename)) {
			return $tpl;
		}
		return $view . 'Base' . '.tpl';
	}

	/**
	 * Getting default label.
	 *
	 * @return string defaultLabel
	 */
	public function getDefaultLabel()
	{
		return $this->defaultLabel;
	}

	/**
	 * Getting field type.
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Getting column name.
	 *
	 * @return string columnName
	 */
	public function getColumnName()
	{
		return $this->has('columnName') ? $this->get('columnName') : $this->columnName;
	}

	/**
	 * Getting column name.
	 *
	 * @return string[] customColumn
	 */
	public function getCustomColumn()
	{
		return $this->customColumn;
	}

	public function isSummary()
	{
		return $this->summationValue;
	}

	public function getDefaultValue()
	{
		return $this->has('defaultValue') ? $this->get('defaultValue') : $this->defaultValue;
	}

	/**
	 * Getting value to display.
	 *
	 * @param mixed $value
	 * @param array $rowData
	 * @param bool  $rawText
	 *
	 * @return string
	 */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		return \App\Purifier::encodeHtml($value);
	}

	/**
	 * Function to get the list value in display view.
	 *
	 * @param mixed $value
	 * @param array $rowData
	 * @param bool  $rawText
	 *
	 * @return mixed
	 */
	public function getListViewDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		return $this->getDisplayValue($value, $rowData, $rawText);
	}

	/**
	 * Getting value to display.
	 *
	 * @param mixed $value
	 *
	 * @return string
	 */
	public function getEditValue($value)
	{
		return $this->getDisplayValue($value);
	}

	/**
	 * Getting value.
	 *
	 * @param type $value
	 *
	 * @return string
	 */
	public function getValue($value)
	{
		if ('' == $value) {
			$value = $this->getDefaultValue();
		}
		return $value;
	}

	/**
	 * Function to check if the current field is mandatory or not.
	 *
	 * @return bool
	 */
	public function isMandatory()
	{
		return true;
	}

	/**
	 * Function to check whether the current field is visible.
	 *
	 * @return bool
	 */
	public function isVisible()
	{
		return self::FIELD_HIDDEN !== $this->get('displayType');
	}

	/**
	 * Function to check if field is visible in detail view.
	 *
	 * @return bool
	 */
	public function isVisibleInDetail()
	{
		return \in_array($this->get('displayType'), [self::FIELD_VISIBLE_EVERYWHERE, self::FIELD_READONLY, self::FIELD_VISIBLE_IN_DETAIL]);
	}

	/**
	 * Function to check whether the current field is editable.
	 *
	 * @return bool
	 */
	public function isEditable(): bool
	{
		return \in_array($this->get('displayType'), [self::FIELD_VISIBLE_EVERYWHERE, self::FIELD_READONLY]);
	}

	/**
	 * Function checks if the field is read-only.
	 *
	 * @return bool
	 */
	public function isReadOnly()
	{
		return self::FIELD_READONLY === $this->get('displayType');
	}

	/**
	 * Getting value to display.
	 *
	 * @return array
	 */
	public function modulesValues()
	{
		$modules = Vtiger_Module_Model::getAll([0], [], true);
		foreach ($modules as $module) {
			$modulesNames[] = ['module' => $module->getName(), 'name' => $module->getName(), 'id' => $module->getName()];
		}
		return $modulesNames;
	}

	public function getSummaryValuesFromData($data)
	{
		$sum = 0;
		if (\is_array($data)) {
			foreach ($data as $row) {
				$sum += $row[$this->getColumnName()];
			}
		}
		return $sum;
	}

	/**
	 * Gets relation field.
	 *
	 * @param string $related
	 *
	 * @throws \App\Exceptions\AppException
	 *
	 * @return bool|\Vtiger_Field_Model
	 */
	public function getMapDetail(string $related)
	{
		$inventory = Vtiger_Inventory_Model::getInstance($this->getModuleName());
		$fields = $inventory->getAutoCompleteFields();
		$field = false;
		if ($mapDetail = $fields[$related][$this->getColumnName()] ?? false) {
			$moduleModel = Vtiger_Module_Model::getInstance($related);
			$field = Vtiger_Field_Model::getInstance($mapDetail['field'], $moduleModel);
		}
		return $field;
	}

	public function getFieldDataType()
	{
		return $this->fieldDataType;
	}

	/**
	 * Gets database value.
	 *
	 * @param mixed       $value
	 * @param string|null $name
	 *
	 * @return mixed
	 */
	public function getDBValue($value, ?string $name = '')
	{
		return $value;
	}

	/**
	 * Verification of data.
	 *
	 * @param mixed  $value
	 * @param string $columnName
	 * @param bool   $isUserFormat
	 * @param mixed  $originalValue
	 *
	 * @throws \App\Exceptions\Security
	 */
	public function validate($value, string $columnName, bool $isUserFormat, $originalValue = null)
	{
		if (!is_numeric($value) && (\is_string($value) && $value !== strip_tags($value))) {
			throw new \App\Exceptions\Security('ERR_ILLEGAL_FIELD_VALUE||' . ($columnName ?? $this->getColumnName()) . '||' . $this->getModuleName() . '||' . $value, 406);
		}
		if (App\TextUtils::getTextLength($value) > $this->maximumLength) {
			throw new \App\Exceptions\Security('ERR_VALUE_IS_TOO_LONG||' . $columnName ?? $this->getColumnName() . '||' . $this->getModuleName() . '||' . $value, 406);
		}
	}

	/**
	 * Sets default data config.
	 */
	public function setDefaultDataConfig()
	{
		$this->set('columnName', $this->columnName)
			->set('label', $this->defaultLabel)
			->set('presence', 0)
			->set('defaultValue', $this->defaultValue)
			->set('displayType', $this->displayType)
			->set('invtype', $this->type)
			->set('colSpan', $this->colSpan);
	}

	/**
	 * Field required to make an entry.
	 *
	 * @return bool
	 */
	public function isRequired()
	{
		return false;
	}

	/**
	 * Sets value data.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 * @param array                $item
	 * @param bool                 $userFormat
	 *
	 * @throws \App\Exceptions\AppException
	 * @throws \App\Exceptions\Security
	 */
	public function setValueToRecord(Vtiger_Record_Model $recordModel, array $item, bool $userFormat)
	{
		$column = $this->getColumnName();
		$baseValue = $item[$column] ?? null;
		$value = $this->getValueForSave($item, $userFormat, $column);
		if ($userFormat && $baseValue) {
			$baseValue = $this->getDBValue($baseValue, $column);
		}

		$this->validate($value, $column, false, $baseValue);
		$recordModel->setInventoryItemPart($item['id'], $column, $value);
		if ($customColumn = $this->getCustomColumn()) {
			foreach (array_keys($customColumn) as $column) {
				$value = $this->getValueForSave($item, $userFormat, $column);
				$this->validate($value, $column, false);
				$recordModel->setInventoryItemPart($item['id'], $column, $value);
			}
		}
	}

	/**
	 * Gets purify type.
	 *
	 * @return array
	 */
	public function getPurifyType()
	{
		return [$this->getColumnName() => $this->purifyType] + $this->customPurifyType;
	}

	/**
	 * Get information about field.
	 *
	 * @return array
	 */
	public function getFieldInfo(): array
	{
		return [
			'maximumlength' => $this->maximumLength
		];
	}
}
