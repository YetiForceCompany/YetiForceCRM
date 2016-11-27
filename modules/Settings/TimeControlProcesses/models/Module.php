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

class Settings_TimeControlProcesses_Module_Model extends Vtiger_Base_Model
{

	public static function getCleanInstance()
	{
		$instance = new self();
		return $instance;
	}

	public function getConfigInstance($type = false)
	{

		\App\Log::trace('Start ' . __METHOD__ . " | Type: " . print_r($type, true));
		$query = (new App\Db\Query())->from('yetiforce_proc_tc');
		if ($type) {
			$query->where(['type' => $type]);
		}
		$dataReader = $query->createCommand()->query();
		$output = [];
		while ($row = $dataReader->read()) {
			$output[$row['type']][$row['param']] = $row['value'];
		}
		$this->setData($output);
		\App\Log::trace('End ' . __METHOD__);
		return $this;
	}

	public function setConfig($param)
	{
		\App\Log::trace('Start ' . __METHOD__);
		\App\Db::getInstance()->createCommand()
			->update('yetiforce_proc_tc', ['value' => $param['value']], ['type' => $param['type'], 'param' => $param['param']])->execute();
		\App\Log::trace('End ' . __METHOD__);
		return true;
	}
}
