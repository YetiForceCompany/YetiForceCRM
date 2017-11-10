<?php

/**
 * SumTimeProject class
 * @package YetiForce.SummaryBlock
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class SumTimeProject
{

	public $name = 'FL_TOTAL_TIME_H';
	public $sequence = 9;
	public $reference = 'OSSTimeControl';

	/**
	 * Process
	 * @param Vtiger_Record_Model $recordModel
	 * @return string
	 */
	public function process(Vtiger_Record_Model $recordModel)
	{
		\App\Log::trace('Entering SumTimeProject::process() method ...');
		$sum_time = vtlib\Functions::decimalTimeFormat($recordModel->get('sum_time'));
		\App\Log::trace('Exiting SumTimeProject::process() method ...');
		return $sum_time['short'];
	}
}
