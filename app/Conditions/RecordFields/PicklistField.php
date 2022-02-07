<?php
/**
 * Picklist condition record field file.
 *
 * @package UIType
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Conditions\RecordFields;

/**
 * Picklist condition record field class.
 */
class PicklistField extends BaseField
{
	/** {@inheritdoc} */
	public function operatorE(): bool
	{
		return \in_array($this->getValue(), explode('##', $this->value));
	}

	/** {@inheritdoc} */
	public function operatorN(): bool
	{
		return !\in_array($this->getValue(), explode('##', $this->value));
	}

	/**
	 * Record open operator.
	 *
	 * @return bool
	 */
	public function operatorRo()
	{
		return ($fieldName = \App\RecordStatus::getFieldName($this->recordModel->getModule()->getName()))
		&& \in_array($this->recordModel->get($fieldName), \App\RecordStatus::getStates($this->recordModel->getModule()->getName()), \App\RecordStatus::RECORD_STATE_OPEN);
	}

	/**
	 * Record closed operator.
	 *
	 * @return bool
	 */
	public function operatorRc()
	{
		return !(($fieldName = \App\RecordStatus::getFieldName($this->recordModel->getModule()->getName()))
		&& \in_array($this->recordModel->get($fieldName), \App\RecordStatus::getStates($this->recordModel->getModule()->getName(), \App\RecordStatus::RECORD_STATE_CLOSED)));
	}
}
