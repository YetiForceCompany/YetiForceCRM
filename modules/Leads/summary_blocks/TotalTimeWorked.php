<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class TotalTimeWorked
{

	public $name = 'Total time worked';
	public $sequence = 5;
	public $reference = 'OSSTimeControl';

	public function process($instance)
	{
		
		\App\Log::trace("Entering TotalTimeWorked::process() method ...");
		$adb = PearDatabase::getInstance();
		$timecontrol = 'SELECT SUM(sum_time) as sum FROM vtiger_osstimecontrol
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_osstimecontrol.osstimecontrolid
			WHERE vtiger_crmentity.deleted=0 &&  vtiger_osstimecontrol.link = ?';
		$result_timecontrol = $adb->pquery($timecontrol, array($instance->getId()));
		$decimalTimeFormat = vtlib\Functions::decimalTimeFormat($adb->query_result($result_timecontrol, 0, 'sum'));
		\App\Log::trace("Exiting TotalTimeWorked::process() method ...");
		return $decimalTimeFormat['short'];
	}
}
