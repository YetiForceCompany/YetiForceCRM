<?php
/**
 * Base condition record field file.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
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
	 * @var \Vtiger_Field_Model
	 */
	protected $sourceFieldModel;

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
	 * @param \Vtiger_Field_Model  $fieldModel
	 *
	 * @return void
	 */
	public function setSource(\Vtiger_Record_Model $recordModel, \Vtiger_Field_Model $fieldModel)
	{
		$this->sourceRecordModel = $recordModel;
		$this->sourceFieldModel = $fieldModel;
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
	 * Get value.
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->recordModel->get($this->fieldModel->getFieldName());
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
			return 0 == substr_compare($this->getValue(), $this->value, 0, $ssl);
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
		return false !== strpos($this->value, $this->getValue());
	}

	/**
	 * Does not contain operator.
	 *
	 * @return bool
	 */
	public function operatorK()
	{
		if (\is_array($this->getValue())) {
			return !\in_array($this->value, $this->getValue());
		}
		return false === strpos($this->value, $this->getValue());
	}

	/**
	 * Less than operator.
	 *
	 * @return bool
	 */
	public function operatorL()
	{
		return $this->getValue() > $this->value;
	}

	/**
	 * Greater than operator.
	 *
	 * @return bool
	 */
	public function operatorG()
	{
		return $this->getValue() < $this->value;
	}

	/**
	 * Less than or equal to operator.
	 *
	 * @return bool
	 */
	public function operatorM()
	{
		return $this->getValue() >= $this->value;
	}

	/**
	 * Greater than or equal to operator.
	 *
	 * @return bool
	 */
	public function operatorH()
	{
		return $this->getValue() <= $this->value;
	}

	/**
	 * Before operator.
	 *
	 * @return bool
	 */
	public function operatorB()
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
	public function operatorA()
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
	 * @return array
	 */
	public function operatorY()
	{
		return empty($this->getValue());
	}

	/**
	 * Is not empty operator.
	 *
	 * @return array
	 */
	public function operatorNy()
	{
		return !empty($this->getValue());
	}

	/**
	 * Is watching record operator.
	 *
	 * @return array
	 */
	public function operatorWr()
	{
		return Vtiger_Watchdog_Model::getInstanceById($this->recordModel->getId(), $this->recordModel->getModuleName())->isWatchingRecord();
	}

	/**
	 * Is not watching record operator.
	 *
	 * @return array
	 */
	public function operatorNwr()
	{
		return !Vtiger_Watchdog_Model::getInstanceById($this->recordModel->getId(), $this->recordModel->getModuleName())->isWatchingRecord();
	}

	/**
	 * Is currently logged user operator.
	 *
	 * @return array
	 */
	public function operatorOm()
	{
		return $this->getValue() == \App\User::getCurrentUserId();
	}

	/**
	 * Is currently logged user group operator.
	 *
	 * @return array
	 */
	public function operatorOgr()
	{
		return \in_array($this->getValue(), \App\User::getGroups());
	}
}
