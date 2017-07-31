<?php

/**
 * SumTimeProject class
 * @package YetiForce.SummaryBlock
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class SumTimeProject
{

	public $name = 'FL_TOTAL_TIME_H';
	public $sequence = 9;
	public $reference = 'OSSTimeControl';

	public function process($instance)
	{

		\App\Log::trace("Entering SumTimeProject::process() method ...");
		$sum_time = vtlib\Functions::decimalTimeFormat($instance->get('sum_time'));
		\App\Log::trace("Exiting SumTimeProject::process() method ...");
		return $sum_time['short'];
	}
}
