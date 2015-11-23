<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************************************************************** */

class ListUpdatedRecord
{

	public static function getListRecord($module = NULL, array $columnList)
	{
		$db = PearDatabase::getInstance();

		$moduleList = array();

		if (!$module) {
			$getListModuleSql = "SELECT name FROM vtiger_tab WHERE isentitytype = 1 AND vtiger_tab.presence != 1";
			$getListModuleResult = $db->pquery($getListModuleSql, array(), TRUE);

			for ($i = 0; $i < $db->getFieldsCount($getListModuleResult); $i++) {
				$moduleList[] = $db->query_result($getListModuleResult, $i, 'name');
			}
		} else {
			$moduleList[] = $module;
		}

		$recordList = array();

		if (!in_array('smownerid', $columnList)) {
			$columnList[] = 'smownerid';
		}

		for ($i = 0; $i < count($moduleList); $i++) {

			$getRecordListSql = "SELECT " . implode(',', $columnList) . "," . getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users') . " as smownerid FROM vtiger_crmentity "
				. " INNER JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid "
				. " WHERE was_read = 0 AND vtiger_crmentity.deleted = 0 AND setype = ?";

			$getRecordListResult = $db->pquery($getRecordListSql, array($moduleList[$i]), TRUE);


			for ($k = 0; $k < $db->num_rows($getRecordListResult); $k++) {

				$singelRecord = array();

				foreach ($columnList as $col) {
					$singelRecord[$col] = $db->query_result($getRecordListResult, $k, $col);
				}

				if (!empty($singelRecord)) {
					$recordList[] = $singelRecord;
				}
			}
		}

		if (!count($recordList)) {
			return false;
		}

		return $recordList;
	}
}
