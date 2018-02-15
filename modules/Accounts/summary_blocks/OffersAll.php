<?php

class OffersAll
{
	public $name = 'Offers all';
	public $sequence = 2;
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
			->where(['deleted' => 0, 'u_#__squotes.accountid' => $recordModel->getId()])->count(1);
	}
}
