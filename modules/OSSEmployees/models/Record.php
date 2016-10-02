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

class OSSEmployees_Record_Model extends Vtiger_Record_Model
{

	/**
	 * Function returns the details of Employees Hierarchy
	 * @return <Array>
	 */
	public function getEmployeeHierarchy()
	{
		$focus = CRMEntity::getInstance($this->getModuleName());
		$hierarchy = $focus->getEmployeeHierarchy($this->getId());
		$i = 0;
		foreach ($hierarchy['entries'] as $employeeId => $employeeInfo) {
			preg_match('/<a href="+/', $employeeInfo[0], $matches);
			if ($matches != null) {
				preg_match('/[.\s]+/', $employeeInfo[0], $dashes);
				preg_match("/<a(.*)>(.*)<\/a>/i", $employeeInfo[0], $name);

				$recordModel = Vtiger_Record_Model::getCleanInstance('OSSEmployees');
				$recordModel->setId($employeeId);
				$hierarchy['entries'][$employeeId][0] = $dashes[0] . "<a href=" . $recordModel->getDetailViewUrl() . ">" . $name[2] . "</a>";
			}
		}
		return $hierarchy;
	}

	public function getHolidaysEntitlement($recordId, $year, $list = false)
	{
		$adb = PearDatabase::getInstance();
		$sql = "SELECT * FROM vtiger_ossholidaysentitlement WHERE ossemployeesid= $recordId ";
		if (!$list)
			$sql .= "AND year = $year;";
		$parametry = array();
		$result = $adb->pquery($sql, $parametry, true);
		if ($list) {
			$years = array();
			$num = $adb->num_rows($result);
			for ($i = 0; $i < $num; $i++) {
				$years[$i] = $adb->query_result($result, $i, 'year');
			}
			return $years;
		}
		return $result->fields['days'];
	}

	public function yearExist($recordId, $year)
	{
		$adb = PearDatabase::getInstance();
		$sql = "SELECT year FROM vtiger_ossholidaysentitlement WHERE ossemployeesid= $recordId ";
		$parametry = array();
		$result = $adb->pquery($sql, $parametry, true);
		$num = $adb->num_rows($result);
		for ($i = 0; $i < $num; $i++) {
			if ($year == $adb->query_result($result, $i, 'year'))
				return $year;
		}
		if ($adb->query_result($result, 0, 'year'))
			return $adb->query_result($result, 0, 'year');
		return $year;
	}

	public function getHoliday($recordId, $year)
	{
		$adb = PearDatabase::getInstance();
		$sql = "SELECT * FROM vtiger_ossholidays WHERE ossemployeesid= $recordId;";
		$parametry = array();
		$result = $adb->pquery($sql, $parametry, true);
		$allWorkDay = 0;
		$num = $adb->num_rows($result);
		for ($i = 0; $i < $num; $i++) {
			$start = $adb->query_result($result, $i, 'start_date');
			$end = $adb->query_result($result, $i, 'end_date');
			$workDay = $adb->query_result($result, $i, 'working_days');

			if (substr($start, 0, 4) == $year && substr($end, 0, 4) == $year)
				$allWorkDay += $workDay;
			elseif (substr($start, 0, 4) == $year)
				$allWorkDay += $this->workDays($start, $year . '-12-31');
			elseif (substr($start, 0, 4) == $year)
				$allWorkDay += $this->workDays($year . '-01-01', $end);
		}
		return $allWorkDay;
	}

	public function workDays($firstDate, $secondDate)
	{
		$firstDate = strtotime($firstDate);
		$secondDate = strtotime($secondDate);

		$count = 0;
		$secondDate = strtotime('+1 day', $secondDate);
		$lastYear = null;
		$hol = array('01-01', '05-01', '05-03', '08-15', '11-01', '11-11', '12-25', '12-26');
		while ($firstDate < $secondDate) {
			$year = date('Y', $firstDate);
			if ($year !== $lastYear) {
				$lastYear = $year;
				$easter = date('m-d', easter_date($year));
				$date = strtotime($year . '-' . $easter);
				$easterSec = date('m-d', strtotime('+1 day', $date));
				$cc = date('m-d', strtotime('+60 days', $date));
				$hol[8] = $easter;
				$hol[9] = $easterSec;
				$hol[10] = $cc;
			}
			$weekDay = date('w', $firstDate);
			$md = date('m-d', $firstDate);
			if (!($weekDay == 0 || $weekDay == 6 || in_array($md, $hol) || $year > 2010 && $md == '01-06')) {
				$count++;
			}
			$firstDate = strtotime('+1 day', $firstDate);
		}

		return $count;
	}

	public function checkUser($userId, $return_id = false)
	{
		$adb = PearDatabase::getInstance();
		$sql = "SELECT * FROM vtiger_crmentity WHERE smownerid = ? && setype = ? && deleted = ?;";
		$result = $adb->pquery($sql, array($userId, 'OSSEmployees', 0), true);
		$num = $adb->num_rows($result);
		if ($return_id) {
			if ($num > 0)
				return $adb->query_result($result, 0, 'crmid');
			return false;
		}else {
			if ($num > 0)
				return false;
			return true;
		}
	}

	public function getWorkTime()
	{
		$current_user = vglobal('current_user');
		$employeeID = self::checkUser($current_user->id, true);
		if (!$employeeID) {
			return '';
		}
		$moduleModel = Vtiger_Record_Model::getInstanceById($employeeID, 'OSSEmployees');
		$adb = PearDatabase::getInstance();
		$sql = "SELECT * FROM vtiger_osstimecontrol
					INNER JOIN vtiger_crmentity ON vtiger_osstimecontrol.osstimecontrolid = vtiger_crmentity.crmid
					WHERE vtiger_crmentity.setype = ? && vtiger_crmentity.smownerid = ? ";
		$sql .= "AND (vtiger_osstimecontrol.date_start = DATE(NOW()) || vtiger_osstimecontrol.due_date = DATE(NOW()))";
		$result = $adb->pquery($sql, array('OSSTimeControl', $current_user->id), true);
		$today = date('Y-m-d');
		$countResult = $adb->num_rows($result);
		for ($i = 0; $i < $countResult; $i++) {
			$date_start = $adb->query_result($result, $i, 'date_start');
			$due_date = $adb->query_result($result, $i, 'due_date');
			if ($date_start == $today && $due_date != $today) {
				$date_time = $date_start . ' ' . $adb->query_result($result, $i, 'time_start');
				$date_time2 = $date_start . ' 23:59:59';
				$sum_time += (strtotime($date_time2) - strtotime($date_time)) / 3600;
			} elseif ($date_start != $today && $due_date == $today) {
				$date_time = $due_date . ' ' . $adb->query_result($result, $i, 'time_end');
				$date_time2 = $due_date . ' 00:00:01';
				$sum_time += (strtotime($date_time) - strtotime($date_time2)) / 3600;
			} else
				$sum_time += $adb->query_result($result, $i, 'sum_time');
		}

		if ($sum_time != 0 && $sum_time != '') {
			$text = vtranslate('LBL_DAYWORKSUM', 'OSSEmployees') . ': ' . number_format($sum_time, 2, $current_user->column_fields['currency_decimal_separator'], $current_user->column_fields['currency_grouping_separator']);
			if ($moduleModel->get('dayworktime') != '') {
				$text .= ' ' . vtranslate('LBL_FROM') . ' ' . $moduleModel->get('dayworktime');
			}
			$return = '<span title="' . vtranslate('Average daily working time', 'OSSEmployees') . '">' . $text . '</span>';
		}
		return $return;
	}
}
