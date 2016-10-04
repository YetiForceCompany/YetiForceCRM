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

class ContactsPortalPass
{

	private $moduleList = array('Contacts');

	public function process($data)
	{
		if ($data['password'] && $data['password'] != '')
			return $data['password'];
		if ($data['record'] && $data['record'] != '') {
			$adb = PearDatabase::getInstance();
			$sql = "SELECT `password_sent` FROM `vtiger_portalinfo` WHERE `id` = ? LIMIT 1;";
			$result = $adb->pquery($sql, array($data['record']));
			// return in email unencrypted password
			$password = $adb->query_result($result, 0, 'password_sent');
			return $password;
		}
	}

	public function getListAllowedModule()
	{
		return $this->moduleList;
	}
}
