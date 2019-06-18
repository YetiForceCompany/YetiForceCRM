<?php
/**
 * Base condition record field file.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Conditions\RecordFields;

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
}
