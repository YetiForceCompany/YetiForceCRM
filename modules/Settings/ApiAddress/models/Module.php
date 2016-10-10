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

		$db = PearDatabase::getInstance();
		$log->debug("Entering " . __CLASS__ . "::" . __METHOD__ . "(" . $type . ") method ...");

		$sql = "SELECT * FROM `vtiger_apiaddress` ";
		$params = array();

		if ($type) {
			$params[] = $type;
			"WHERE `type` = ?;";
		}

		$result = $db->pquery($sql, $params, true);

		$rawData = array();

		for ($i = 0; $i < $db->num_rows($result); $i++) {
			$element = $db->query_result_rowdata($result, $i);

			$rawData[$element['type']][$element['name']] = $element['val'];
		}

		$log->debug("Exiting " . __CLASS__ . "::" . __METHOD__ . "(" . $type . ") method ...");

		return $rawData;
	}

	public function setConfig(array $elements)
	{

		$log = vglobal('log');

		$log->debug("Entering " . __CLASS__ . "::" . __METHOD__ . " method ...");

		$db = PearDatabase::getInstance();


		$apiName = $elements['api_name'];
		unset($elements['api_name']);

		if (count($elements)) {
			foreach ($elements as $key => $value) {
				$sqlVar = array();

				$sqlVar[] = $value;
				$sqlVar[] = $apiName;
				$sqlVar[] = $key;

				$sql = "UPDATE `vtiger_apiaddress` SET `val` = ? WHERE `type` = ? && `name` = ?";

				$result = $db->pquery($sql, $sqlVar, true);
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
		$db = PearDatabase::getInstance();

		$query = 'SELECT COUNT(1) AS num FROM `vtiger_apiaddress` WHERE `name` = "nominatim" && `val` > "0";';
		$result = $db->query($query);

		return (bool) $db->getSingleValue($result);
	}
}
