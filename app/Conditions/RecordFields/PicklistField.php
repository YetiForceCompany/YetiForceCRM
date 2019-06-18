<?php
/**
 * Picklist condition record field file.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App\Conditions\RecordFields;

/**
 * Picklist condition record field class.
 */
class PicklistField extends BaseField
{
	/**
	 * Record open operator.
	 *
	 * @return bool
	 */
	public function operatorRo()
	{
		if (
			($fieldName = App\RecordStatus::getFieldName($this->recordModel->getModule()->getName())) &&
		\in_array($this->recordModel->get($fieldName), App\RecordStatus::getStates($this->recordModel->getModule()->getName()), \App\RecordStatus::RECORD_STATE_OPEN)
		) {
			return true;
		}
		return false;
	}

	/**
	 * Record closed operator.
	 *
	 * @return bool
	 */
	public function operatorRc()
	{
		if (
			($fieldName = App\RecordStatus::getFieldName($this->recordModel->getModule()->getName())) &&
		\in_array($this->recordModel->get($fieldName), App\RecordStatus::getStates($this->recordModel->getModule()->getName(), \App\RecordStatus::RECORD_STATE_CLOSED))
		) {
			return false;
		}
		return true;
	}
}
