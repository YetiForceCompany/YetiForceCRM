<?php

/**
 * Reservations record model class.
 * @package   Model
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Reservations_Record_Model extends Vtiger_Record_Model
{
	const RECALCULATE_STATUS = 'Accepted';

	public function checkID($ID)
	{
		if (0 == $ID || '' == $ID) {
			return false;
		}
		return true;
	}

	public function getDuplicateRecordUrl()
	{
		$module = $this->getModule();
		$date = new DateTime();
		$currDate = DateTimeField::convertToUserFormat($date->format('Y-m-d'));

		$time = $date->format('H:i');

		return 'index.php?module=' . $this->getModuleName() . '&view=' . $module->getEditViewName() . '&record=' . $this->getId() . '&isDuplicate=true&date_start='
			. $currDate . '&due_date=' . $currDate . '&time_start=' . $time . '&time_end=' . $time;
	}
}
