<?php

class OffersAll
{

	public $name = 'Offers all';
	public $sequence = 2;
	public $reference = 'SQuotes';

	public function process($instance)
	{
		return (int) (new \App\Db\Query())->from('u_#__squotes')
			->innerJoin('vtiger_crmentity', 'u_#__squotes.squotesid = vtiger_crmentity.crmid')
			->where(['deleted' => 0, 'u_#__squotes.accountid' => $instance->getId()])->count(1);
	}
}
