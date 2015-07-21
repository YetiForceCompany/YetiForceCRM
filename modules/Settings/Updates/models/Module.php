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

class Settings_Updates_Module_Model extends Settings_Vtiger_Module_Model
{

	public static function getUpdates()
	{

		$db = PearDatabase::getInstance();

		$query = 'SELECT * FROM `yetiforce_updates` yup';
		$result = $db->pquery($query);
		$noOfRows = $db->num_rows($result);

		$matchingRecords = array();
		$updates = array();
		for ($i = 0; $i < $noOfRows; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			$updates[] = $row;
		}
		return $updates;
	}
}
