<?php namespace includes\fields;

/**
 * Tools for email class
 * @package YetiForce.Include
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Email
{

	public function findCrmidByPrefix($value, $moduleName)
	{
		$moduleModel = \Settings_Vtiger_CustomRecordNumberingModule_Model::getInstance($moduleName);
		$moduleData = $moduleModel->getModuleCustomNumberingData();
		$redex = '/\[' . $moduleData['prefix'] . '([0-9]*)\]/';
		preg_match($redex, $value, $match);
		if (!empty($match)) {
			return $match[1];
		} else {
			return false;
		}
	}

	public static function findCrmidByEmail($value, $allowedModules = [], $skipModules = [])
	{
		$db = \PearDatabase::getInstance();
		$rows = $params = $fields = [];
		$query = '';
		$countWhere = 0;
		$result = $db->query('SELECT vtiger_field.columnname,vtiger_field.tablename,vtiger_field.fieldlabel,vtiger_field.tabid,vtiger_tab.name FROM vtiger_field INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_field.tabid WHERE vtiger_tab.presence = 0 AND vtiger_field.presence <> 1 AND uitype = 13');
		while ($row = $db->getRow($result)) {
			$fields[$row['name']][$row['tablename']][$row['columnname']] = $row;
		}
		foreach ($fields as $moduleName => &$moduleFields) {
			if (($modules && !in_array($moduleName, $allowedModules)) || in_array($moduleName, $skipModules)) {
				continue;
			}
			$instance = \CRMEntity::getInstance($moduleName);
			$join = $where = [];
			foreach ($moduleFields as $tablename => &$columns) {
				$tableIndex = $instance->tab_name_index[$tablename];
				$query .= sprintf(' UNION (SELECT %s AS id,label,setype FROM %s', $tableIndex, $tablename);
				foreach ($columns as $columnName => &$row) {
					$join[$tablename] = $row;
					$where[] = $tablename . '.' . $columnName;
					$countWhere++;
				}
			}
			foreach ($join as $columnName => &$row) {
				$query .= sprintf(' INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=%s', $tablename . '.' . $tableIndex);
			}
			$query .= ' WHERE vtiger_crmentity.deleted = 0 AND (';
			$or = '';
			foreach ($where as $columnName) {
				$or .= sprintf(' OR %s = ?', $columnName);
			}
			$query .= ltrim($or, ' OR ') . '))';
		}
		for ($index = 0; $index < $countWhere; $index++) {
			$params[] = $value;
		}
		$result = $db->pquery(ltrim($query, ' UNION '), $params);
		while ($row = $db->getRow($result)) {
			$rows[] = ['crmid' => $row['id'], 'modules' => $row['setype'], 'label' => $row['label']];
		}
		return $rows;
	}
}
