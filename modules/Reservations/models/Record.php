<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * Contributor(s): YetiForce.com.
 * *********************************************************************************************************************************** */

Class Reservations_Record_Model extends Vtiger_Record_Model
{

	const recalculateStatus = 'Accepted';

	public function recalculateTimeControl($data)
	{
		$db = PearDatabase::getInstance();
		$ticketid = $data->get('ticketid');
		$projectid = $data->get('projectid');
		$projecttaskid = $data->get('projecttaskid');
		$servicecontractsid = $data->get('servicecontractsid');
		$reservationsid = $data->get('reservationsid');

		self::recalculateProjectTask($projecttaskid);
		self::recalculateHelpDesk($ticketid);
		self::recalculateProject($projectid);
		self::recalculateServiceContracts($servicecontractsid);

		if (self::checkID($projecttaskid)) {
			$ModuleNameInstance = Vtiger_Record_Model::getInstanceById($projecttaskid, 'ProjectTask');
			$projectid = $ModuleNameInstance->get('projectid');
			if (self::checkID($projectid)) {
				self::recalculateProject($projectid);
				$ModuleNameInstance = Vtiger_Record_Model::getInstanceById($projectid, 'Project');
				self::recalculateServiceContracts($ModuleNameInstance->get('servicecontractsid'));
			}
		}
		if (self::checkID($ticketid)) {
			$ModuleNameInstance = Vtiger_Record_Model::getInstanceById($ticketid, 'HelpDesk');
			$projectid = $ModuleNameInstance->get('projectid');
			if (self::checkID($projectid)) {
				self::recalculateProject($projectid);
				$ModuleNameInstance = Vtiger_Record_Model::getInstanceById($projectid, 'Project');
				self::recalculateServiceContracts($ModuleNameInstance->get('servicecontractsid'));
			}
		}
		if (self::checkID($ticketid)) {
			$ModuleNameInstance = Vtiger_Record_Model::getInstanceById($ticketid, 'HelpDesk');
			self::recalculateServiceContracts($ModuleNameInstance->get('servicecontractsid'));
		}
	}

	public function recalculateProjectTask($ProjectTaskID)
	{
		if (!self::checkID($ProjectTaskID)) {
			return false;
		}
		$db = PearDatabase::getInstance();
		$sum_time = 0;
		$sum_result = $db->pquery("SELECT SUM(sum_time) as sum FROM vtiger_reservations WHERE deleted = ? && reservations_status = ? && projecttaskid = ?;", array(0, self::recalculateStatus, $ProjectTaskID), true);
		$sum_time = number_format($db->query_result($sum_result, 0, 'sum'), 2);
//		$db->pquery( "UPDATE vtiger_projecttask SET sum_time = ? WHERE projecttaskid = ?;",
//			array($sum_time,$ProjectTaskID), true );
		return $sum_time;
	}

	public function recalculateServiceContracts($ServiceContractsID)
	{
		if (!self::checkID($ServiceContractsID)) {
			return false;
		}
		$db = PearDatabase::getInstance();
		$sum_time = 0;
		$sum_result = $db->pquery("SELECT SUM(sum_time) as sum FROM vtiger_reservations WHERE deleted = ? && reservations_status = ? && servicecontractsid = ? && projecttaskid = ? && ticketid = ? && projectid = ?;", array(0, self::recalculateStatus, $ServiceContractsID, 0, 0, 0), true);
		$sum_time = number_format($db->query_result($sum_result, 0, 'sum'), 2);
		//////// sum_time_h
		$sql_sum_time_h = 'SELECT SUM(vtiger_reservations.sum_time) AS sum 
			FROM vtiger_reservations 
			INNER JOIN vtiger_troubletickets ON vtiger_troubletickets.ticketid = vtiger_reservations.ticketid
			WHERE vtiger_reservations.deleted = ? 
			AND vtiger_reservations.ticketid <> ? 
			AND vtiger_reservations.projectid = ?
			AND reservations_status = ?
			AND vtiger_troubletickets.servicecontractsid = ?;';
		$sum_time_h_result = $db->pquery($sql_sum_time_h, array(0, 0, 0, self::recalculateStatus, $ServiceContractsID), true);
		$sum_time_h = number_format($db->query_result($sum_time_h_result, 0, 'sum'), 2);
		//////// sum_time_p
		$project_result = $db->pquery("SELECT projectid 
			FROM vtiger_project
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_project.projectid
			WHERE deleted = ? && servicecontractsid = ?;", array(0, $ServiceContractsID), true);
		
		$numRowsCount = $db->num_rows($project_result);
		for ($i = 0; $i < $numRowsCount; $i++) {
			$ProjectID = $db->query_result($project_result, $i, 'projectid');
			$sum_time_result = $db->pquery("SELECT SUM(sum_time) as sum FROM vtiger_reservations WHERE deleted = ? && reservations_status = ? && projectid = ? && projecttaskid = ? && ticketid = ?;", array(0, self::recalculateStatus, $ProjectID, 0, 0), true);
			$sum_time_p += number_format($db->query_result($sum_time_result, 0, 'sum'), 2);
			$sql_sum_time_h = 'SELECT SUM(vtiger_reservations.sum_time) AS sum 
							FROM vtiger_reservations 
							INNER JOIN vtiger_troubletickets ON vtiger_troubletickets.ticketid = vtiger_reservations.ticketid
							WHERE vtiger_reservations.deleted = ? 
							AND vtiger_reservations.ticketid <> ? 
							AND vtiger_reservations.projectid = ?
							AND vtiger_reservations.servicecontractsid = ?
							AND reservations_status = ?
							AND vtiger_troubletickets.projectid = ?;';
			$sum_time_h_result = $db->pquery($sql_sum_time_h, array(0, 0, 0, 0, self::recalculateStatus, $ProjectID), true);
			$sum_time_p += number_format($db->query_result($sum_time_h_result, 0, 'sum'), 2);
			$sql_sum_time_pt = 'SELECT SUM(vtiger_reservations.sum_time) AS sum 
							FROM vtiger_reservations 
							INNER JOIN vtiger_projecttask ON vtiger_projecttask.projecttaskid = vtiger_reservations.projecttaskid
							WHERE vtiger_reservations.deleted = ? 
							AND vtiger_reservations.projecttaskid <> ? 
							AND vtiger_reservations.ticketid = ? 
							AND vtiger_reservations.projectid = ?
							AND vtiger_reservations.servicecontractsid = ?
							AND vtiger_reservations.reservations_status = ?
							AND vtiger_projecttask.projectid = ?;';
			$sum_time_pt_result = $db->pquery($sql_sum_time_pt, array(0, 0, 0, 0, 0, self::recalculateStatus, $ProjectID), true);
			$sum_time_p += number_format($db->query_result($sum_time_pt_result, 0, 'sum'), 2);
		}
		//////////////////
		//////// Sum
		$sum_time_all = $sum_time + $sum_time_h + $sum_time_p;
//		$db->pquery( "UPDATE vtiger_servicecontracts SET sum_time = ?,sum_time_h = ?,sum_time_p = ?,sum_time_all = ? WHERE servicecontractsid = ?;",
//			array($sum_time,$sum_time_h,$sum_time_p,$sum_time_all,$ServiceContractsID), true );
		return array($sum_time, $sum_time_h, $sum_time_p, $sum_time_all);
	}

	public function recalculateProject($ProjectID)
	{
		if (!self::checkID($ProjectID)) {
			return false;
		}
		$db = PearDatabase::getInstance();
		$sum_time = 0;
		//////// sum_time
		$sum_time_result = $db->pquery("SELECT SUM(sum_time) as sum FROM vtiger_reservations WHERE deleted = ? && reservations_status = ? && projectid = ? && projecttaskid = ? && ticketid = ?;", array(0, self::recalculateStatus, $ProjectID, 0, 0), true);
		$sum_time = number_format($db->query_result($sum_time_result, 0, 'sum'), 2);
		//////// sum_time_h
		$sql_sum_time_h = 'SELECT SUM(vtiger_reservations.sum_time) AS sum 
						FROM vtiger_reservations 
						INNER JOIN vtiger_troubletickets ON vtiger_troubletickets.ticketid = vtiger_reservations.ticketid
						WHERE vtiger_reservations.deleted = ? 
						AND vtiger_reservations.ticketid <> ? 
						AND reservations_status = ?
						AND vtiger_troubletickets.projectid = ?;';
		$sum_time_h_result = $db->pquery($sql_sum_time_h, array(0, 0, self::recalculateStatus, $ProjectID), true);
		$sum_time_h = number_format($db->query_result($sum_time_h_result, 0, 'sum'), 2);
		//////// sum_time_pt
		$sql_sum_time_pt = 'SELECT SUM(vtiger_reservations.sum_time) AS sum 
						FROM vtiger_reservations 
						INNER JOIN vtiger_projecttask ON vtiger_projecttask.projecttaskid = vtiger_reservations.projecttaskid
						WHERE vtiger_reservations.deleted = ? 
						AND vtiger_reservations.projecttaskid <> ? 
						AND vtiger_reservations.ticketid = ? 
						AND vtiger_reservations.reservations_status = ?
						AND vtiger_projecttask.projectid = ?;';
		$sum_time_pt_result = $db->pquery($sql_sum_time_pt, array(0, 0, 0, self::recalculateStatus, $ProjectID), true);
		$sum_time_pt = number_format($db->query_result($sum_time_pt_result, 0, 'sum'), 2);
		//////// Sum
		$sum_time_all = $sum_time + $sum_time_h + $sum_time_pt;
//		$db->pquery( "UPDATE vtiger_project SET sum_time = ?,sum_time_h = ?,sum_time_pt = ?,sum_time_all = ? WHERE projectid = ?;",
//			array($sum_time,$sum_time_h,$sum_time_pt,$sum_time_all,$ProjectID), true );
		return array($sum_time, $sum_time_h, $sum_time_pt, $sum_time_all);
	}

	public function recalculateHelpDesk($HelpDeskID)
	{
		if (!self::checkID($HelpDeskID)) {
			return false;
		}
		$db = PearDatabase::getInstance();
		$sum_time = 0;
		$sum_result = $db->pquery("SELECT SUM(sum_time) as sum FROM vtiger_reservations WHERE deleted = ? && reservations_status = ? && ticketid = ?;", array(0, self::recalculateStatus, $HelpDeskID), true);
		$sum_time = number_format($db->query_result($sum_result, 0, 'sum'), 2);
//		$db->pquery( "UPDATE vtiger_troubletickets SET sum_time = ? WHERE ticketid = ?;",
//			array($sum_time,$HelpDeskID), true );
		return $sum_time;
	}

	public function getProjectRelatedIDS($ProjectID)
	{
		if (!self::checkID($ProjectID)) {
			return false;
		}
		$db = PearDatabase::getInstance();
		//////// sum_time
		$projectIDS = array();
		$sum_time_result = $db->pquery("SELECT reservationsid FROM vtiger_reservations WHERE deleted = ? && reservations_status = ? && projectid = ? && projecttaskid = ? && ticketid = ?;", array(0, self::recalculateStatus, $ProjectID, 0, 0), true);
		
		$numRowsCount = $db->num_rows($sum_time_result);
		for ($i = 0; $i < $numRowsCount; $i++) {
			$projectIDS[] = $db->query_result($sum_time_result, $i, 'reservationsid');
		}
		//////// sum_time_h
		$ticketsIDS = array();
		$sql_sum_time_h = 'SELECT reservationsid 
						FROM vtiger_reservations 
						INNER JOIN vtiger_troubletickets ON vtiger_troubletickets.ticketid = vtiger_reservations.ticketid
						WHERE vtiger_reservations.deleted = ? 
						AND vtiger_reservations.ticketid <> ? 
						AND reservations_status = ?
						AND vtiger_troubletickets.projectid = ?;';
		$sum_time_h_result = $db->pquery($sql_sum_time_h, array(0, 0, self::recalculateStatus, $ProjectID), true);
		
		$numRowsCount = $db->num_rows($sum_time_h_result);
		for ($i = 0; $i < $numRowsCount; $i++) {
			$ticketsIDS[] = $db->query_result($sum_time_h_result, $i, 'reservationsid');
		}
		//////// sum_time_pt
		$taskIDS = array();
		$sql_sum_time_pt = 'SELECT reservationsid 
						FROM vtiger_reservations 
						INNER JOIN vtiger_projecttask ON vtiger_projecttask.projecttaskid = vtiger_reservations.projecttaskid
						WHERE vtiger_reservations.deleted = ? 
						AND vtiger_reservations.projecttaskid <> ? 
						AND vtiger_reservations.ticketid = ? 
						AND vtiger_reservations.projectid = ?
						AND vtiger_reservations.reservations_status = ?
						AND vtiger_projecttask.projectid = ?;';
		$sum_time_pt_result = $db->pquery($sql_sum_time_pt, array(0, 0, 0, 0, self::recalculateStatus, $ProjectID), true);
		
		$numRowsCount = $db->num_rows($sum_time_pt_result);
		for ($i = 0; $i < $numRowsCount; $i++) {
			$taskIDS[] = $db->query_result($sum_time_pt_result, $i, 'reservationsid');
		}
		return array($taskIDS, $ticketsIDS, $projectIDS);
	}

	public function checkID($ID)
	{
		if ($ID == 0 || $ID == '') {
			return false;
		}
		return true;
	}

	public function getDuplicateRecordUrl()
	{
		$module = $this->getModule();
		$date = new DateTime();
		$currDate = DateTimeField::convertToUserFormat($date->format('Y-m-d'));

		$time = $date->format('H:i');

		return 'index.php?module=' . $this->getModuleName() . '&view=' . $module->getEditViewName() . '&record=' . $this->getId() . '&isDuplicate=true&date_start='
			. $currDate . '&due_date=' . $currDate . '&time_start=' . $time . '&time_end=' . $time;
	}
}
