<?php

/**
 * SumTimeProject class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class SumTimeProject
{
	public $name = 'FL_TOTAL_TIME_H';
	public $sequence = 9;
	public $reference = 'OSSTimeControl';

	/**
	 * Process.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return string
	 */
	public function process(Vtiger_Record_Model $recordModel)
	{
		return \App\Fields\Time::formatToHourText($recordModel->get('sum_time'), 'short');
	}
}
