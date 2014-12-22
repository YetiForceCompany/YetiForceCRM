<?php
class CalculationsAccepted{
	public $name = 'Calculations accepted';
	public $sequence = 1;
	public $reference = 'Calculations';
	
    public function process( $instance ) {
		$adb = PearDatabase::getInstance();
		$calculations ='SELECT COUNT(calculationsstatus) AS count FROM vtiger_calculations
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_calculations.calculationsid
				WHERE vtiger_crmentity.deleted=0 AND vtiger_calculations.relatedid = ? AND vtiger_calculations.calculationsstatus = ?';
		$result_calculations = $adb->pquery( $calculations, array( $instance->getId(), 'Accepted' ) );
		$count = $adb->query_result($result_calculations, 0, 'count');
		return $count;
    }
}