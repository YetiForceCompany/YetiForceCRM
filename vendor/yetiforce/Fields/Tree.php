<?php
namespace App\Fields;

class Tree
{

	/**
	 * Get tree values by ID
	 * @param int $templateId
	 * @return array[]
	 */
	public static function getValuesById($templateId)
	{
		if (\App\Cache::has('TreeValuesById', $templateId)) {
			return \App\Cache::get('TreeValuesById', $templateId);
		}
		$rows = (new \App\Db\Query())
				->from('vtiger_trees_templates_data')
				->where(['templateid' => $templateId])->indexBy('tree')->all();
		\App\Cache::save('TreeValuesById', $templateId, $rows, \App\Cache::SHORT);
		return $rows;
	}

	/**
	 * Get tree values by tree ID
	 * @param int $templateId
	 * @param string $tree
	 * @return array[]
	 */
	public static function getValueByTreeId($templateId, $tree)
	{
		$rows = static::getValuesById($templateId);
		return $rows[$tree];
	}
}
