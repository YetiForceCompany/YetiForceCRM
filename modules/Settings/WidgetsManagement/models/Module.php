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
	
	public function getRole(){
		global $log;
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::getRole() method ...");
		$db = PearDatabase::getInstance();
        
        $sql = "SELECT * FROM `vtiger_role`;";
        $result = $db->query( $sql, true );
        
        $roleData = array();
        
        if ( $db->num_rows( $result ) == 0 )
            return false;
		for($i=0; $i<$db->num_rows( $result );$i++){
			$roleData[$i]['roleid']                 = $db->query_result( $result, $i, 'roleid' );
			$roleData[$i]['rolename']               = $db->query_result( $result, $i, 'rolename' );
			$roleData[$i]['parentrole']             = $db->query_result( $result, $i, 'parentrole' );
			$roleData[$i]['depth']                  = $db->query_result( $result, $i, 'depth' );
			$roleData[$i]['allowassignedrecordsto'] = $db->query_result( $result, $i, 'allowassignedrecordsto' );
        }
		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::getRole() method ...");
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

	public function setInactiveWidgets($widgetsToRole, $oldWidgetsToRole){
		global $log;
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::inactiveWidgets(".$widgetsToRole."".$oldWidgetsToRole.") method ...");
		$adb = PearDatabase::getInstance();
		self::removeWidgets($oldWidgetsToRole, 'inactive');
		foreach($widgetsToRole as $role=>$widgets){
			foreach($widgets as $widget){
				$query='SELECT * from vtiger_links WHERE linkid = ?;';
				$result = $adb->pquery($query, array($widget),true);
				if($adb->num_rows( $result ) > 0){
					$data = $adb->query_result( $result, 0, 'linkdata' );
					if($data){
						$data = Zend_Json::decode(html_entity_decode($data));
					}
					if(!is_array($data['roles']) && !$data['roles'])
						$data['roles'] = array();
					$data['inactive'] = 1;
					if(!in_array($role, $data['roles']))
						$data['roles'][] = $role;
					$adb->pquery('UPDATE vtiger_links SET linkdata = ? WHERE linkid = ?;',array(Zend_Json::encode($data), $widget));
				
					//
					$query='SELECT * from vtiger_module_dashboard_widgets WHERE linkid = ?;';
					$result = $adb->pquery($query, array($widget),true);
					for($i=0;$i<$adb->num_rows( $result ); $i++){
						$userId = $adb->query_result( $result, $i, 'userid' );
						$sql='SELECT * from vtiger_user2role WHERE userid = ?;';
						$resultSql = $adb->pquery($sql, array($userId),true);
						if($adb->num_rows( $resultSql ) > 0){
							$roleId = $adb->query_result( $resultSql, 0, 'roleid' );
							if($roleId == $role)
								$adb->pquery("DELETE FROM vtiger_module_dashboard_widgets WHERE linkid = ? AND userid = ? ", array($widget, $userId));
						}
					}
				}
			}
		}
		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::setInactiveWidgets() method ...");
        return true;
	}
	
	public function removeWidgets($oldWidgetsToRole, $overlap){
		global $log;
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::removeWidgets(".$oldWidgetsToRole.", " .$overlap. ") method ...");
		$adb = PearDatabase::getInstance();
		$done = array();
		
		foreach($oldWidgetsToRole as $role=>$widgets){
			$rolesId = array();
			$query='SELECT userid FROM `vtiger_user2role` WHERE roleid = ?;';
			$result = $adb->pquery($query, array($role),true);
			for($i=0;$i<$adb->num_rows( $result );$i++){
				$rolesId[] = $adb->query_result( $result, $i, 'userid' );
			}
			foreach($widgets as $widget){
				if($overlap == 'mandatory')
					$adb->pquery('UPDATE vtiger_module_dashboard_widgets SET isdefault = ? WHERE linkid = ? AND userid IN ('.generateQuestionMarks($rolesId).');',array(0, $widget, $rolesId));
				if(in_array($widget, $done))
					continue;
				$query='SELECT * from vtiger_links WHERE linkid = ?;';
				$result = $adb->pquery($query, array($widget),true);
				if($adb->num_rows( $result ) > 0){
					$data = $adb->query_result( $result, 0, 'linkdata' );
					$data = Zend_Json::decode(html_entity_decode($data));
					if($overlap == 'mandatory'){
						if($data['mandatory']['roles']){
							if(in_array($role, $data['mandatory']['roles'])){
								$key = array_search($role, $data['mandatory']['roles']);
								unset($data['mandatory']['roles'][$key]);
							}
						} else {
							$data['mandatory']['roles'] = array();
						}
						$adb->pquery('UPDATE vtiger_links SET linkdata = ? WHERE linkid = ?;',array(Zend_Json::encode($data), $widget));
					} else {
						if($data['roles']){
							if(in_array($role, $data['roles'])){
								$key = array_search($role, $data['roles']);
								unset($data['roles'][$key]);
							}
						} else {
							$data['inactive'] = 0;
							$data['roles'] = array();
						}
						$adb->pquery('UPDATE vtiger_links SET linkdata = ? WHERE linkid = ?;',array(Zend_Json::encode($data), $widget));
					}
					$done[] = $widget;
				}
			}
		}
		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::removeWidgets() method ...");
	}

	function getWidgets($moduleName){
		global $log;
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::getWidgets(".$moduleName.") method ...");
		$adb = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$query='SELECT 
				  linkid,
				  linkdata
				FROM
				  `vtiger_links`  
				WHERE linktype = ? AND tabid = ?;';
		$result = $adb->pquery($query, array('DASHBOARDWIDGET', getTabid($moduleName)),true);
		$num = $adb->num_rows( $result );
		$tab = array();
		for ( $i=0; $i<$num; $i++ ) {
			$data = $adb->query_result( $result, $i, 'linkdata' );
			$data = Zend_Json::decode(html_entity_decode($data));
			if($data['inactive'] == 1){
				foreach($data['roles'] AS $role){
					$tab['inactive'][$role][] = $adb->query_result( $result, $i, 'linkid' );
				}
			}
			if($data['mandatory']['roles']){
				foreach($data['mandatory']['roles'] AS $role){
					$tab['mandatory'][$role][] = $adb->query_result( $result, $i, 'linkid' );
				}
			}
		}
		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::getWidgets() method ...");
		return $tab;
	}

	function setMandatoryWidgets($widgetsToRole, $oldWidgetsToRole){
		global $log;
		$log->debug("Entering Settings_WidgetsManagement_Module_Model::saveWidgetsManagement(".$widgetsToRole.",".$oldWidgetsToRole.") method ...");
		$adb = PearDatabase::getInstance();
		self::removeWidgets($oldWidgetsToRole, 'mandatory');
		$users = Users_Record_Model::getAll();
		if(!is_array($widgetsToRole))
			$widgetsToRole = array();
		$i = 0;
		
		foreach($users as $user){
			$roleUser = $user->getRole();
			if(array_key_exists($roleUser, $widgetsToRole)){
				$userId = $user->getId();
				foreach($widgetsToRole as $role=>$widgets){
					foreach($widgets as $widget){
						if($i == 0){
							$query='SELECT * from vtiger_links WHERE linkid = ?;';
							$result = $adb->pquery($query, array($widget),true);
							if($adb->num_rows( $result ) > 0){
								$data = $adb->query_result( $result, 0, 'linkdata' );
								if($data){
									$data = Zend_Json::decode(html_entity_decode($data));
								}
								if(!is_array($data['mandatory']) && !$data['mandatory'])
									$data['mandatory']['roles'] = array();
								if(!in_array($role, $data['mandatory']['roles']))
									$data['mandatory']['roles'][] = $role;
								$adb->pquery('UPDATE vtiger_links SET linkdata = ? WHERE linkid = ?;',array(Zend_Json::encode($data), $widget));
							}
						}
						if($roleUser == $role){
							$query='SELECT * from vtiger_module_dashboard_widgets WHERE userid = ? AND linkid = ?;';
							$result = $adb->pquery($query, array($userId, $widget),true);
							if($adb->num_rows( $result ) == 0){
								$adb->pquery('INSERT INTO vtiger_module_dashboard_widgets(linkid, userid, isdefault) VALUES(?, ?, ?)',array($widget, $userId, 1));
							}
							else
								$adb->pquery('UPDATE vtiger_module_dashboard_widgets SET isdefault = ? WHERE linkid = ? AND userid = ?',array(1, $widget, $userId));
						}
					}
				}
				$i++;
			}
		}
		$log->debug("Exiting Settings_WidgetsManagement_Module_Model::setMandatoryWidgets() method ...");
        return true;;
	}

}
