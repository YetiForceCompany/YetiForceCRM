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
class PushCall{
	public $restler;
	public $userID;
	public $debug = false;
	public $permittedActions = array('getPushCallDetails');
	
	public function post($type = '', $authorization = ''){
		$authorization = json_decode($authorization);
		$adb = PearDatabase::getInstance(); $log = vglobal('log');
		$log->info('Start PushCall metod');
		if( $authorization->phoneKey == '' || !$this->checkPermissions($authorization) ){
			$resultData = Array('status' => 0,'message' =>  'No permission to: PushCall');
		}elseif( in_array($type,$this->permittedActions) ){
			$resultData = $this->$type($authorization);
		}else{
			$resultData = Array('status' => 0,'message' =>  'Method not found: '.$type);
		}
		if($this->debug){
			$file = 'api/mobile_services_PushCall_logs.txt';
			$test = print_r( array('respons' => $resultData, 'request' => $type ),true);
			file_put_contents($file,'-----> '.date("Y-m-d H:i:s").' <-----'.PHP_EOL.$test.PHP_EOL,FILE_APPEND | LOCK_EX);
		}
		$log->info('End PushCall metod | return: '.print_r( $resultData,true));
		return $resultData;
	}
	
	public function getPushCallDetails(){
		$adb = PearDatabase::getInstance(); $log = vglobal('log');
		$log->info('Start PushCall::getPushCallDetails | user id: '.$this->userID);
		$resultData = array('status' => 2);
		$result = $adb->pquery("SELECT * FROM yetiforce_mobile_pushcall WHERE user = ?",array($this->userID));
		$Num = $adb->num_rows($result);
		if($Num > 0){
			$resultData = array('status' => 1, 'phone_number' => $adb->query_result_raw($result, 0, 'number') );
			$adb->pquery("DELETE FROM yetiforce_mobile_pushcall WHERE user = ?;",array($this->userID));
		}
		$log->info('End PushCall::getPushCallDetails | return: '.print_r( $resultData,true));
		return $resultData;
	}
	
	public function checkPermissions($authorization){
		$adb = PearDatabase::getInstance(); $log = vglobal('log');
		$log->info('Start PushCall::checkPermissions | '.print_r( $authorization,true));
		$return = false;	
		$result = $adb->pquery("SELECT yetiforce_mobile_keys.user FROM yetiforce_mobile_keys INNER JOIN vtiger_users ON vtiger_users.id = yetiforce_mobile_keys.user WHERE service = ? && `key` = ? && vtiger_users.user_name = ?",array('pushcall', $authorization->phoneKey, $authorization->userName),true);
		if($adb->num_rows($result) > 0 ){
			$this->userID = $adb->query_result_raw($result, 0, 'user');
			$return = true;	
		}
		$log->info('End PushCall::checkPermissions | return: '.$return);
		return $return;
	}
}
