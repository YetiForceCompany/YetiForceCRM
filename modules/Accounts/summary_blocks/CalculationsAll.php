<?php

class CalculationsAll
{

	public $name = 'Calculations all';
	public $sequence = 0;
	public $reference = 'SCalculations';

	public function process($instance)
	{
		$count = (new \App\Db\Query())->from('u_#__scalculations')
			->innerJoin('vtiger_crmentity', 'u_#__scalculations.scalculationsid = vtiger_crmentity.crmid')
			->where(['deleted' => 0, 'u_#__scalculations.accountid' => $instance->getId()])->count(1);
		return (int) $count;
	}
}
