<?php
class OffersAll{
	public $name = 'Offers all';
	public $sequence = 2;
	public $reference = 'Quotes';
	
    public function process( $instance ) {
		$adb = PearDatabase::getInstance();
		$quotes ='SELECT COUNT(quotestage) AS count FROM vtiger_quotes
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_quotes.quoteid
				WHERE vtiger_crmentity.deleted=0 AND vtiger_quotes.accountid = ?';
		$result_quotes = $adb->pquery($quotes, array($instance->getId()));
		$count = $adb->query_result($result_quotes, 0, 'count');
		return $count;
    }
}