<?php
class TotalSale{
	public $name = 'Total sale';
	public $sequence = 7;
	public $reference = 'Invoice';
	
    public function process( $instance ) {
		$adb = PearDatabase::getInstance();
		$invoice ='SELECT SUM(total) as sum FROM vtiger_invoice
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_invoice.invoiceid
				WHERE vtiger_crmentity.deleted=0 AND vtiger_invoice.accountid = ? AND invoicestatus = ?';
		$result_invoice = $adb->pquery($invoice, array($instance->getId(), 'Invoice entered'));
		return number_format($adb->query_result($result_invoice, 0, 'sum'),0, ',', ' ');
    }
}