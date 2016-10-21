<?php

class CalculationsAll
{

	public $name = 'Calculations all';
	public $sequence = 0;
	public $reference = 'SCalculations';

	public function process($instance)
	{
		$count = (new \App\Db\Query())->from('u_yf_scalculations')
			->innerJoin('vtiger_crmentity', 'u_yf_scalculations.scalculationsid = vtiger_crmentity.crmid')
			->where(['deleted' => 0, 'u_yf_scalculations.accountid' => $instance->getId()])->count(1);
		return (int) $count;
	}
}
