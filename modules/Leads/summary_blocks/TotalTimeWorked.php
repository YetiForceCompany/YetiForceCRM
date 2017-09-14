<?php

/**
 * TotalTimeWorked class
 * @package YetiForce.SummaryBlock
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class TotalTimeWorked
{

	public $name = 'Total time worked';
	public $sequence = 5;
	public $reference = 'OSSTimeControl';

	/**
	 * Process
	 * @param Vtiger_Record_Model $recordModel
	 * @return int
	 */
	public function process(Vtiger_Record_Model $recordModel)
	{

		\App\Log::trace('Entering TotalTimeWorked::process() method ...');
		$adb = PearDatabase::getInstance();
		$timecontrol = 'SELECT SUM(sum_time) as sum FROM vtiger_osstimecontrol
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_osstimecontrol.osstimecontrolid
			WHERE vtiger_crmentity.deleted=0 &&  vtiger_osstimecontrol.link = ?';
		$result_timecontrol = $adb->pquery($timecontrol, array($recordModel->getId()));
		$decimalTimeFormat = vtlib\Functions::decimalTimeFormat($adb->queryResult($result_timecontrol, 0, 'sum'));
		\App\Log::trace('Exiting TotalTimeWorked::process() method ...');
		return $decimalTimeFormat['short'];
	}
}
