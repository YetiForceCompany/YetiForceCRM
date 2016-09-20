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
class HistoryCall{
    public $restler;
	public $userID;
	public $debug = true;
	public $permittedActions = array('addCallLogs');
	public $types = array(
		'1' => 'Incoming',
		'2' => 'Outgoing',
		'3' => 'Missed',
		'4' => 'Voicemail',
	);
	public $outgoingStatus = array(
		0 => 'Outgoing missed',
		1 => 'Outgoing received',
	);
	
    function post($type = '', $authorization = '', $data = ''){
		$authorization = json_decode($authorization);
		$adb = PearDatabase::getInstance(); $log = vglobal('log');
		$log->info("Start HistoryCall metod");
		if( $authorization->phoneKey == '' || !$this->checkPermissions($authorization) ){
			$resultData = Array('status' => 0,'message' =>  'No permission to: HistoryCall');
		}elseif( in_array($type,$this->permittedActions) ){
			$resultData = $this->$type($data);
		}else{
			$resultData = Array('status' => 0,'message' =>  'Method not found: '.$type);
		}
		if($this->debug){
			$file = 'api/mobile_services_HistoryCall_logs.txt';
			$dane = print_r( array( $type,$authorization, $data, $resultData ) ,true);
			file_put_contents($file,'-----> '.date("Y-m-d H:i:s").' <-----'.PHP_EOL.$dane.PHP_EOL,FILE_APPEND | LOCK_EX);
		}
        return $resultData;
    }
	
	public function addCallLogs($data){
		$adb = PearDatabase::getInstance(); $log = vglobal('log');
		include_once 'include/main/WebUI.php';
		$log->info("Start HistoryCall::addCallLogs | user id: ".$this->userID);
		$resultData = array('status' => 2);
		$user = new Users();
		$count = 0;
		$current_user = $user->retrieveCurrentUserInfoFromFile(Users::getActiveAdminId());
		$data = json_decode($data);

		foreach ($data->callLogs as $call) {
			$to_number = $call->to_number;
			$from_number = $data->phoneNumber;
			$destination = $this->findPhoneNumber($to_number);
			
			$CallHistory = CRMEntity::getInstance('CallHistory');
			$CallHistory->column_fields['assigned_user_id'] =  $this->userID;
			$CallHistory->column_fields['callhistorytype'] = $this->getType( $call->type , $call->duration);
			$CallHistory->column_fields['country'] = $call->country_iso;
			$CallHistory->column_fields['to_number'] = $to_number;
			$CallHistory->column_fields['from_number'] = $from_number;
			$CallHistory->column_fields['location'] = $call->location;
			$CallHistory->column_fields['phonecallid'] = $call->callid;
			$CallHistory->column_fields['start_time'] = $this->getDate($call->start_time);
			$CallHistory->column_fields['end_time'] = $this->getDate($call->end_time);
			$CallHistory->column_fields['duration'] = $call->duration;
			$CallHistory->column_fields['imei'] = $data->imei;
			$CallHistory->column_fields['ipAddress'] = $data->ipAddress;
			$CallHistory->column_fields['simSerial'] = $data->simSerial;
			$CallHistory->column_fields['subscriberId'] = $data->subscriberId;
			if($destination)
				$CallHistory->column_fields['destination'] = $destination;
			$CallHistory->save('CallHistory');
			$count++;
		}
		$resultData = array('status' => 1, 'count' => $count);
		$log->info("End HistoryCall::addCallLogs | return: ".print_r( $resultData,true));
		return $resultData;
	}
	
	public function checkPermissions($authorization){
		$adb = PearDatabase::getInstance(); $log = vglobal('log');
		$log->info("Start HistoryCall::checkPermissions | ".print_r( $authorization,true));
		$return = false;	
		$result = $adb->pquery("SELECT yetiforce_mobile_keys.user FROM yetiforce_mobile_keys INNER JOIN vtiger_users ON vtiger_users.id = yetiforce_mobile_keys.user WHERE service = ? && `key` = ? && vtiger_users.user_name = ?",array('historycall', $authorization->phoneKey, $authorization->userName),true);
		if($adb->num_rows($result) > 0 ){
			$this->userID = $adb->query_result_raw($result, 0, 'user');
			$return = true;	
		}
		$log->info("End HistoryCall::checkPermissions | return: ".$return);
		return $return;
	}
	
	public function findPhoneNumber($number){
		$adb = PearDatabase::getInstance(); $log = vglobal('log');
		$crmid = false;
		$modulesInstance = array();
		$sql = "SELECT columnname,tablename,vtiger_tab.name FROM vtiger_field INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_field.tabid WHERE vtiger_tab.presence = 0 && uitype = '11' && vtiger_tab.name IN ('Contacts','Accounts','Leads','OSSEmployees','Vendors')";
		$result = $adb->query($sql,true);
		for($i = 0; $i < $adb->num_rows($result); $i++){
			$module = $adb->query_result_raw($result, $i, 'name');
			$columnname = $adb->query_result_raw($result, $i, 'columnname');
			$tablename = $adb->query_result_raw($result, $i, 'tablename');
			if(!$modulesInstance[$module]){
				include_once 'modules/'.$module.'/'.$module.'.php';
				$moduleInstance = CRMEntity::getInstance($module);
				$modulesInstance[$module] = $moduleInstance->tab_name_index;
			}
			$table_index = $modulesInstance[$module][$tablename];
			if( strpos($number, '+') !== false )
				$number = substr($number, 3);
			$number = preg_replace('/\D/', '',$number);
			$sqlNumber = '';
			foreach (str_split($number) as $num) {
				$sqlNumber .= '[^0-9]*'.$num;
			}
			$sql = "SELECT crmid FROM $tablename INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = $tablename.$table_index WHERE vtiger_crmentity.deleted = 0 && $columnname RLIKE '$sqlNumber';";
			$resultData = $adb->query($sql,true);
			if($adb->num_rows($resultData) > 0 ){
				$crmid = $adb->query_result_raw($resultData, 0, 'crmid');
				break;
			}
		}
		return $crmid;
	}
	public function getType($type, $duration){
		if($type == 2){
			return $duration > 0 ? $this->outgoingStatus[1] : $this->outgoingStatus[0];
		}else{
			return !$this->types[$type]? $type : $this->types[$type];
		}
	}
	
	public function getDate($timestamp){
		$timestamp = substr($timestamp, 0, 10);
		return date("Y-m-d H:i:s", $timestamp);
	}
}
