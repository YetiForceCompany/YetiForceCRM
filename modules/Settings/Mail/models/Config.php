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

class Settings_Mail_Config_Model
{

	public function updateConfig($name, $val, $type)
	{
		$db = PearDatabase::getInstance();
		$db->pquery('UPDATE yetiforce_mail_config SET `value` = ? WHERE `type` = ? && `name` = ?;', [$val, $type, $name]);
	}

	public static function getConfig($type)
	{
		$db = PearDatabase::getInstance();
		$config = [];
		$result = $db->pquery('SELECT * FROM yetiforce_mail_config WHERE type = ?;', [$type]);
		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$row = $db->raw_query_result_rowdata($result, $i);
			$config[$row['name']] = $row['value'];
		}
		return $config;
	}

	/**
	 * Function to get instance
	 * @param <Boolean> true/false
	 * @return <Settings_Leads_Mapping_Model>
	 */
	public static function getInstance()
	{
		$instance = new self();
		return $instance;
	}
}
