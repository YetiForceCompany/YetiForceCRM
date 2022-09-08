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
			$referenceModuleList = $this->getReferenceModules();
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
	 * Function to get reference modules.
	 *
	 * @return array
	 */
	public function getReferenceModules()
	{
		$values = $this->getParamsConfig()['modules'] ?? [];
		if (\is_string($values)) {
			$values = explode(' |##| ', $values);
		}

		return $values;
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

	/** {@inheritdoc} */
	public function getConfigFieldsData(): array
	{
		$qualifiedModuleName = 'Settings:LayoutEditor';
		$data = parent::getConfigFieldsData();

		$data['modules'] = [
			'name' => 'modules',
			'label' => 'LBL_PARAMS_MODULES',
			'uitype' => 33,
			'maximumlength' => '25',
			'typeofdata' => 'V~M',
			'purifyType' => \App\Purifier::ALNUM,
			'picklistValues' => []
		];
		foreach (Vtiger_Module_Model::getAll([0], [], true) as $module) {
			$data['modules']['picklistValues'][$module->getName()] = \App\Language::translate($module->getName(), $module->getName());
		}

		$data['mandatory'] = [
			'name' => 'mandatory',
			'label' => 'LBL_PARAMS_MANDATORY',
			'uitype' => 16,
			'maximumlength' => '5',
			'typeofdata' => 'V~M',
			'purifyType' => \App\Purifier::STANDARD,
			'defaultvalue' => 'true',
			'picklistValues' => [
				'false' => \App\Language::translate('LBL_NO', $qualifiedModuleName),
				'true' => \App\Language::translate('LBL_YES', $qualifiedModuleName),
			],
		];

		return $data;
	}

	/** {@inheritdoc} */
	public function compare($value, $prevValue, string $column): bool
	{
		return (int) $value === (int) $prevValue;
	}
}
