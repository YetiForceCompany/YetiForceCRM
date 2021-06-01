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

	/** {@inheritdoc} */
	public function getEditTemplateName()
	{
		return 'inventoryTypes/Reference.tpl';
	}

	/** {@inheritdoc} */
	public function getDisplayValue($value, array $rowData = [], bool $rawText = false)
	{
		if (empty($value) || !($referenceModule = $this->getReferenceModule($value))) {
			return '';
		}
		$referenceModuleName = $referenceModule->getName();
		if ('Users' === $referenceModuleName || 'Groups' === $referenceModuleName) {
			return \App\Fields\Owner::getLabel($value);
		}
		if (!\App\Record::isExists($value)) {
			return '';
		}
		$label = \App\Record::getLabel($value);
		if ($rawText || ($value && !\App\Privilege::isPermitted($referenceModuleName, 'DetailView', $value))) {
			return $label;
		}
		$label = App\TextParser::textTruncate($label, \App\Config::main('href_max_length'));
		if ('Active' !== \App\Record::getState($value)) {
			$label = '<s>' . $label . '</s>';
		}
		return "<a class='modCT_$referenceModuleName showReferenceTooltip js-popover-tooltip--record' href='index.php?module=$referenceModuleName&view=" . $referenceModule->getDetailViewName() . "&record=$value'>$label</a>";
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
	 * Function to get reference modules.
	 *
	 * @return array
	 */
	public function getReferenceModules()
	{
		$paramsDecoded = $this->getParamsConfig();
		return $paramsDecoded['modules'];
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
			$metadata = vtlib\Functions::getCRMRecordMetadata($record);
			$referenceModuleList = $this->getReferenceModules();
			$referenceEntityType = $metadata['setype'] ?? '';
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
		if ($rangeValues[1] < $value || $rangeValues[0] > $value) {
			throw new \App\Exceptions\Security("ERR_VALUE_IS_TOO_LONG||$columnName||$value", 406);
		}
	}
}
