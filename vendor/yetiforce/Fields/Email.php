<?php
namespace App\Fields;

/**
 * Tools for email class
 * @package YetiForce.App
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Email
{

	public static function findRecordNumber($value, $moduleName)
	{
		$moduleData = RecordNumber::getNumber($moduleName);
		$redex = '/\[' . $moduleData['prefix'] . '([0-9]*)' . $moduleData['postfix'] . '\]/';
		preg_match($redex, $value, $match);
		if (!empty($match)) {
			return $moduleData['prefix'] . $match[1] . $moduleData['postfix'];
		} else {
			return false;
		}
	}

	public static function findCrmidByEmail($value, $allowedModules = [], $skipModules = [])
	{
		$db = \PearDatabase::getInstance();
		$rows = $ids = $params = $fields = [];
		$query = '';
		$countWhere = 0;
		$result = $db->query('SELECT vtiger_field.columnname,vtiger_field.tablename,vtiger_field.fieldlabel,vtiger_field.tabid,vtiger_tab.name FROM vtiger_field INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_field.tabid WHERE vtiger_tab.presence = 0 && vtiger_field.presence <> 1 && (uitype = 13 || uitype = 104)');
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
				$query .= sprintf(' UNION (SELECT %s AS id, \'%s\' AS setype FROM %s', $tableIndex, $moduleName, $tablename);
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
			$ids[] = $row['id'];
			$rows[] = ['crmid' => $row['id'], 'modules' => $row['setype'], 'label' => isset($row['label']) ? $row['label'] : false];
		}
		$labels = \App\Record::getLabel($ids);
		foreach ($rows as &$row) {
			$row['label'] = $labels[$row['crmid']];
		}
		return $rows;
	}

	public static function getUserMail($userId)
	{
		$userModel = \App\User::getUserModel($userId);
		return $userModel->getDetail('email1');
	}
}
