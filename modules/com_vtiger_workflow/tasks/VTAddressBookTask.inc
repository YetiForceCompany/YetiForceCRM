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
require_once('modules/com_vtiger_workflow/VTEntityCache.inc');
require_once('modules/com_vtiger_workflow/VTWorkflowUtils.php');
require_once('modules/Users/Users.php');
class VTAddressBookTask extends VTTask {
	// Sending email takes more time, this should be handled via queue all the time.
	public $executeImmediately = false; 

	public function getFieldNames(){
		return array('test');
	}
	public function doTask($entity){
		global $adb, $current_user;
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$userID = $currentUserModel->getId();
		
		$util = new VTWorkflowUtils();
		$admin = $util->adminUser();
		$ws_id = $entity->getId();
		$module = $entity->getModuleName();
		$parts = explode('x', $ws_id);
		$entityId = $parts[1];
		$contactName = $entity->get('firstname').' '.$entity->get('lastname');

		$org_current_user = $current_user;
		$result_users = $adb->query( 'SELECT id FROM vtiger_users;');
		$allUsers = array();
        for($i = 0; $i < $adb->num_rows($result_users); $i++){
            $allUsers[] = $adb->query_result($result_users, $i, 'id');
        }
		$userInstance = new Users();
		$users = '';
		foreach ($allUsers as $user){
			$current_user = $userInstance->retrieveCurrentUserInfoFromFile( $user );
			if( Users_Privileges_Model::isPermitted($module, 'DetailView', $entityId) ){
				$users .= ','.$user;
			}
		}
		
		$adb->pquery( 'DELETE FROM vtiger_contactsbookmails WHERE contactid = ?', array($entityId));
		$result = $adb->pquery( 'SELECT fieldname FROM vtiger_field WHERE tabid = ? AND uitype = ?;', array( Vtiger_Functions::getModuleId($module), 13));
        for($i = 0; $i < $adb->num_rows($result); $i++){
            $fieldname = $adb->query_result($result, $i, 'fieldname');
			if( $entity->get($fieldname) != '' )
				$adb->pquery( 'INSERT INTO vtiger_contactsbookmails (`contactid`, `email`, `name`, `users`) VALUES (?, ?, ?, ?);', array($entityId, $entity->get($fieldname), $contactName, trim($users, ',')));
        }
		OSSMail_Module_Model::createBookMailsFiles();
		$current_user = $org_current_user;
	}
	
	/**
	 * Function to get contents of this task
	 * @param <Object> $entity
	 * @return <Array> contents
	 */
	public function getContents($entity, $entityCache=false) {
		$this->contents = true;
		return $this->contents;
	}
}