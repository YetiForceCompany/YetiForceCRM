<?php

class OffersAll
{

	public $name = 'Offers all';
	public $sequence = 2;
	public $reference = 'SQuotes';

	public function process($instance)
	{
		$db = PearDatabase::getInstance();
		$quotes = 'SELECT COUNT(1) AS count FROM u_yf_squotes
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=u_yf_squotes.squotesid
				WHERE vtiger_crmentity.deleted=0 && u_yf_squotes.accountid = ?;';
		$resultQuotes = $db->pquery($quotes, [$instance->getId()]);
		return (int) $db->getSingleValue($resultQuotes);
	}
}
