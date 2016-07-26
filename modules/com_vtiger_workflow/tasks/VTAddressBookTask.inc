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
require_once('modules/com_vtiger_workflow/VTEntityCache.inc');
require_once('modules/com_vtiger_workflow/VTWorkflowUtils.php');
require_once('modules/Users/Users.php');

class VTAddressBookTask extends VTTask {

	public $executeImmediately = false;
	public $tables = [ 'Contacts' => 'vtiger_contactsbookmails', 'OSSEmployees' => 'vtiger_ossemployeesbookmails',
		'Accounts' => 'vtiger_accountbookmails', 'Leads' => 'vtiger_leadbookmails',
		'Vendors' => 'vtiger_vendorbookmails'];

	public function getFieldNames() {
		return array('test');
	}

	public function doTask($entity) {
		$db = PearDatabase::getInstance();
		$current_user = vglobal('current_user');
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$userID = $currentUserModel->getId();

		$ws_id = $entity->getId();
		$module = $entity->getModuleName();
		$parts = explode('x', $ws_id);
		$entityId = $parts[1];

		$org_current_user = $current_user;
		$userInstance = new Users();
		$users = $name = '';
		$table = $this->tables[$module];
		$metainfo = vtlib\Functions::getEntityModuleInfo($module);
		$entityNames = explode(',', $metainfo['fieldname']);
		foreach ($entityNames as $entityName) {
			$name .= ' ' . $entity->get($entityName);
		}

		$resultUsers = $db->query('SELECT id FROM vtiger_users;');
		while ($user = $db->getSingleValue($resultUsers)) {
			$current_user = $userInstance->retrieveCurrentUserInfoFromFile($user);
			if (Users_Privileges_Model::isPermitted($module, 'DetailView', $entityId)) {
				$users .= ',' . $user;
			}
		}

		$db->delete($table, 'id = ?', [$entityId]);
		$result = $db->pquery('SELECT fieldname FROM vtiger_field WHERE tabid = ? AND uitype = ?;', [vtlib\Functions::getModuleId($module), 13]);
		while ($fieldname = $db->getSingleValue($result)) {
			if ($entity->get($fieldname) != '') {
				$db->insert($table, ['id' => $entityId, 'email' => $entity->get($fieldname), 'name' => trim($name), 'users' => trim($users, ',')]);
			}
		}
		OSSMail_Module_Model::createBookMailsFiles($this->tables);
		$current_user = $org_current_user;
	}

	/**
	 * Function to get contents of this task
	 * @param <Object> $entity
	 * @return <Array> contents
	 */
	public function getContents($entity, $entityCache = false) {
		$this->contents = true;
		return $this->contents;
	}

}
