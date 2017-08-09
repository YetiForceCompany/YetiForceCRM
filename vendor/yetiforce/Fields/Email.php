<?php
namespace App\Fields;

/**
 * Tools for email class
 * @package YetiForce.App
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Email
{

	/**
	 * Gets the prefix from text
	 * @param string $value
	 * @param string $moduleName
	 * @return boolean|string
	 */
	public static function findRecordNumber($value, $moduleName)
	{
		$moduleData = RecordNumber::getNumber($moduleName);
		$prefix = str_replace(['\{\{YYYY\}\}', '\{\{YY\}\}', '\{\{MM\}\}', '\{\{DD\}\}', '\{\{M\}\}', '\{\{D\}\}'], ['\d{4}', '\d{2}', '\d{2}', '\d{2}', '\d{1,2}', '\d{1,2}'], preg_quote($moduleData['prefix'], '/'));
		$postfix = str_replace(['\{\{YYYY\}\}', '\{\{YY\}\}', '\{\{MM\}\}', '\{\{DD\}\}', '\{\{M\}\}', '\{\{D\}\}'], ['\d{4}', '\d{2}', '\d{2}', '\d{2}', '\d{1,2}', '\d{1,2}'], preg_quote($moduleData['postfix'], '/'));
		$redex = '/\[' . $prefix . '([0-9]*)' . $postfix . '\]/';
		preg_match($redex, $value, $match);
		if (!empty($match)) {
			return trim($match[0], '[,]');
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
		$dataReader = (new \App\Db\Query())->select(['vtiger_field.columnname', 'vtiger_field.tablename', 'vtiger_field.fieldlabel', 'vtiger_field.tabid', 'vtiger_tab.name'])
				->from('vtiger_field')->innerJoin('vtiger_tab', 'vtiger_field.tabid = vtiger_tab.tabid')
				->where(['vtiger_tab.presence' => 0])
				->andWhere(['<>', 'vtiger_field.presence', 1])
				->andWhere(['or', ['uitype' => 13], ['uitype' => 104]])->createCommand()->query();
		while ($row = $dataReader->read()) {
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
