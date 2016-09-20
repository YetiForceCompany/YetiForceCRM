<?php

class CalculationsAll
{

	public $name = 'Calculations all';
	public $sequence = 0;
	public $reference = 'SCalculations';

	public function process($instance)
	{
		$db = PearDatabase::getInstance();
		$calculations = 'SELECT COUNT(1) FROM u_yf_scalculations
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=u_yf_scalculations.scalculationsid
				WHERE vtiger_crmentity.deleted=0 && u_yf_scalculations.accountid = ?';
		$resultCalculations = $db->pquery($calculations, [$instance->getId()]);
		return (int) $db->getSingleValue($resultCalculations);
	}
}
