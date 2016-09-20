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
class CallListener{
	public $restler;
	public $userID;
	public $debug = true;
	public $mobileKeysName = 'callListener';
	public $permittedActions = array('addCallActions');
    function post($type = '', $authorization = '', $data = ''){
		$authorization = json_decode($authorization);
		$adb = PearDatabase::getInstance(); $log = vglobal('log');
		$log->debug("Entering " . __CLASS__ . "::" . __METHOD__ . "| user id: ".$this->userID);
		if( $authorization->phoneKey == '' || !$this->checkPermissions($authorization) ){
			$resultData = Array('status' => 0,'message' =>  'No permission to: PushCall');
		}elseif( in_array($type,$this->permittedActions) ){
			$resultData = $this->$type($data);
		}else{
			$resultData = Array('status' => 0,'message' =>  'Method not found: '.$type);
		}
		if($this->debug){
			$file = 'api/mobile_services_'. __CLASS__ .'_logs.txt';
			$test = print_r( array('respons' => $resultData, 'request' => $data ),true);
			file_put_contents($file,'-----> '.date("Y-m-d H:i:s").' <-----'.PHP_EOL.$test.PHP_EOL,FILE_APPEND | LOCK_EX);
		}
		$log->debug("Exiting " . __CLASS__ . "::" . __METHOD__ . " | return(".print_r( $resultData,true));
		return $resultData;
    }
	public function addCallActions($data){
		$adb = PearDatabase::getInstance(); $log = vglobal('log');
		$log->debug("Entering " . __CLASS__ . "::" . __METHOD__ . "| user id: ".$this->userID);
		$data = json_decode($data);
		$params = array($this->userID, $data->callActions->to_number, $this->getDirection($data->callActions->direction), $this->getStatus($data->callActions->action), $data->callActions->timestamp);
		$adb->pquery("INSERT INTO yetiforce_mobile_calllistener (`user`,`number`,`direction`,`status`,`time`) VALUES (?,?,?,?,?);",$params);
		$log->debug("Exiting " . __CLASS__ . "::" . __METHOD__ . " | return(".print_r( $resultData,true));
		return array('status' => 1);
	}
	public function checkPermissions($authorization){
		$adb = PearDatabase::getInstance(); $log = vglobal('log');
		$log->debug("Entering " . __CLASS__ . "::" . __METHOD__ . "| ".print_r( $authorization,true));
		$return = false;	
		$result = $adb->pquery("SELECT yetiforce_mobile_keys.user FROM yetiforce_mobile_keys INNER JOIN vtiger_users ON vtiger_users.id = yetiforce_mobile_keys.user WHERE service = ? && `key` = ? && vtiger_users.user_name = ?",array($this->mobileKeysName, $authorization->phoneKey, $authorization->userName));
		if($adb->num_rows($result) > 0 ){
			$this->userID = $adb->query_result_raw($result, 0, 'user');
			$return = true;	
		}
		$log->debug("Exiting " . __CLASS__ . "::" . __METHOD__ . " | return(".$return);
		return $return;
	}
	public function getDirection($type){
		$types = array(
			'incoming' => 0,
			'outgoing' => 1,
		);
		return $types[$type];
	}
	public function getStatus($type){
		$types = array(
			'ringing' => 0,
			'call' => 1,
			'hanged' => 2,
			'rejected' => 3,
		);
		return $types[$type];
	}
}
