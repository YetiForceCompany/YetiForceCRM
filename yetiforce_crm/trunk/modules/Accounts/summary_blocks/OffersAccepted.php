<?php
class OffersAccepted{
	public $name = 'Offers accepted';
	public $sequence = 3;
	public $reference = 'Quotes';
	
    public function process( $instance ) {
		$adb = PearDatabase::getInstance();
		$quotes ='SELECT COUNT(quotestage) AS count FROM vtiger_quotes
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_quotes.quoteid
				WHERE vtiger_crmentity.deleted=0 AND vtiger_quotes.accountid = ? AND vtiger_quotes.quotestage = ?';
		$result_quotes = $adb->pquery($quotes, array( $instance->getId(), 'Accepted' ));
		$count = $adb->query_result($result_quotes, 0, 'count');
		return $count;
    }
}