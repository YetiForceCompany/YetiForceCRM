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

class Settings_ApiAddress_Module_Model extends Settings_Vtiger_Module_Model
{

	public function getConfig($type = NULL)
	{
		$log = vglobal('log');
		$log->debug("Entering " . __CLASS__ . "::" . __METHOD__ . "(" . $type . ") method ...");

		$rawData = [];
		$query = (new \App\db\Query())->from('vtiger_apiaddress');
		if ($type) {
			$query->where(['type' => $type]);
		}
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$rawData[$row['type']][$row['name']] = $row['val'];
		}

		$log->debug("Exiting " . __CLASS__ . "::" . __METHOD__ . "(" . $type . ") method ...");
		return $rawData;
	}

	public function setConfig(array $elements)
	{

		$log = vglobal('log');
		$log->debug("Entering " . __CLASS__ . "::" . __METHOD__ . " method ...");

		$apiName = $elements['api_name'];
		unset($elements['api_name']);
		$result = 0;
		if (count($elements)) {
			$db = \App\DB::getInstance();
			foreach ($elements as $key => $value) {
				$result = $db->createCommand()
					->update('vtiger_apiaddress', [
						'val' => $value
						], ['type' => $apiName, 'name' => $key])
					->execute();
			}
		}
		$log->debug("Exiting " . __CLASS__ . "::" . __METHOD__ . " method ...");
		return $result;
	}
	/*
	 * Function that checks if keys for chosen adress api are entered, hence if this api is active
	 * @return <Boolean> - true if active, false otherwise
	 */

	public static function isActive()
	{
		return (new \App\db\Query())
				->from('vtiger_apiaddress')
				->where(['name' => 'nominatim'])
				->andWhere(['>', 'val', 0])
				->count(1);
	}
}
