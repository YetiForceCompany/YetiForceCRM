<?php

/**
 * Inventory Name Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Name_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'Name';
	protected $defaultLabel = 'LBL_ITEM_NAME';
	protected $columnName = 'name';
	protected $dbType = 'int DEFAULT 0';
	protected $params = ['modules', 'limit', 'mandatory'];
	protected $colSpan = 30;
	protected $maximumLength = '-2147483648,2147483647';
	protected $purifyType = \App\Purifier::INTEGER;

	/** {@inheritdoc} */
	public function getEditTemplateName()
	{
		return 'inventoryTypes/Name.tpl';
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		if (empty($value)) {
			return '';
		}
		if (!($referenceModule = $this->getReferenceModule($value))) {
			return '<i class="color-red-500" title="' . \App\Purifier::encodeHtml($value) . '">' . \App\Language::translate('LBL_RECORD_DOES_NOT_EXIST', 'ModTracker') . '</i>';
		}
		$referenceModuleName = $referenceModule->getName();
		if ('Users' === $referenceModuleName || 'Groups' === $referenceModuleName) {
			return \App\Fields\Owner::getLabel($value);
		}
		if ($rawText) {
			return \App\Record::getLabel($value, $rawText);
		}
		return "<span class=\"yfm-{$referenceModuleName} mr-1\"></span>" . \App\Record::getHtmlLink($value, $referenceModuleName, \App\Config::main('href_max_length'));
	}

	/** {@inheritdoc} */
	public function getEditValue($value)
	{
		return \App\Record::getLabel($value);
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value.
	 *
	 * @param mixed $record
	 *
	 * @return Vtiger_Module_Model|null
	 */
	public function getReferenceModule($record): ?Vtiger_Module_Model
	{
		if (!empty($record)) {
			$referenceModuleList = $this->getParamsConfig()['modules'] ?? [];
			$referenceModuleList = !\is_array($referenceModuleList) ? [$referenceModuleList] : $referenceModuleList;
			$referenceEntityType = vtlib\Functions::getCRMRecordMetadata($record)['setype'] ?? '';
			if (!empty($referenceModuleList) && \in_array($referenceEntityType, $referenceModuleList)) {
				return Vtiger_Module_Model::getInstance($referenceEntityType);
			}
			if (!empty($referenceModuleList) && \in_array('Users', $referenceModuleList)) {
				return Vtiger_Module_Model::getInstance('Users');
			}
		}
		return null;
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

	/**
	 * Getting value to display.
	 *
	 * @return array
	 */
	public function mandatoryValues()
	{
		return [
			['id' => 'true', 'name' => 'LBL_YES'],
			['id' => 'false', 'name' => 'LBL_NO'],
		];
	}

	/** {@inheritdoc} */
	public function isMandatory()
	{
		return true;
	}

	/** {@inheritdoc} */
	public function getDBValue($value, ?string $name = '')
	{
		return (int) $value;
	}

	/** {@inheritdoc} */
	public function validate($value, string $columnName, bool $isUserFormat, $originalValue = null)
	{
		if ((empty($value) && $this->isMandatory()) || ($value && !is_numeric($value))) {
			throw new \App\Exceptions\Security("ERR_ILLEGAL_FIELD_VALUE||$columnName||$value", 406);
		}
		if (!\App\Record::isExists($value)) {
			throw new \App\Exceptions\AppException("ERR_RECORD_NOT_FOUND||$value||$columnName", 406);
		}
		$rangeValues = explode(',', $this->maximumLength);
		if ($value && ($rangeValues[1] < $value || $rangeValues[0] > $value)) {
			throw new \App\Exceptions\AppException("ERR_VALUE_IS_TOO_LONG||$columnName||$value", 406);
		}
	}

	/** {@inheritdoc} */
	public function isRequired()
	{
		$config = $this->getParamsConfig();
		return isset($config['mandatory']) ? 'false' !== $config['mandatory'] : true;
	}

	/**
	 * Gets URL for mass selection.
	 *
	 * @param string $moduleName
	 *
	 * @return string
	 */
	public function getUrlForMassSelection(string $moduleName): string
	{
		$view = 'RecordsList';
		if (\App\Config::module($moduleName, 'inventoryMassAddEntriesTreeView') && ($field = current(Vtiger_Module_Model::getInstance($moduleName)->getFieldsByType('tree', true))) && $field->isViewable()) {
			$view = 'TreeInventoryModal';
		}
		return "index.php?module={$moduleName}&view={$view}&src_module={$this->getModuleName()}&multi_select=true";
	}
}
