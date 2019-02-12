<?php

class CalculationsAccepted
{
	public $name = 'Calculations accepted';
	public $sequence = 1;
	public $reference = 'SCalculations';

	/**
	 * Process function.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return int
	 */
	public function process(Vtiger_Record_Model $recordModel)
	{
		return (int) (new \App\Db\Query())->from('u_#__scalculations')
			->innerJoin('vtiger_crmentity', 'u_#__scalculations.scalculationsid = vtiger_crmentity.crmid')
			->where(['deleted' => 0, 'u_#__scalculations.accountid' => $recordModel->getId(), 'u_#__scalculations.scalculations_status' => 'PLL_ACCEPTED'])
			->count(1);
	}
}
