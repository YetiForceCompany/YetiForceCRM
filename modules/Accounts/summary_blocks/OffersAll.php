<?php

class OffersAll
{

	public $name = 'Offers all';
	public $sequence = 2;
	public $reference = 'SQuotes';

	public function process($instance)
	{
		return (int) (new \App\Db\Query())->from('u_yf_squotes')
			->innerJoin('vtiger_crmentity', 'u_yf_squotes.squotesid = vtiger_crmentity.crmid')
			->where(['deleted' => 0, 'u_yf_squotes.accountid' => $instance->getId()])->count(1);
	}
}
