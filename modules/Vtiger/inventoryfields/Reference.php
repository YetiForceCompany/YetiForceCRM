<?php

/**
 * Inventory Reference Field Class.
 *
 * @package   InventoryField
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_Reference_InventoryField extends Vtiger_Basic_InventoryField
{
	protected $type = 'Reference';
	protected $defaultLabel = 'LBL_REFERENCE';
	protected $columnName = 'ref';
	protected $dbType = 'int';
	protected $params = ['modules', 'mandatory'];
	protected $maximumLength = '-2147483648,2147483647';
	protected $purifyType = \App\Purifier::INTEGER;

	/** {@inheritdoc} */
	public function getEditTemplateName()
	{
		return 'inventoryTypes/Reference.tpl';
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		if (empty($value)) {
			return '';
		}
		if (!($referenceModule = $this->getReferenceModule($value))) {
			return '<i class="color-red-500" title="' . \App\Purifier::encodeHtml($value) . '">' . \App\Language::translate('LBL_RECORD_DOES_NOT_EXIST') . '</i>';
		}
		$referenceModuleName = $referenceModule->getName();
		if ('Users' === $referenceModuleName || 'Groups' === $referenceModuleName) {
			return \App\Fields\Owner::getLabel($value);
		}
		if ($rawText) {
			return \App\Record::getLabel($value, $rawText);
		}
		return \App\Record::getHtmlLink($value, $referenceModuleName, \App\Config::main('href_max_length'));
	}

	/** {@inheritdoc} */
	public function getEditValue($value)
	{
		if (empty($value)) {
			return '';
		}
		if (($referenceModule = $this->getReferenceModule($value)) && ('Users' === $referenceModule->getName() || 'Groups' === $referenceModule->getName())) {
			return \App\Fields\Owner::getLabel($value);
		}
		return \App\Record::getLabel($value);
	}

	/** {@inheritdoc} */
	public function isMandatory()
	{
		$config = $this->getParamsConfig();
		return isset($config['mandatory']) ? 'false' !== $config['mandatory'] : true;
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
			$referenceModuleList = $this->getParamsConfig()['modules'];
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
		$rangeValues = explode(',', $this->maximumLength);
		if (!empty($value) && ($rangeValues[1] < $value || $rangeValues[0] > $value)) {
			throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||$value", 406);
		}
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
}
