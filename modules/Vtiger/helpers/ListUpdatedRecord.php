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
		$moduleList = [];
		$recordList = [];
		if (!$module) {
			$moduleList = (new \App\Db\Query())->select('name')
				->from('vtiger_tab')
				->where(['isentitytype' => 1])
				->andWhere(['<>', 'presence', 1])
				->createCommand()->queryColumn();
		} else {
			$moduleList[] = $module;
		}
		if (!in_array('smownerid', $columnList)) {
			$columnList[] = 'smownerid';
		}
		if ($limit == 'all') {
			$limit = 200;
		}
		$select = array_values($columnList);
		$select['smownerid'] = \App\Module::getSqlForNameInDisplayFormat('Users');
		$dataReader = (new \App\Db\Query())->select($select)->from('vtiger_crmentity')
			->leftJoin('u_#__crmentity_label', 'u_#__crmentity_label.crmid = vtiger_crmentity.crmid')
			->innerJoin('vtiger_users', 'vtiger_users.id = vtiger_crmentity.smownerid')
			->where(['was_read' => 0, 'vtiger_crmentity.deleted' => 0, 'setype' => $moduleList])
			->limit($limit)
			->createCommand()->query();
		while ($row = $dataReader->read()) {
			$row['setype'] = vtranslate($row['setype'], $row['setype']);
			$recordList [] = $row;
		}
		if (empty($recordList)) {
			return false;
		}
		return $recordList;
	}
}
