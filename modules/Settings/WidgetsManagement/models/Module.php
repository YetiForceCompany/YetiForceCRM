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
class Settings_WidgetsManagement_Module_Model extends Settings_Vtiger_Module_Model {
	
	public function getAuthorization(){
		global $log;
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::getAuthorization() method ...");
		$db = PearDatabase::getInstance();
			
		$sql = "SELECT * FROM `vtiger_role`;";
		$result = $db->query( $sql, true );
		
		$roleData = array();
		
		if ( $db->num_rows( $result ) == 0 )
			return false;
		for($i=0; $i<$db->num_rows( $result );$i++){
			$roleId = $db->query_result( $result, $i, 'roleid' );
			$roleData[$roleId]['authorizedid']                 = $roleId;
			$roleData[$roleId]['authorizedname']               = $db->query_result( $result, $i, 'rolename' );
			$roleData[$roleId]['parentrole']             = $db->query_result( $result, $i, 'parentrole' );
			$roleData[$roleId]['depth']                  = $db->query_result( $result, $i, 'depth' );
			$roleData[$roleId]['allowassignedrecordsto'] = $db->query_result( $result, $i, 'allowassignedrecordsto' );
		}
		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::getAuthorization() method ...");
		return $roleData;
	}

	public function getSelectableDashboard() {
		global $log;
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::getSelectableDashboard() method ...");
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$sql = 'SELECT * FROM vtiger_links WHERE linktype = ?';
		$params = array('DASHBOARDWIDGET');
		

		$result = $db->pquery($sql, $params);

		$widgets = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$moduleName = Vtiger_Functions::getModuleName($row['tabid']);
			
			if($moduleName != 'Home' && ($row['linklabel'] == 'Mini List' || $row['linklabel'] == 'Notebook')) 
				continue;
			if($row['linklabel'] == 'Tag Cloud') {
				$isTagCloudExists = getTagCloudView($currentUser->getId());
				if($isTagCloudExists == 'false') {
					continue;
				}
			}
			$moduleName = Vtiger_Functions::getModuleName($row['tabid']) ;
			$widgets[$moduleName][] = Vtiger_Widget_Model::getInstanceFromValues($row);
		}
		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::getSelectableDashboard() method ...");
		return $widgets;
	}

	
	/**
	* Save data
	* @param Array $data
	* @param String $moduleName
	* @return Array(success:true/false)
	**/
	function saveDetails($data, $moduleName){
		global $log;
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::saveDetails(".$data.", ".$moduleName.") method ...");
		$adb = PearDatabase::getInstance();
		$tabId = getTabid($moduleName);
		try{
			$query='SELECT * FROM `vtiger_module_dashboard` WHERE `id` = ? LIMIT 1; ';
			$params = array($data['id']);

			$result = $adb->pquery($query, $params);
		} catch (Exception $e) {
			return array('success'=>false,'message'=>$e->getMessage());
		}
		if( $adb->num_rows( $result ) > 0){
			try{
				if($data['isdefault'])
					$active = 1;
				$query = 'UPDATE `vtiger_module_dashboard` SET `isdefault` = ? WHERE `id` = ? ;';
				$params = array($data['isdefault'], $data['id']);
				$adb->pquery($query, $params);
				
				$query = 'UPDATE `vtiger_module_dashboard_widgets` SET `isdefault` = ? ';
				$params = array($data['isdefault']);
				if($active){
					$query .= ', `active` = ? ';
					$params[] = $active;
				}
				$query .= ' WHERE `templateid` = ? ;';
				$params[] = $data['id'];
				$adb->pquery($query, $params);
				
			} catch (Exception $e) {
				return array('success'=>false,'message'=>$e->getMessage());
			}
		}
		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::saveData() method ...");
		return array('success'=>true);
	}
	
	public function addBlock($data, $moduleName){
		global $log;
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::addBlock(".$data.", ".$moduleName.") method ...");
		$adb = PearDatabase::getInstance();
		$tabId = getTabid($moduleName);
		$query='INSERT INTO vtiger_module_dashboard_blocks (`authorized`, `tabid`) VALUES (?, ?);';
		$params = array($data['authorized'], $tabId);
		$adb->pquery($query,$params);
		$blockId = $adb->getLastInsertID(); 
		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::addBlock() method ...");
		return array('success'=>true, 'id'=>$blockId);
	}
	
	public function addWidget($data, $moduleName){
		global $log;
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::addWidget(".$data.", ".$moduleName.") method ...");
		$adb = PearDatabase::getInstance();
		$query='INSERT INTO vtiger_module_dashboard(linkid, blockid, filterid, title, data, isdefault) VALUES(?,?,?,?,?,?);';
		if($data['isdefault'] != 1 || $data['isdefault'] != '1')
			$data['isdefault'] = 0;
		$params = array($data['linkid'], $data['blockid'], $data['filterid'], $data['title'], $data['data'], $data['isdefault']);
		$adb->pquery($query,$params,true);
		$widgetId = $adb->getLastInsertID(); 

		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::addWidget() method ...");
		return array('success'=>true, 'id'=>$widgetId);
	}
	
	public function getBlocksId(){
		global $log;
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::getBlocksId() method ...");
		$adb = PearDatabase::getInstance();
		$data = array();
		$query='SELECT 
				  `vtiger_module_dashboard_blocks`.* , `vtiger_role`.`rolename` 
				FROM
				  `vtiger_module_dashboard_blocks` 
				  INNER JOIN `vtiger_role` 
				  ON `vtiger_module_dashboard_blocks`.`authorized` = `vtiger_role`.`roleid`;';
		$result = $adb->query($query);
		for ( $i=0; $i<$adb->num_rows( $result ); $i++ ) {
			$blockId = $adb->query_result($result, $i, 'id');
			$authorizedName = $adb->query_result($result, $i, 'rolename');
			$tabId = $adb->query_result($result, $i, 'tabid');
			$authorized = $adb->query_result($result, $i, 'authorized');
			$moduleName = Vtiger_Functions::getModuleName($tabId);
			$data[$moduleName][$blockId]['name'] = $authorizedName;
			$data[$moduleName][$blockId]['code'] = $authorized;
		}
		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::getBlocksId() method ...");
		return $data;
	}
	
	public function getBlocksFromModule($moduleName, $authorized = ''){
		global $log;
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::getBlocksFromModule(".$moduleName.", ".$authorized.") method ...");
		$adb = PearDatabase::getInstance();
		$tabId = getTabid($moduleName);
		$data = array();
		$query='SELECT * FROM `vtiger_module_dashboard_blocks` WHERE `tabid` = ?';
		$params = array($tabId);
		if($authorized){
			$query .= ' AND `authorized` = ? ;';
			$params[] = $authorized;
		}
		$result = $adb->pquery($query, $params);
		for ( $i=0; $i<$adb->num_rows( $result ); $i++ ) {
			$blockId = $adb->query_result($result, $i, 'id');
			$tabId = $adb->query_result($result, $i, 'tabid');
			$authorized = $adb->query_result($result, $i, 'authorized');
			$data[$authorized] = $blockId;
		}
		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::getBlocksFromModule() method ...");
		return $data;
	}
	
	public function getSpecialWidgets($moduleName){
		global $log;
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::getSpecialWidgets(".$moduleName.") method ...");
		$adb = PearDatabase::getInstance();
		$tabId = getTabid($moduleName);
		$query='SELECT * FROM `vtiger_links` WHERE `tabid` = ? AND linklabel IN (?, ?)';
		$params = array($tabId, 'Mini List', 'Notebook');
		$result = $adb->pquery($query, $params);
		$widgets = array();
		for($i=0; $i<$adb->num_rows($result); $i++) {
			$row = $adb->query_result_rowdata($result, $i);
			$widgets[$row['linklabel']] = Vtiger_Widget_Model::getInstanceFromValues($row);
		}
		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::getSpecialWidgets() method ...");
		return $widgets;
	}
	/**
	* Gets all id widgets for the module
	* @param String $moduleName
	* @return Array
	**/
	public function getDashboardForModule($moduleName){
		global $log;
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::getDashboardForModule(".$moduleName.") method ...");
		$adb = PearDatabase::getInstance();
		$tabId = getTabid($moduleName);
		$data = array();
		
		//$data = self::getBlocksId();
		
		$query='SELECT 
				  mdw.blockid,
				  mdw.data,
				  mdw.title,
				  mdw.filterid,
				  mdw.id,
				  mdw.isdefault,
				  `vtiger_links`.*,
				  `mdb`.`authorized`
				FROM
				  `vtiger_module_dashboard` AS mdw 
				  INNER JOIN `vtiger_links` 
					ON `mdw`.`linkid` = `vtiger_links`.`linkid` 
				  INNER JOIN `vtiger_module_dashboard_blocks` AS mdb 
					ON (`mdw`.`blockid` = `mdb`.`id` AND `vtiger_links`.`tabid` = `mdb`.`tabid`)
				WHERE `vtiger_links`.`tabid` = ?';
		$params = array($tabId);
		$result = $adb->pquery($query, $params);
		$num = $adb->num_rows( $result );
		$userId = '';
		$blockId = '';
		for ( $i=0; $i<$num; $i++ ) {
			$row = $adb->query_result_rowdata($result, $i);
			if($row['linklabel'] == 'Mini List'){
				$minilistWidget = Vtiger_Widget_Model::getInstanceFromValues($row);
				$minilistWidgetModel = new Vtiger_MiniList_Model();
				$minilistWidgetModel->setWidgetModel($minilistWidget);
				$minilistWidget->set('title', $minilistWidgetModel->getTitle());
				$data[$row['blockid']][$i] = $minilistWidget;
			} else
				$data[$row['blockid']][$i] = Vtiger_Widget_Model::getInstanceFromValues($row);
		}
		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::getDashboardForModule() method ...");
		return $data;
	}
	
	public function removeWidget($data){
		global $log;
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::removeWidget(".$data.") method ...");
		$adb = PearDatabase::getInstance();
		$query='DELETE FROM vtiger_module_dashboard WHERE vtiger_module_dashboard.id = ?;';
		$params = array($data['id']);
		$adb->pquery($query,$params);
		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::removeWidget() method ...");
		return array('success'=>true); 
	}
	
	public function removeBlock($data){
		global $log;
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::removeBlock(".$data.") method ...");
		$adb = PearDatabase::getInstance();
		$query='DELETE FROM `vtiger_module_dashboard_blocks` WHERE vtiger_module_dashboard_blocks.id = ?;';
		$params = array($data['blockid']);
		$adb->pquery($query,$params);
		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::removeBlock() method ...");
		return array('success'=>true); 
	}
}
