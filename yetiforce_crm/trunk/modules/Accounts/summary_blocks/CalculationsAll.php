<?php
class CalculationsAll{
	public $name = 'Calculations all';
	public $sequence = 0;
	public $reference = 'Calculations';
	
    public function process( $instance ) {
		$adb = PearDatabase::getInstance();
		$calculations ='SELECT COUNT(calculationsstatus) AS count FROM vtiger_calculations
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_calculations.calculationsid
				WHERE vtiger_crmentity.deleted=0 AND vtiger_calculations.relatedid = ?';
		$result_calculations = $adb->pquery( $calculations, array( $instance->getId() ) );
		$count = $adb->query_result($result_calculations, 0, 'count');
		return $count;
    }
}