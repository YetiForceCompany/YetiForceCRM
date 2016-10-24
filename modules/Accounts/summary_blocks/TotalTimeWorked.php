<?php

class TotalTimeWorked
{

	public $name = 'Total time worked';
	public $sequence = 6;
	public $reference = 'OSSTimeControl';

	public function process($instance)
	{
		$sum = (new \App\Db\Query())->from('vtiger_osstimecontrol')
			->innerJoin('vtiger_crmentity', 'vtiger_osstimecontrol.osstimecontrolid = vtiger_crmentity.crmid')
			->where(['vtiger_crmentity.deleted' => 0, 'vtiger_osstimecontrol.link' => $instance->getId(), 'osstimecontrol_status' => 'Accepted'])->sum('sum_time');
		$decimalTimeFormat = vtlib\Functions::decimalTimeFormat($sum);
		return $decimalTimeFormat['short'];
	}
}
