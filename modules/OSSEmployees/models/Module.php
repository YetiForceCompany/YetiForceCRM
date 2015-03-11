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
class OSSEmployees_Module_Model extends Vtiger_Module_Model {
	/**
	 * Function to get the Quick Links for the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$parentQuickLinks = parent::getSideBarLinks($linkParams);
	
		$quickLink = array(
			'linktype' => 'SIDEBARLINK',
			'linklabel' => 'LBL_DASHBOARD',
			'linkurl' => $this->getDashBoardUrl(),
			'linkicon' => '',
		);
	
		//Check profile permissions for Dashboards
		$moduleModel = Vtiger_Module_Model::getInstance('Dashboard');
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
		if($permission) {
			$parentQuickLinks['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
		}
	
		return $parentQuickLinks;
	}
	/**
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery) {
		return $listQuery." AND vtiger_ossemployees.employee_status = 'Employee'";
	}
	
	public function getWidgetTimeControl($user, $time, $selectedTimeTypes) {
		if(!$time){
			return array();
		}
		$db = PearDatabase::getInstance();
		$param = array('OSSTimeControl', $user, $time['start'], $time['end'] );
		$sql = "SELECT sum_time AS daytime, due_date, timecontrol_type FROM vtiger_osstimecontrol
					INNER JOIN vtiger_crmentity ON vtiger_osstimecontrol.osstimecontrolid = vtiger_crmentity.crmid
					WHERE vtiger_crmentity.setype = ? AND vtiger_crmentity.smownerid = ? ";
		$sql .= "AND (vtiger_osstimecontrol.date_start >= ? AND vtiger_osstimecontrol.due_date <= ?) AND vtiger_osstimecontrol.deleted = 0 ORDER BY due_date ";
		$result = $db->pquery( $sql, $param );
		$data = array();
		$days = array();
		$chartExist = FALSE;
		for($i=0;$i<$db->num_rows( $result );$i++){
			$due_date = $db->query_result_raw($result, $i, 'due_date');
			$daytime = $db->query_result_raw($result, $i, 'daytime');
			$timecontrol_type = $db->query_result_raw($result, $i, 'timecontrol_type');
			$due_date = DateTimeField::convertToUserFormat($due_date);			
			$data[$due_date][$timecontrol_type] += $daytime;
		}
		if(!is_array($selectedTimeTypes)){
			$selectedTimeTypes = array($selectedTimeTypes);
		} 
		foreach ($data as $key => $value) {
			if($value['PLL_BREAK_TIME'] && (in_array('breakTime', $selectedTimeTypes) || in_array('all', $selectedTimeTypes))){
				$chartData['PLL_BREAK_TIME'][] = $value['PLL_BREAK_TIME'];
				$chartExist = TRUE;
			}elseif(in_array('breakTime', $selectedTimeTypes) || in_array('all', $selectedTimeTypes)){
				$chartData['PLL_BREAK_TIME'][] = 0;
			}
			if($value['PLL_WORKING_TIME'] && (in_array('workTime', $selectedTimeTypes) || in_array('all', $selectedTimeTypes))){
				$chartData['PLL_WORKING_TIME'][] = $value['PLL_WORKING_TIME'];
				$chartExist = TRUE;
			}elseif(in_array('workTime', $selectedTimeTypes) || in_array('all', $selectedTimeTypes)){
				$chartData['PLL_WORKING_TIME'][] = 0;
			}
			if($value['PLL_HOLIDAY'] && (in_array('holidayTime', $selectedTimeTypes) || in_array('all', $selectedTimeTypes))){
				$chartData['PLL_HOLIDAY_TIME'][] = $value['PLL_HOLIDAY'];
				$chartExist = TRUE;
			}elseif(in_array('holidayTime', $selectedTimeTypes) || in_array('all', $selectedTimeTypes)){
				$chartData['PLL_HOLIDAY_TIME'][] = 0;
			}
			$days[] = $key; 
		}	
			
		$numDays = count($days);
		if($chartData){
			$colors = $this->getBarChartColors($chartData);
		}
		$max = 0;
		for($i = 0; $i < $numDays; $i++){
			$sum = 0;
			if($chartData['PLL_BREAK_TIME'][$i])
				$sum += $chartData['PLL_BREAK_TIME'][$i];

			if($chartData['PLL_HOLIDAY_TIME'][$i])
				$sum += $chartData['PLL_HOLIDAY_TIME'][$i];

			if($chartData['PLL_WORKING_TIME'][$i])
				$sum += $chartData['PLL_WORKING_TIME'][$i];
					
			if($sum > $max){
				$max = $sum;
			}
		}		
	
		$yMaxValue = $max + 2 + ($max[0]/100)*25;;
		$chartData['days'] = $days;
		$chartData['yMaxValue'] = $yMaxValue; 

		return array('data' => $chartData, 'colors' => $colors, 'chartExist' => $chartExist);
	}
	
	function getWorkingDays($startDate, $endDate){
		$begin = strtotime($startDate);
		$end   = strtotime($endDate);
		if ($begin > $end) {
			return 0;
		} else {
			$no_days  = 0;
			$weekends = 0;
			while ($begin <= $end) {
				$no_days++; // no of days in the given interval
				$what_day = date("N", $begin);
				if ($what_day > 5) { // 6 and 7 are weekend days
					$weekends++;
				};
				$begin += 86400; // +1 day
			};
			$working_days = $no_days - $weekends;
			return $working_days;
		}
	}

	function getBarChartColors($chartData){
		$numSelectedTimeTypes = count($chartData);
		$i = 0;
		$colors = array( '#4bb2c5', '#EAA228', '#c5b47f');
		foreach ($chartData as $key => $value) {
			$result[$key] = $colors[$i];
			$i++;
		}
		
		return $result;
	}
}