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

class Settings_MobileApps_Module_Model extends Settings_Vtiger_Module_Model
{

	public $serviceDir = 'api/mobile_services';

	public function getAllMobileKeys($service = false)
	{
		return Vtiger_Mobile_Model::getAllMobileKeys($service);
	}

	public function getAllService()
	{
		$serices = Array();
		$dir = new DirectoryIterator($this->serviceDir);
		foreach ($dir as $fileinfo) {
			if (!$fileinfo->isDot()) {
				$tmp = explode('.', $fileinfo->getFilename());
				if ($tmp[0] != 'test')
					$serices[$tmp[0]] = 'LBL_MOBILE_' . strtoupper($tmp[0]);
			}
		}
		return $serices;
	}

	public function addKey($params)
	{
		if ((new \App\Db\Query())->from('yetiforce_mobile_keys')
				->where(['user' => $params['user'], 'service' => $params['service']])
				->count())
			return 1;
		$keyLength = 10;
		$key = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $keyLength);
		$result = \App\Db::getInstance()->createCommand()
				->insert('yetiforce_mobile_keys', [
					'user' => $params['user'],
					'service' => $params['service'],
					'key' => $key
				])->execute();
		if (!$result)
			return 0;
		return $key;
	}

	public function deleteKey($params)
	{
		\App\Db::getInstance()->createCommand()
			->delete('yetiforce_mobile_keys', ['user' => $params['user'], 'service' => $params['service']])
			->execute();
	}

	public function changePrivileges($params)
	{
		if ($params['privileges'] != 'null') {
			$privileges = serialize($params['privileges']);
		} else {
			$privileges = '';
		}
		\App\Db::getInstance()->createCommand()->update('yetiforce_mobile_keys', ['privileges_users' => $privileges], ['user' => $params['user'], 'service' => $params['service']])
			->execute();
	}
}
