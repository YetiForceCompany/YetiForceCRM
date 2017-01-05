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

	public static function updateConfig($name, $val, $type)
	{
		\App\Db::getInstance()->createCommand()->update('yetiforce_mail_config', ['value' => $val], [
			'type' => $type,
			'name' => $name
		])->execute();
	}

	public static function getConfig($type)
	{
		$config = [];
		$dataReader = (new \App\Db\Query())->select(['name', 'value'])
				->from('yetiforce_mail_config')
				->where(['type' => $type])
				->createCommand()->query();
		while ($row = $dataReader->read()) {
			$config[$row['name']] = $row['value'];
		}
		return $config;
	}
	
	public static function acceptanceRecord($id)
	{
		\App\Db::getInstance('admin')->createCommand()->update('s_#__mail_queue', ['status' => 1], [
			'id' => $id
		])->execute();
	}

	/**
	 * Function to get instance
	 * @param boolean true/false
	 * @return Settings_Mail_Config_Model
	 */
	public static function getInstance()
	{
		return new self();
	}
}
