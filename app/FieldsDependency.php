<?php
/**
 * Fields dependency file.
 *
 * @package App
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace App;

/**
 * Fields dependency class.
 */
class FieldsDependency
{
	/**
	 * @var array Views labels
	 */
	const VIEWS = [
		'Create' => 'LBL_VIEW_CREATE',
		'Edit' => 'LBL_VIEW_EDIT',
		'Detail' => 'LBL_VIEW_DETAIL',
		'QuickCreate' => 'LBL_QUICK_CREATE',
		'QuickEdit' => 'LBL_QUICK_EDIT',
	];
	/**
	 * @var int
	 */
	public const GUI_BACKEND = 0;
	/**
	 * @var int
	 */
	public const GUI_FRONTEND = 1;
	/**
	 * Cache variable for list of fields to hide for a record in a view.
	 *
	 * @see FieldsDependency::getByRecordModel()
	 *
	 * @var array
	 */
	public static $recordModelCache = [];

	/**
	 * Get the dependency list for module.
	 *
	 * @param int      $tabId
	 * @param int|null $gui
	 *
	 * @return array
	 */
	public static function getByModule(int $tabId, ?int $gui = null): array
	{
		if (Cache::has('FieldsDependency', $tabId)) {
			$fields = Cache::get('FieldsDependency', $tabId);
		} else {
			$fields = [];
			$dataReader = (new \App\Db\Query())->from('s_#__fields_dependency')->where(['status' => 0, 'tabid' => $tabId])
				->createCommand()->query();
			while ($row = $dataReader->read()) {
				$row['gui'] = (int) $row['gui'];
				$row['mandatory'] = (int) $row['mandatory'];
				$row['conditions'] = Json::decode($row['conditions']) ?? [];
				$row['fields'] = Json::decode($row['fields']) ?? [];
				$row['conditionsFields'] = Json::decode($row['conditionsFields']) ?? [];
				$views = Json::decode($row['views']) ?? [];
				unset($row['views']);
				foreach ($views as $view) {
					$fields[$view][] = $row;
				}
			}
			Cache::save('FieldsDependency', $tabId, $fields);
		}
		if (isset($gui)) {
			foreach ($fields as $view => $rows) {
				foreach ($rows as $key => $row) {
					if ($gui !== $row['gui']) {
						unset($fields[$view][$key]);
					}
				}
			}
		}
		return $fields;
	}

	/**
	 * Get the list of fields to hide for a record in a view.
	 *
	 * @see FieldsDependency::$recordModelCache
	 *
	 * @param string               $view
	 * @param \Vtiger_Record_Model $recordModel
	 * @param bool                 $cache
	 *
	 * @return array
	 */
	public static function getByRecordModel(string $view, \Vtiger_Record_Model $recordModel, bool $cache = true): array
	{
		$cacheKey = $view . $recordModel->getId();
		if ($cache && isset(self::$recordModelCache[$cacheKey])) {
			return self::$recordModelCache[$cacheKey];
		}
		$return = [
			'show' => ['backend' => [], 'frontend' => [], 'mandatory' => []],
			'hide' => ['backend' => [], 'frontend' => [], 'mandatory' => []],
			'mandatory' => [],
			'conditionsFields' => [],
		];
		$fields = self::getByModule($recordModel->getModule()->getId());
		if ($fields && isset($fields[$view])) {
			foreach ($fields[$view] as $row) {
				$status = (!$row['conditions'] || Condition::checkConditions($row['conditions'], $recordModel)) ? 'show' : 'hide';
				if (self::GUI_FRONTEND === $row['gui']) {
					$return[$status]['frontend'] = array_merge($return[$status]['frontend'], $row['fields']);
				} else {
					$return[$status]['backend'] = array_merge($return[$status]['backend'], $row['fields']);
				}
				if (1 === $row['mandatory']) {
					$return[$status]['mandatory'] = array_merge($return[$status]['mandatory'], $row['fields']);
					$return['mandatory'] = array_merge($return['mandatory'], $row['fields']);
				}
				$return['conditionsFields'] = array_merge($return['conditionsFields'], $row['conditionsFields']);
			}
		}
		return self::$recordModelCache[$cacheKey] = $return;
	}
}
