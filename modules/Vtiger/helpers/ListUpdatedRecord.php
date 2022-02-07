<?php

/**
 * ListUpdatedRecord class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class ListUpdatedRecord
{
	public static function getListRecord($module, array $columnList, $limit)
	{
		$moduleList = [];
		$recordList = [];
		if (!$module) {
			$moduleList = (new \App\Db\Query())->select(['name'])
				->from('vtiger_tab')
				->where(['isentitytype' => 1])
				->andWhere(['<>', 'presence', 1])
				->createCommand()->queryColumn();
		} else {
			$moduleList[] = $module;
		}
		if (!\in_array('smownerid', $columnList)) {
			$columnList[] = 'smownerid';
		}
		if ('all' == $limit) {
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
			$row['setype'] = \App\Language::translate($row['setype'], $row['setype']);
			$recordList[] = $row;
		}
		if (empty($recordList)) {
			return false;
		}
		$dataReader->close();

		return $recordList;
	}
}
