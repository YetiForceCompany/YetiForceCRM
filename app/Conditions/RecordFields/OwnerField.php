<?php

namespace App\Conditions\RecordFields;

/**
 * Owner condition record field class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OwnerField extends BaseField
{
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
}
