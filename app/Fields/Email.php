<?php

namespace App\Fields;

/**
 * Tools for email class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Email
{
	/**
	 * Gets the prefix from text.
	 *
	 * @param string $value
	 * @param string $moduleName
	 *
	 * @return bool|string
	 */
	public static function findRecordNumber($value, $moduleName)
	{
		$moduleData = RecordNumber::getInstance($moduleName);
		$prefix = str_replace(['\{\{YYYY\}\}', '\{\{YY\}\}', '\{\{MM\}\}', '\{\{DD\}\}', '\{\{M\}\}', '\{\{D\}\}'], ['\d{4}', '\d{2}', '\d{2}', '\d{2}', '\d{1,2}', '\d{1,2}'], preg_quote($moduleData->get('prefix'), '/'));
		$postfix = str_replace(['\{\{YYYY\}\}', '\{\{YY\}\}', '\{\{MM\}\}', '\{\{DD\}\}', '\{\{M\}\}', '\{\{D\}\}'], ['\d{4}', '\d{2}', '\d{2}', '\d{2}', '\d{1,2}', '\d{1,2}'], preg_quote($moduleData->get('postfix'), '/'));
		$redex = '/\[' . $prefix . '([0-9]*)' . $postfix . '\]/';
		$redex = preg_replace_callback('/\\\\{\\\\{picklist\\\\:([a-z0-9_]+)\\\\}\\\\}/i', function ($matches) {
			$picklistPrefix = array_column(Picklist::getValues($matches[1]), 'prefix');
			if (!$picklistPrefix) {
				return '';
			}
			return '((' . implode('|', $picklistPrefix) . ')*)';
		}, $redex);
		preg_match($redex, $value, $match);
		if (!empty($match)) {
			return trim($match[0], '[,]');
		}
		return false;
	}

	/**
	 * Find crm id by email.
	 *
	 * @param int|string $value
	 * @param array      $allowedModules
	 * @param array      $skipModules
	 *
	 * @return array
	 */
	public static function findCrmidByEmail($value, array $allowedModules = [], array $skipModules = [])
	{
		$db = \App\Db::getInstance();
		$rows = $fields = [];
		$dataReader = (new \App\Db\Query())->select(['vtiger_field.columnname', 'vtiger_field.tablename', 'vtiger_field.fieldlabel', 'vtiger_field.tabid', 'vtiger_tab.name'])
			->from('vtiger_field')->innerJoin('vtiger_tab', 'vtiger_field.tabid = vtiger_tab.tabid')
			->where(['vtiger_tab.presence' => 0])
			->andWhere(['<>', 'vtiger_field.presence', 1])
			->andWhere(['or', ['uitype' => 13], ['uitype' => 104]])->createCommand()->query();
		while ($row = $dataReader->read()) {
			$fields[$row['name']][$row['tablename']][$row['columnname']] = $row;
		}
		$queryUnion = null;
		foreach ($fields as $moduleName => $moduleFields) {
			if (($allowedModules && !in_array($moduleName, $allowedModules)) || in_array($moduleName, $skipModules)) {
				continue;
			}
			$instance = \CRMEntity::getInstance($moduleName);
			$isEntityType = isset($instance->tab_name_index['vtiger_crmentity']);
			foreach ($moduleFields as $tablename => $columns) {
				$tableIndex = $instance->tab_name_index[$tablename];
				$query = (new \App\Db\Query())->select(['crmid' => $tableIndex, 'modules' => new \yii\db\Expression($db->quoteValue($moduleName))])
					->from($tablename);
				if ($isEntityType) {
					$query->innerJoin('vtiger_crmentity', "vtiger_crmentity.crmid = {$tablename}.{$tableIndex}")->where(['vtiger_crmentity.deleted' => 0]);
				}
				$orWhere = ['or'];
				foreach ($columns as $row) {
					$orWhere[] = ["{$row['tablename']}.{$row['columnname']}" => $value];
				}
				$query->andWhere($orWhere);
				if ($queryUnion) {
					$queryUnion->union($query);
				} else {
					$queryUnion = $query;
				}
			}
		}
		$rows = $queryUnion->all();
		$labels = \App\Record::getLabel(array_column($rows, 'crmid'));
		foreach ($rows as &$row) {
			$row['label'] = $labels[$row['crmid']];
		}
		return $rows;
	}

	/**
	 * Get user mail.
	 *
	 * @param int $userId
	 *
	 * @return string
	 */
	public static function getUserMail($userId)
	{
		return \App\User::getUserModel($userId)->getDetail('email1');
	}
}
