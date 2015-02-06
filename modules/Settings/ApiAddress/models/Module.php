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

class Settings_ApiAddress_Module_Model extends Settings_Vtiger_Module_Model {

	public function getConfig($panel = false) {
		$log = vglobal('log');

		$db = PearDatabase::getInstance();
		$log->debug("Entering Settings_ApiAddress_Module_Model::getConfig(" . $panel . ") method ...");

		$sql = "SELECT * FROM `vtiger_apiaddress`;";
		$result = $db->query($sql, true);

		$rawData = array();

		for ($i = 0; $i <= count($db->num_rows($result)); $i++) {
			$rawData[$i] = $db->query_result_rowdata($result, $i);
		}

		$log->debug("Exiting Settings_ApiAddress_Module_Model::getConfig() method ...");

		return $rawData;
	}

	public function setConfig(array $elements) {

		$log = vglobal('log');

		$log->debug("Entering Settings_ApiAddress_Module_Model::setConfig() method ...");

		$db = PearDatabase::getInstance();

		$apiName = $elements['api_name'];
		unset($elements['api_name']);

		$keys = array_keys($elements);
		$values = array_values($elements);

		$updateFld = array();

		if (count($elements)) {
			foreach ($keys as $key => $value) {
				$updateFld[] = '`' . $value . '` = ? ';
			}
		}

		$sql = "UPDATE `vtiger_apiaddress` SET " . implode(',', $updateFld) . ' WHERE api_name = ?';

		$values[] = $apiName;

		$result = $db->pquery($sql, $values, true);

		$log->debug("Exiting Settings_ApiAddress_Module_Model::setConfig() method ...");

		return $result;
	}

}
