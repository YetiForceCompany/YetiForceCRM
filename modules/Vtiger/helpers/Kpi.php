<?php

/**
 * Vtiger kpi helper class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Vtiger_Kpi_Helper
{
	public $time = false;
	public $service = false;
	public $type = false;

	/**
	 * Construct.
	 *
	 * @param \App\Request $request
	 */
	public function __construct(\App\Request $request)
	{
		$this->time = $request->get('time');
		$this->service = $request->get('service');
		$this->type = $request->get('type');
	}

	public function getKpiList()
	{
		$adb = PearDatabase::getInstance();
		$list = [];
		$sql = 'SELECT serviceid as id, servicename as name FROM vtiger_service INNER JOIN vtiger_crmentity ON vtiger_service.serviceid = vtiger_crmentity.crmid WHERE vtiger_crmentity.deleted = ? && discontinued = ?;';
		$params = [0, 1];
		$result = $adb->pquery($sql, $params, true);
		$countResult = $adb->numRows($result);
		for ($i = 0; $i < $countResult; ++$i) {
			$list[$adb->queryResultRaw($result, $i, 'id')] = $adb->queryResultRaw($result, $i, 'name');
		}

		return $list;
	}

	public function getKpiTypes()
	{
		$types = [];
		$types['Tdu'] = 'Terminowość dostarczania usługi';
		$types['Cdu'] = 'Czas dostarczania usługi';
		$types['Tuatd'] = 'Terminowość usuwania awarii';
		$types['Cukapu'] = 'Czas usuniecia każdej awarii priorytetowej dla usługi dzierżawy';
		$types['Cukazu'] = 'Czas usuniecia każdej awarii zwykłej dla usługi dzierżawy';
		$types['La100u'] = 'Liczba awarii na 100 usuługi dzierżawy';

		return $types;
	}

	public function getData()
	{
		$type = 'get' . $this->type;
		if ($this->type == '' || !method_exists('Vtiger_Kpi_Helper', $type)) {
			return;
		}

		return $this->$type();
	}

	// Data KPI
	public function getTdu()
	{
		$reference = 30;
		$tolerance = '1.00%';
		$maxValue = 100;
		$adb = PearDatabase::getInstance();

		$sql = 'SELECT ordertime
			FROM vtiger_osssoldservices
			INNER JOIN vtiger_crmentity ON vtiger_osssoldservices.osssoldservicesid = vtiger_crmentity.crmid
			WHERE vtiger_crmentity.deleted = ? && serviceid = ? && vtiger_crmentity.createdtime BETWEEN ? AND ?;';
		$params = [0, $this->service, $this->time['start'], $this->time['end']];
		$result = $adb->pquery($sql, $params, true);
		$all = 0;
		$accepted = 0;
		$countResult = $adb->numRows($result);
		for ($i = 0; $i < $countResult; ++$i) {
			if ($adb->queryResultRaw($result, $i, 'ordertime') < $reference) {
				++$accepted;
			}
			++$all;
		}
		if ($all == 0) {
			return 0;
		} else {
			$result = number_format($accepted / $all * 100, 2);

			return [
				'result_lable' => $result . ' procent terminowo dostarczonych usług w okresie raportowym',
				'result' => $result,
				'reference_lable' => "100% (max $reference dni)",
				'reference' => $reference,
				'tolerance' => $tolerance,
				'accepted' => $accepted,
				'all' => $all,
				'maxValue' => $maxValue,
			];
		}
	}

	public function getCdu()
	{
		$reference = 30;
		$tolerance = '1 dzień';
		$adb = PearDatabase::getInstance();

		$sql = 'SELECT ordertime
			FROM vtiger_osssoldservices
			INNER JOIN vtiger_crmentity ON vtiger_osssoldservices.osssoldservicesid = vtiger_crmentity.crmid
			WHERE vtiger_crmentity.deleted = ? && serviceid = ? && vtiger_crmentity.createdtime BETWEEN ? AND ?;';
		$params = [0, $this->service, $this->time['start'], $this->time['end']];
		$result = $adb->pquery($sql, $params, true);
		$all = 0;
		$sum = 0;
		$countResult = $adb->numRows($result);
		for ($i = 0; $i < $countResult; ++$i) {
			$sum += $adb->queryResultRaw($result, $i, 'ordertime');
			++$all;
		}
		if ($all == 0) {
			return 0;
		} else {
			$result = number_format($sum / $all, 2);

			return [
				'result_lable' => $result . ' - średni czas dostarczania usługi wyrażony w dniach',
				'result' => $result,
				'reference_lable' => $reference . ' dni',
				'reference' => $reference,
				'tolerance' => $tolerance,
				'accepted' => $accepted,
				'all' => $all,
				'maxValue' => (int) $result + 5,
			];
		}
	}

	public function getTuatd()
	{
		$reference = 12;
		$tolerance = '2.00%';
		$maxValue = 100;
		$adb = PearDatabase::getInstance();

		$sql = 'SELECT ordertime
			FROM vtiger_troubletickets
			INNER JOIN vtiger_crmentity ON vtiger_troubletickets.ticketid = vtiger_crmentity.crmid
			WHERE vtiger_crmentity.deleted = ? && product_id = ? && vtiger_crmentity.createdtime BETWEEN ? AND ?;';
		$params = [0, $this->service, $this->time['start'], $this->time['end']];
		$result = $adb->pquery($sql, $params, true);
		$all = 0;
		$accepted = 0;
		$countResult = $adb->numRows($result);
		for ($i = 0; $i < $countResult; ++$i) {
			if ($adb->queryResultRaw($result, $i, 'ordertime') < $reference) {
				++$accepted;
			}
			++$all;
		}
		if ($all == 0) {
			return 0;
		} else {
			$result = number_format($accepted / $all * 100, 2);

			return [
				'result_lable' => $result . '%',
				'result' => $result,
				'reference_lable' => "100% (max $reference dni)",
				'reference' => $reference,
				'tolerance' => $tolerance,
				'accepted' => $accepted,
				'all' => $all,
				'maxValue' => $maxValue,
			];
		}
	}

	public function getCukapu()
	{
		$reference = 12;
		$tolerance = '2 godziny';
		$maxValue = 100;
		$adb = PearDatabase::getInstance();

		$sql = "SELECT ordertime
			FROM vtiger_troubletickets
			INNER JOIN vtiger_crmentity ON vtiger_troubletickets.ticketid = vtiger_crmentity.crmid
			WHERE vtiger_crmentity.deleted = ? && product_id = ? && priority IN ('High','Urgent') && vtiger_crmentity.createdtime BETWEEN ? AND ?;";
		$params = [0, $this->service, $this->time['start'], $this->time['end']];
		$result = $adb->pquery($sql, $params, true);
		$all = 0;
		$accepted = 0;
		$countResult = $adb->numRows($result);
		for ($i = 0; $i < $countResult; ++$i) {
			$sum += $adb->queryResultRaw($result, $i, 'ordertime');
			++$all;
		}
		if ($all == 0) {
			return 0;
		} else {
			$result = number_format($sum / $all, 2);

			return [
				'result_lable' => $result . ' średni czas usunięcia awarii priorytetowej wyrażony w godzinach',
				'result' => $result,
				'reference_lable' => "$reference godzin (Umowa PPP 23&sect;,ust. 11)",
				'reference' => $reference,
				'tolerance' => $tolerance,
				'accepted' => $accepted,
				'all' => $all,
				'maxValue' => $maxValue,
			];
		}
	}

	public function getCukazu()
	{
		$reference = 12;
		$tolerance = '2 godziny';
		$adb = PearDatabase::getInstance();

		$sql = "SELECT ordertime
			FROM vtiger_troubletickets
			INNER JOIN vtiger_crmentity ON vtiger_troubletickets.ticketid = vtiger_crmentity.crmid
			WHERE vtiger_crmentity.deleted = ? && product_id = ? && priority IN ('Normal','Low') && vtiger_crmentity.createdtime BETWEEN ? AND ?;";
		$params = [0, $this->service, $this->time['start'], $this->time['end']];
		$result = $adb->pquery($sql, $params, true);
		$all = 0;
		$accepted = 0;
		$countResult = $adb->numRows($result);
		for ($i = 0; $i < $countResult; ++$i) {
			$sum += $adb->queryResultRaw($result, $i, 'ordertime');
			++$all;
		}
		if ($all == 0) {
			return 0;
		} else {
			$result = number_format($sum / $all, 2);

			return [
				'result_lable' => $result . ' średni czas usunięcia awarii zwykłej wyrażony w godzinach',
				'result' => $result,
				'reference_lable' => "$reference godzin (Umowa PPP 23&sect;,ust. 11)",
				'reference' => $reference,
				'tolerance' => $tolerance,
				'accepted' => $accepted,
				'all' => $all,
				'maxValue' => (int) $result + 5,
			];
		}
	}

	public function getLa100u()
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
		$params = [0, 0, $this->service, $this->time['start'], $this->time['end']];
		$result = $adb->pquery($sql, $params, true);
		$all = 0;
		$accepted = 0;
		$countResult = $adb->numRows($result);
		for ($i = 0; $i < $countResult; ++$i) {
			$sum += $adb->queryResultRaw($result, $i, 'ordertime');
			++$all;
		}
		if ($all == 0) {
			return 0;
		} else {
			$result = number_format($sum / $all, 2);

			return [
				'result_lable' => $result . ' liczba awarii na 100 usług dzierżawy transmisji',
				'result' => $result,
				'reference_lable' => $reference,
				'reference' => $reference,
				'tolerance' => $tolerance,
				'accepted' => $accepted,
				'all' => $all,
				'maxValue' => (int) $result + 5,
			];
		}
	}
}
