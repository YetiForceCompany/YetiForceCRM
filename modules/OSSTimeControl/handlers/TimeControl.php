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

class TimeControlHandler extends VTEventHandler
{

	function handleEvent($eventName, $data)
	{
		if (!is_object($data)) {
			$extendedData = $data;
			$data = $extendedData['entityData'];
		}

		$moduleName = $data->getModuleName();
		$record_id = $data->getId();
		switch ($eventName) {
			case 'vtiger.entity.aftersave.final':
				if ($moduleName == 'OSSTimeControl') {
					$relatedToId = $data->get('related_to');
					$start = strtotime($data->get('date_start') . ' ' . $data->get('time_start'));
					$end = strtotime($data->get('due_date') . ' ' . $data->get('time_end'));
					$time = round(abs($end - $start) / 3600, 2);
					$data->set('sum_time', $time);
					$db = PearDatabase::getInstance();
					$db->pquery("UPDATE vtiger_osstimecontrol SET sum_time = ? WHERE osstimecontrolid = ?;", array($time, $record_id), true);
					OSSTimeControl_Record_Model::recalculateTimeControl($data);
					OSSTimeControl_Record_Model::recalculateTimeOldValues($record_id, $data);
				}
				if ($moduleName == 'HelpDesk') {
					OSSTimeControl_Record_Model::recalculateProject($data->get('projectid'));
				}
				if ($moduleName == 'ProjectTask') {
					OSSTimeControl_Record_Model::recalculateProject($data->get('projectid'));
				}
				if ($moduleName == 'Project') {
					OSSTimeControl_Record_Model::recalculateServiceContracts($data->get('servicecontractsid'));
				}
				if ($moduleName == 'Quotes') {
					OSSTimeControl_Record_Model::recalculatePotentials($data->get('potential_id'));
				}
				if ($moduleName == 'Accounts') {
					OSSTimeControl_Record_Model::recalculateAccounts($record_id);
				}
				break;
			case 'vtiger.entity.unlink.after':
				if ($moduleName == 'OSSTimeControl') {
					OSSTimeControl_Record_Model::recalculateTimeOldValues($record_id, $data);
				}
				break;
			case 'vtiger.entity.afterdelete':
				if ($moduleName == 'OSSTimeControl') {
					$db = PearDatabase::getInstance();
					$db->pquery("UPDATE vtiger_osstimecontrol SET deleted = ? WHERE osstimecontrolid = ?;", array(1, $record_id), true);
					OSSTimeControl_Record_Model::recalculateTimeControl($data);
				}
				break;
		}
	}
}
