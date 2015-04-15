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
	  
class Settings_MarketingProcesses_Processes_Model extends Vtiger_Base_Model{
	
	/**
	 * Function to get all the values of the Object
	 * @return Array (key-value mapping)
	 */
	public function getInstance($moduleName = ''){
		global $log, $adb;
		$log->debug('Entering Settings_MarketingProcesses_Processes_Model::getInstance() method ...');		
		$values = [];
		if($moduleName){
			$sql = 'SELECT * FROM vtiger_marketing_processes WHERE `module_id` = ? LIMIT 1';
			$params[] = getTabid($moduleName);
			$result = $adb->pquery($sql, $params);
			
			if($adb->num_rows($result)== 1) {
				$row = $adb->query_result_rowdata($result, $i);
				$values = Zend_Json::decode(html_entity_decode($row['data']));
			}
		}
		$this->valueMap = $values;
		$log->debug("Exiting Settings_MarketingProcesses_Processes_Model::getInstance() method ...");
		return $this;
	}
	public function getGroups(){
		global $log;
		$log->debug('Entering Settings_MarketingProcesses_Processes_Model::getGroups() method ...');
		if(!$this->has('groups')){
			$log->debug("Exiting Settings_MarketingProcesses_Processes_Model::getGroups() method ...");
			return [];
		}elseif(!is_array($this->get('groups'))){
			$this->set('groups',[$this->get('groups')]);
		}
		return $this->get('groups');
		$log->debug("Exiting Settings_MarketingProcesses_Processes_Model::getGroups() method ...");
	}
	public function getLeadStatus(){
		global $log;
		$log->debug('Entering Settings_MarketingProcesses_Processes_Model::getLeadStatus() method ...');
		if(!$this->has('leadstatus')){
			$log->debug("Exiting Settings_MarketingProcesses_Processes_Model::getLeadStatus() method ...");
			return [];
		}elseif(!is_array($this->get('leadstatus'))){
			$this->set('leadstatus',[$this->get('leadstatus')]);
		}
		return $this->get('leadstatus');
		$log->debug("Exiting Settings_MarketingProcesses_Processes_Model::getLeadStatus() method ...");
	}
	
	/**
	 * Saves state data for convert to account
	 * @param <String> $state 
	 * @return array 
	 */
	function save() {
		global $log, $adb;
		$log->debug('Entering Settings_MarketingProcesses_Processes_Model::save() method ...');
		if(!$this->getData()){
			$log->debug("Exiting Settings_MarketingProcesses_Processes_Model::save() method ...");
			return FALSE;
		}
		$tabid = getTabid($this->get('module'));
		$query = 'SELECT * FROM vtiger_marketing_processes WHERE `module_id` = ?';
		$result = $adb->pquery($query, [$tabid]);
		$data = Zend_Json::encode($this->getData());
		if($adb->num_rows($result) > 0) {
			$query = 'UPDATE vtiger_marketing_processes SET `data` = ? WHERE `module_id` = ?';
			$params = [$data, $tabid];
		}else{
			$query = 'INSERT INTO vtiger_marketing_processes (`module_id`,`data`) VALUES(?,?)';
			$params = array($tabid, $data);
		}
		$log->debug("Exiting Settings_MarketingProcesses_Processes_Model::save() method ...");
		return $adb->pquery($query, $params);
	}
}