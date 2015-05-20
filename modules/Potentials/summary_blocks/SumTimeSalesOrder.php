<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
class SumTimeSalesOrder{
	public $name = 'Total time [Sales Order]';
	public $sequence = 11;
	public $reference = 'OSSTimeControl';
	
    public function process( $instance ) {
		$log = vglobal('log');
		$log->debug("Entering SumTimeSalesOrder::process() method ...");
		$sum_time = Vtiger_Functions::decimalTimeFormat( $instance->get('sum_time_so') );
		$log->debug("Exiting SumTimeSalesOrder::process() method ...");
		return $sum_time['short'];
    }
}