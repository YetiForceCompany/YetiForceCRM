<?php

/**
 * SumTimeProject class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class SumTimeProject
{
	public $name = 'FL_TOTAL_TIME_H';
	public $sequence = 9;
	public $reference = 'OSSTimeControl';
	public $icon = '';
	public $type = 'value';

	/**
	 * Process.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return string
	 */
	public function process(Vtiger_Record_Model $recordModel)
	{
		return \App\Fields\RangeTime::displayElapseTime($recordModel->get('sum_time'));
	}
}
