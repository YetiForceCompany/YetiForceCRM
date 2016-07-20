<?php namespace includes\fields;

/**
 * Tools for email class
 * @package YetiForce.Include
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Email
{

	public static function findCrmidByPrefix($value, $moduleName)
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
		$result = $db->query('SELECT vtiger_field.columnname,vtiger_field.tablename,vtiger_field.fieldlabel,vtiger_field.tabid,vtiger_tab.name FROM vtiger_field INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_field.tabid WHERE vtiger_tab.presence = 0 AND vtiger_field.presence <> 1 AND (uitype = 13 OR uitype = 104)');
		while ($row = $db->getRow($result)) {
			$fields[$row['name']][$row['tablename']][$row['columnname']] = $row;
		}
		foreach ($fields as $moduleName => &$moduleFields) {
			if (($allowedModules && !in_array($moduleName, $allowedModules)) || in_array($moduleName, $skipModules)) {
				continue;
			}
			$instance = \CRMEntity::getInstance($moduleName);
			$isEntityType = isset($instance->tab_name_index['vtiger_crmentity']);
			$join = $where = [];
			foreach ($moduleFields as $tablename => &$columns) {
				$tableIndex = $instance->tab_name_index[$tablename];
				if ($isEntityType) {
					$selest = ',label,setype';
				} else {
					$selest = ",'$moduleName' AS setype";
				}
				$query .= sprintf(' UNION (SELECT %s AS id %s FROM %s', $tableIndex, $selest, $tablename);
				foreach ($columns as $columnName => &$row) {
					$join[$tablename] = $row;
					$where[] = $tablename . '.' . $columnName;
					$countWhere++;
				}
			}
			$whereQuery = ' WHERE';
			if ($isEntityType) {
				foreach ($join as $columnName => &$row) {
					$query .= sprintf(' INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=%s', $tablename . '.' . $tableIndex);
				}
				$whereQuery .= ' vtiger_crmentity.deleted = 0 AND';
			}
			$whereQuery .= ' (';
			$or = '';
			foreach ($where as $columnName) {
				$or .= sprintf(' OR %s = ?', $columnName);
			}
			$query .= $whereQuery . ltrim($or, ' OR ') . '))';
		}
		for ($index = 0; $index < $countWhere; $index++) {
			$params[] = $value;
		}
		$result = $db->pquery(ltrim($query, ' UNION '), $params);
		while ($row = $db->getRow($result)) {
			$rows[] = ['crmid' => $row['id'], 'modules' => $row['setype'], 'label' => isset($row['label']) ? $row['label'] : false];
		}
		return $rows;
	}

	public static function getUserMail($userId)
	{
		$userModel = \Users_Privileges_Model::getInstanceById($userId);
		return $userModel->get('email1');
	}
}
