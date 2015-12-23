<?php
class CalculationsAccepted{
	public $name = 'Calculations accepted';
	public $sequence = 1;
	public $reference = 'SCalculations';
	
    public function process( $instance ) {
		$db = PearDatabase::getInstance();
		$calculations ='SELECT COUNT(1) FROM u_yf_scalculations
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=u_yf_scalculations.scalculationsid
				WHERE vtiger_crmentity.deleted=0 AND u_yf_scalculations.accountid = ? AND u_yf_scalculations.scalculations_status = ?';
		$resultCalculations = $db->pquery( $calculations, [$instance->getId(), 'PLL_ACCEPTED'] );
		return (int) $db->getSingleValue($resultCalculations);
    }
}
