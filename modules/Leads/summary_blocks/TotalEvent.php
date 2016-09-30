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

class TotalEvent
{

	public $name = 'Total event';
	public $sequence = 3;
	public $reference = 'Calendar';

	public function process($instance)
	{
		
		\App\Log::trace("Entering TotalEvent::process() method ...");
		$adb = PearDatabase::getInstance();
		$activity = 'SELECT COUNT(vtiger_activity.activityid) AS count
			FROM vtiger_activity 
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_activity.activityid 
			WHERE vtiger_activity.link=? 
			AND vtiger_crmentity.deleted = 0 
			AND vtiger_activity.activitytype <> ?';
		$result_Event = $adb->pquery($activity, array($instance->getId(), 'Task'));
		$count = $adb->query_result($result_Event, 0, 'count');
		\App\Log::trace("Exiting TotalEvent::process() method ...");
		return $count;
	}
}
