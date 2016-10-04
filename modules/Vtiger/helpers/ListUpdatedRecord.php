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

	public static function getListRecord($module = NULL, array $columnList, $limit)
	{
		$db = PearDatabase::getInstance();
		$moduleList = [];
		$recordList = [];
		if (!$module) {
			$getListModuleSql = "SELECT name FROM vtiger_tab WHERE isentitytype = 1 && vtiger_tab.presence != 1";
			$getListModuleResult = $db->query($getListModuleSql);
			while ($row = $db->getRow($getListModuleResult)) {
				$moduleList [] = $row['name'];
			}
		} else {
			$moduleList[] = $module;
		}
		if (!in_array('smownerid', $columnList)) {
			$columnList[] = 'smownerid';
		}
		$query = 'SELECT %s,%s as smownerid FROM vtiger_crmentity
				LEFT JOIN u_yf_crmentity_label ON u_yf_crmentity_label.crmid = vtiger_crmentity.crmid
				INNER JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid 
				WHERE was_read = 0 && vtiger_crmentity.deleted = 0 && setype IN (%s) LIMIT ?';
		$query = sprintf($query, implode(',', $columnList), \vtlib\Deprecated::getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users'), generateQuestionMarks($moduleList));
		if ($limit == 'all') {
			$limit = 200;
		}
		$params = array_merge($moduleList, [$limit]);
		$getRecordListResult = $db->pquery($query, $params);
		while ($row = $db->getRow($getRecordListResult)) {
			$row['setype'] = vtranslate($row['setype'], $row['setype']);
			$recordList [] = $row;
		}
		if (!count($recordList)) {
			return false;
		}
		return $recordList;
	}
}
