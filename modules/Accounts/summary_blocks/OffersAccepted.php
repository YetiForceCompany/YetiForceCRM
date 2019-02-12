<?php

class OffersAccepted
{
	public $name = 'Offers accepted';
	public $sequence = 3;
	public $reference = 'SQuotes';

	/**
	 * Process function.
	 *
	 * @param Vtiger_Record_Model $recordModel
	 *
	 * @return int
	 */
	public function process(Vtiger_Record_Model $recordModel)
	{
		return (int) (new \App\Db\Query())->from('u_#__squotes')
			->innerJoin('vtiger_crmentity', 'u_#__squotes.squotesid = vtiger_crmentity.crmid')
			->where(['deleted' => 0, 'u_#__squotes.accountid' => $recordModel->getId(), 'u_#__squotes.squotes_status' => 'PLL_ACCEPTED'])->count(1);
	}
}
