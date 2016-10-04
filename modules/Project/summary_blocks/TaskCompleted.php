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

class TaskCompleted
{

	public $name = 'LBL_TASKS_COMPLETED';
	public $sequence = 1;
	public $reference = 'ProjectTask';

	public function process($instance)
	{
		
		\App\Log::trace("Entering TaskCompleted::process() method ...");
		$adb = PearDatabase::getInstance();
		$query = 'SELECT COUNT(projecttaskid) as count 
				FROM vtiger_projecttask
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=vtiger_projecttask.projecttaskid
						WHERE vtiger_crmentity.deleted=0 && vtiger_projecttask.projectid = ? && vtiger_projecttask.projecttaskstatus = ? ';
		$result = $adb->pquery($query, array($instance->getId(), 'Completed'));
		$count = $adb->query_result($result, 0, 'count');
		\App\Log::trace("Exiting TaskCompleted::process() method ...");
		return $count;
	}
}
