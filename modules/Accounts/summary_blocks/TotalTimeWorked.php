<?php

class TotalTimeWorked
{

	public $name = 'Total time worked';
	public $sequence = 6;
	public $reference = 'OSSTimeControl';

	public function process($instance)
	{
		$adb = PearDatabase::getInstance();
		$timecontrol = 'SELECT SUM(sum_time) as sum FROM vtiger_osstimecontrol
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_osstimecontrol.osstimecontrolid
				WHERE vtiger_crmentity.deleted=0 &&  vtiger_osstimecontrol.link = ? && osstimecontrol_status = ?';
		$result_timecontrol = $adb->pquery($timecontrol, array($instance->getId(), 'Accepted'));
		$decimalTimeFormat = vtlib\Functions::decimalTimeFormat($adb->query_result($result_timecontrol, 0, 'sum'));
		return $decimalTimeFormat['short'];
	}
}
