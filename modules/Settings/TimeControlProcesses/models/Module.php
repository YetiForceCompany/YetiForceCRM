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
		$log = vglobal('log');
		$log->debug('Start ' . __CLASS__ . ':' . __FUNCTION__ . " | Type: " . print_r($type, true));
		$db = PearDatabase::getInstance();
		$sql = 'SELECT * FROM `yetiforce_proc_tc`';
		if ($type && !is_array($type)) {
			$type = [$type];
		}
		$params = [];
		if ($type) {
			$sql .= sprintf(' WHERE `type` IN (%s)', generateQuestionMarks($type));
			$params = $type;
		}
		$result = $db->pquery($sql, $params);
		$output = [];
		while ($row = $db->fetch_array($result)) {
			$output[$row['type']][$row['param']] = $row['value'];
		}
		$this->setData($output);
		$log->debug('End ' . __CLASS__ . ':' . __FUNCTION__);
		return $this;
	}

	public function setConfig($param)
	{
		$log = vglobal('log');
		$log->debug('Start ' . __CLASS__ . ':' . __FUNCTION__);
		$db = PearDatabase::getInstance();
		$db->pquery('UPDATE `yetiforce_proc_tc` SET `value` = ? WHERE `type` = ? && `param` = ?;', $param);
		$log->debug('End ' . __CLASS__ . ':' . __FUNCTION__);
		return true;
	}
}
