<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************************************************************** */

class Vtiger_Kpi_Helper
{

	public $time = false;
	public $service = false;
	public $type = false;

	public function Vtiger_Kpi_Helper($request)
	{
		$this->time = $request->get('time');
		$this->service = $request->get('service');
		$this->type = $request->get('type');
	}

	public function getKpiList()
	{
		$adb = PearDatabase::getInstance();
		$list = [];
		$sql = "SELECT serviceid as id, servicename as name FROM vtiger_service INNER JOIN vtiger_crmentity ON vtiger_service.serviceid = vtiger_crmentity.crmid WHERE vtiger_crmentity.deleted = ? && discontinued = ?;";
		$params = array(0, 1);
		$result = $adb->pquery($sql, $params, true);
		$countResult = $adb->num_rows($result);
		for ($i = 0; $i < $countResult; $i++) {
			$list[$adb->query_result_raw($result, $i, 'id')] = $adb->query_result_raw($result, $i, 'name');
		}
		return $list;
	}

	public function getKpiTypes()
	{
		$types = [];
		$types['tdu'] = 'Terminowość dostarczania usługi';
		$types['cdu'] = 'Czas dostarczania usługi';
		$types['tuatd'] = 'Terminowość usuwania awarii';
		$types['cukapu'] = 'Czas usuniecia każdej awarii priorytetowej dla usługi dzierżawy';
		$types['cukazu'] = 'Czas usuniecia każdej awarii zwykłej dla usługi dzierżawy';
		$types['la100u'] = 'Liczba awarii na 100 usuługi dzierżawy';
		return $types;
	}

	public function getData()
	{
		$type = 'get_' . $this->type;
		if ($this->type == '' || !method_exists('Vtiger_Kpi_Helper', $type)) {
			return;
		}
		return $this->$type();
	}

	// Data KPI
	public function get_tdu()
	{
		$reference = 30;
		$tolerance = '1.00%';
		$maxValue = 100;
		$adb = PearDatabase::getInstance();

		$sql = "SELECT ordertime 
			FROM vtiger_osssoldservices 
			INNER JOIN vtiger_crmentity ON vtiger_osssoldservices.osssoldservicesid = vtiger_crmentity.crmid 
			WHERE vtiger_crmentity.deleted = ? && serviceid = ? && vtiger_crmentity.createdtime BETWEEN ? AND ?;";
		$params = array(0, $this->service, $this->time['start'], $this->time['end']);
		$result = $adb->pquery($sql, $params, true);
		$all = 0;
		$accepted = 0;
		$countResult = $adb->num_rows($result);
		for ($i = 0; $i < $countResult; $i++) {
			if ($adb->query_result_raw($result, $i, 'ordertime') < $reference) {
				$accepted++;
			}
			$all++;
		}
		if ($all == 0) {
			return 0;
		} else {
			$result = number_format($accepted / $all * 100, 2);
			return array(
				'result_lable' => $result . ' procent terminowo dostarczonych usług w okresie raportowym',
				'result' => $result,
				'reference_lable' => "100% (max $reference dni)",
				'reference' => $reference,
				'tolerance' => $tolerance,
				'accepted' => $accepted,
				'all' => $all,
				'maxValue' => $maxValue,
			);
		}
	}

	public function get_cdu()
	{
		$reference = 30;
		$tolerance = '1 dzień';
		$adb = PearDatabase::getInstance();

		$sql = "SELECT ordertime 
			FROM vtiger_osssoldservices 
			INNER JOIN vtiger_crmentity ON vtiger_osssoldservices.osssoldservicesid = vtiger_crmentity.crmid 
			WHERE vtiger_crmentity.deleted = ? && serviceid = ? && vtiger_crmentity.createdtime BETWEEN ? AND ?;";
		$params = array(0, $this->service, $this->time['start'], $this->time['end']);
		$result = $adb->pquery($sql, $params, true);
		$all = 0;
		$sum = 0;
		$countResult = $adb->num_rows($result);
		for ($i = 0; $i < $countResult; $i++) {
			$sum+= $adb->query_result_raw($result, $i, 'ordertime');
			$all++;
		}
		if ($all == 0) {
			return 0;
		} else {
			$result = number_format($sum / $all, 2);
			return array(
				'result_lable' => $result . ' - średni czas dostarczania usługi wyrażony w dniach',
				'result' => $result,
				'reference_lable' => $reference . ' dni',
				'reference' => $reference,
				'tolerance' => $tolerance,
				'accepted' => $accepted,
				'all' => $all,
				'maxValue' => (int) $result + 5,
			);
		}
	}

	public function get_tuatd()
	{
		$reference = 12;
		$tolerance = '2.00%';
		$maxValue = 100;
		$adb = PearDatabase::getInstance();

		$sql = "SELECT ordertime 
			FROM vtiger_troubletickets 
			INNER JOIN vtiger_crmentity ON vtiger_troubletickets.ticketid = vtiger_crmentity.crmid 
			WHERE vtiger_crmentity.deleted = ? && product_id = ? && vtiger_crmentity.createdtime BETWEEN ? AND ?;";
		$params = array(0, $this->service, $this->time['start'], $this->time['end']);
		$result = $adb->pquery($sql, $params, true);
		$all = 0;
		$accepted = 0;
		$countResult = $adb->num_rows($result);
		for ($i = 0; $i < $countResult; $i++) {
			if ($adb->query_result_raw($result, $i, 'ordertime') < $reference) {
				$accepted++;
			}
			$all++;
		}
		if ($all == 0) {
			return 0;
		} else {
			$result = number_format($accepted / $all * 100, 2);
			return array(
				'result_lable' => $result . '%',
				'result' => $result,
				'reference_lable' => "100% (max $reference dni)",
				'reference' => $reference,
				'tolerance' => $tolerance,
				'accepted' => $accepted,
				'all' => $all,
				'maxValue' => $maxValue,
			);
		}
	}

	public function get_cukapu()
	{
		$reference = 12;
		$tolerance = '2 godziny';
		$maxValue = 100;
		$adb = PearDatabase::getInstance();

		$sql = "SELECT ordertime 
			FROM vtiger_troubletickets 
			INNER JOIN vtiger_crmentity ON vtiger_troubletickets.ticketid = vtiger_crmentity.crmid 
			WHERE vtiger_crmentity.deleted = ? && product_id = ? && priority IN ('High','Urgent') && vtiger_crmentity.createdtime BETWEEN ? AND ?;";
		$params = array(0, $this->service, $this->time['start'], $this->time['end']);
		$result = $adb->pquery($sql, $params, true);
		$all = 0;
		$accepted = 0;
		$countResult = $adb->num_rows($result);
		for ($i = 0; $i < $countResult; $i++) {
			$sum+= $adb->query_result_raw($result, $i, 'ordertime');
			$all++;
		}
		if ($all == 0) {
			return 0;
		} else {
			$result = number_format($sum / $all, 2);
			return array(
				'result_lable' => $result . ' średni czas usunięcia awarii priorytetowej wyrażony w godzinach',
				'result' => $result,
				'reference_lable' => "$reference godzin (Umowa PPP 23&sect;,ust. 11)",
				'reference' => $reference,
				'tolerance' => $tolerance,
				'accepted' => $accepted,
				'all' => $all,
				'maxValue' => $maxValue,
			);
		}
	}

	public function get_cukazu()
	{
		$reference = 12;
		$tolerance = '2 godziny';
		$adb = PearDatabase::getInstance();

		$sql = "SELECT ordertime 
			FROM vtiger_troubletickets 
			INNER JOIN vtiger_crmentity ON vtiger_troubletickets.ticketid = vtiger_crmentity.crmid 
			WHERE vtiger_crmentity.deleted = ? && product_id = ? && priority IN ('Normal','Low') && vtiger_crmentity.createdtime BETWEEN ? AND ?;";
		$params = array(0, $this->service, $this->time['start'], $this->time['end']);
		$result = $adb->pquery($sql, $params, true);
		$all = 0;
		$accepted = 0;
		$countResult = $adb->num_rows($result);
		for ($i = 0; $i < $countResult; $i++) {
			$sum+= $adb->query_result_raw($result, $i, 'ordertime');
			$all++;
		}
		if ($all == 0) {
			return 0;
		} else {
			$result = number_format($sum / $all, 2);
			return array(
				'result_lable' => $result . ' średni czas usunięcia awarii zwykłej wyrażony w godzinach',
				'result' => $result,
				'reference_lable' => "$reference godzin (Umowa PPP 23&sect;,ust. 11)",
				'reference' => $reference,
				'tolerance' => $tolerance,
				'accepted' => $accepted,
				'all' => $all,
				'maxValue' => (int) $result + 5,
			);
		}
	}

	public function get_la100u()
	{
		$reference = 2;
		$tolerance = '0';
		$adb = PearDatabase::getInstance();

		$sql = "SELECT COUNT(ticketid) 
			FROM vtiger_troubletickets 
			INNER JOIN vtiger_crmentity ON vtiger_troubletickets.ticketid = vtiger_crmentity.crmid 
			WHERE vtiger_crmentity.deleted = ? && status = 'Closed' && pssold_id = IN 
				(SELECT osssoldservicesid FROM vtiger_osssoldservices 
				INNER JOIN vtiger_crmentity ON vtiger_osssoldservices.osssoldservicesid = vtiger_crmentity.crmid 
				WHERE vtiger_crmentity.deleted = ? && serviceid = ? ORDER BY vtiger_crmentity.createdtime DESC LIMIT 100) 
			AND vtiger_crmentity.createdtime BETWEEN ? AND ?;";
		$params = array(0, 0, $this->service, $this->time['start'], $this->time['end']);
		$result = $adb->pquery($sql, $params, true);
		$all = 0;
		$accepted = 0;
		$countResult = $adb->num_rows($result);
		for ($i = 0; $i < $countResult; $i++) {
			$sum+= $adb->query_result_raw($result, $i, 'ordertime');
			$all++;
		}
		if ($all == 0) {
			return 0;
		} else {
			$result = number_format($sum / $all, 2);
			return array(
				'result_lable' => $result . ' liczba awarii na 100 usług dzierżawy transmisji',
				'result' => $result,
				'reference_lable' => $reference,
				'reference' => $reference,
				'tolerance' => $tolerance,
				'accepted' => $accepted,
				'all' => $all,
				'maxValue' => (int) $result + 5,
			);
		}
	}
}
