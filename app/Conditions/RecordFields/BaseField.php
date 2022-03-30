<?php
/**
 * Base condition record field file.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Conditions\RecordFields;

use App\Log;

/**
 * Base condition record field class.
 */
class BaseField
{
	/**
	 * @var \Vtiger_Record_Model
	 */
	protected $recordModel;
	/**
	 * @var \Vtiger_Field_Model
	 */
	protected $fieldModel;
	/**
	 * @var mixed
	 */
	protected $value;
	/**
	 * @var string
	 */
	protected $operator;
	/**
	 * @var \Vtiger_Record_Model
	 */
	protected $sourceRecordModel;
	/**
	 * @var string
	 */
	protected $sourceFieldName;

	/**
	 * Constructor.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 * @param \Vtiger_Field_Model  $fieldModel
	 * @param array                $rule
	 */
	public function __construct(\Vtiger_Record_Model $recordModel, \Vtiger_Field_Model $fieldModel, array $rule)
	{
		$this->recordModel = $recordModel;
		$this->fieldModel = $fieldModel;
		$this->value = $rule['value'];
		$this->operator = $rule['operator'];
	}

	/**
	 * Set source.
	 *
	 * @param \Vtiger_Record_Model $recordModel
	 * @param string               $fieldName
	 *
	 * @return void
	 */
	public function setSource(\Vtiger_Record_Model $recordModel, string $fieldName)
	{
		$this->sourceRecordModel = $recordModel;
		$this->sourceFieldName = $fieldName;
	}

	/**
	 * Check condition for field.
	 *
	 * @return void
	 */
	public function check()
	{
		$fn = 'operator' . ucfirst($this->operator);
		if (method_exists($this, $fn)) {
			Log::trace("Entering to $fn in " . __CLASS__);
			return $this->{$fn}();
		}
		Log::error("Not found operator: $fn in  " . __CLASS__);
		return false;
	}

	/**
	 * Get value from record.
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->recordModel->get($this->fieldModel->getName());
	}

	/**
	 * Get value from record source.
	 *
	 * @return string
	 */
	public function getValueFromSource(): string
	{
		$return = '';
		[$fieldName, $fieldModuleName, $sourceFieldName] = array_pad(explode(':', $this->value), 3, '');
		if ($sourceFieldName && ($relId = $this->recordModel->get($sourceFieldName)) && \App\Record::isExists($relId, $fieldModuleName)) {
			$return = \Vtiger_Record_Model::getInstanceById($relId, $fieldModuleName)->get($fieldName);
		} elseif (!$sourceFieldName && $this->recordModel->getModuleName() === $fieldModuleName) {
			$return = $this->recordModel->get($fieldName);
		}
		return $return;
	}

	/**
	 * Equals operator.
	 *
	 * @return bool
	 */
	public function operatorE()
	{
		return $this->getValue() == $this->value;
	}

	/**
	 * Not equal operator.
	 *
	 * @return bool
	 */
	public function operatorN()
	{
		return $this->getValue() != $this->value;
	}

	/**
	 * Starts with operator.
	 *
	 * @return bool
	 */
	public function operatorS()
	{
		$ssl = \strlen($this->value);
		if (\strlen($this->getValue()) >= $ssl) {
			return 0 == substr_compare($this->getValue(), $this->value, 0, $ssl, true);
		}
		return false;
	}

	/**
	 * Ends with operator.
	 *
	 * @return bool
	 */
	public function operatorEw()
	{
		$sl = \strlen($this->getValue());
		$ssl = \strlen($this->value);
		if ($sl >= $ssl) {
			return 0 == substr_compare($this->getValue(), $this->value, $sl - $ssl, $ssl);
		}
		return false;
	}

	/**
	 * Contains operator.
	 *
	 * @return bool
	 */
	public function operatorC()
	{
		if (\is_array($this->getValue())) {
			return \in_array($this->value, $this->getValue());
		}
		return false !== strpos($this->getValue(), $this->value);
	}

	/**
	 * Does not contain operator.
	 *
	 * @return bool
	 */
	public function operatorK(): bool
	{
		if (\is_array($this->getValue())) {
			return !\in_array($this->value, $this->getValue());
		}
		return false === strpos($this->getValue(), $this->value);
	}

	/**
	 * Before operator.
	 *
	 * @return bool
	 */
	public function operatorB(): bool
	{
		if (empty($this->getValue())) {
			return false;
		}
		if ($this->getValue() < $this->value) {
			return true;
		}
		return false;
	}

	/**
	 * After operator.
	 *
	 * @return bool
	 */
	public function operatorA(): bool
	{
		if (empty($this->getValue())) {
			return false;
		}
		if ($this->getValue() > $this->value) {
			return true;
		}
		return false;
	}

	/**
	 * Is empty operator.
	 *
	 * @return bool
	 */
	public function operatorY(): bool
	{
		return empty($this->getValue());
	}

	/**
	 * Is not empty operator.
	 *
	 * @return bool
	 */
	public function operatorNy(): bool
	{
		return !empty($this->getValue());
	}

	/**
	 * Has changed operator.
	 *
	 * @return bool
	 */
	public function operatorHs(): bool
	{
		$hasChanged = $this->recordModel->getPreviousValue($this->fieldModel->getName());
		if (false === $hasChanged) {
			return false;
		}
		return $this->getValue() != $hasChanged;
	}

	/**
	 * Has changed to operator.
	 *
	 * @return bool
	 */
	public function operatorHst(): bool
	{
		return false !== $this->recordModel->getPreviousValue($this->fieldModel->getName()) && $this->getValue() == $this->value;
	}

	/**
	 * Is currently logged user operator.
	 *
	 * @return bool
	 */
	public function operatorOm(): bool
	{
		return $this->getValue() == \App\User::getCurrentUserId();
	}

	/**
	 * Not currently logged user operator.
	 *
	 * @return array
	 */
	public function operatorNom()
	{
		return $this->getValue() != \App\User::getCurrentUserId();
	}

	/**
	 * Is currently logged user group operator.
	 *
	 * @return bool
	 */
	public function operatorOgr(): bool
	{
		return \in_array($this->getValue(), \App\User::getCurrentUserModel()->getGroups());
	}

	/**
	 * Is equal to selected field operator.
	 *
	 * @return bool
	 */
	public function operatorEf(): bool
	{
		return $this->getValue() == $this->getValueFromSource();
	}

	/**
	 * Is not equal to selected field operator.
	 *
	 * @return bool
	 */
	public function operatorNf(): bool
	{
		return $this->getValue() != $this->getValueFromSource();
	}
}
