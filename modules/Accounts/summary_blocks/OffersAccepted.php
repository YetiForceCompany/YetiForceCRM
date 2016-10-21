<?php

class OffersAccepted
{

	public $name = 'Offers accepted';
	public $sequence = 3;
	public $reference = 'SQuotes';

	public function process($instance)
	{
		return (int) (new \App\Db\Query())->from('u_yf_squotes')
			->innerJoin('vtiger_crmentity', 'u_yf_squotes.squotesid = vtiger_crmentity.crmid')
			->where(['deleted' => 0, 'u_yf_squotes.accountid' => $instance->getId(), 'u_yf_squotes.squotes_status' => 'PLL_ACCEPTED'])->count(1);
	}
}
