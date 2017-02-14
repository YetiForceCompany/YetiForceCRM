<?php

class OffersAccepted
{

	public $name = 'Offers accepted';
	public $sequence = 3;
	public $reference = 'SQuotes';

	public function process($instance)
	{
		return (int) (new \App\Db\Query())->from('u_#__squotes')
			->innerJoin('vtiger_crmentity', 'u_#__squotes.squotesid = vtiger_crmentity.crmid')
			->where(['deleted' => 0, 'u_#__squotes.accountid' => $instance->getId(), 'u_#__squotes.squotes_status' => 'PLL_ACCEPTED'])->count(1);
	}
}
