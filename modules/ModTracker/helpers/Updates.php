<?php

/**
 * Updates helper.
 *
 * @package Helper
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * ModTracker_Updates_Helper class.
 */
class ModTracker_Updates_Helper
{
	/**
	 * Gets summary.
	 *
	 * @param array    $modules
	 * @param array    $actions
	 * @param array    $dateRange
	 * @param int|null $owner
	 * @param int|null $historyOwner
	 *
	 * @return array
	 */
	public static function getSummary(array $modules, array $actions, array $dateRange, ?int $owner, ?int $historyOwner): array
	{
		$updates = $usedActions = [];
		$query = (new \App\Db\Query())
			->select(['vtiger_modtracker_basic.module'])
			->from('vtiger_modtracker_basic')
			->where(['and',
				['vtiger_modtracker_basic.module' => $modules],
				['vtiger_modtracker_basic.status' => $actions],
				[
					'between',
					'vtiger_modtracker_basic.changedon',
					$dateRange[0] . ' 00:00:00', $dateRange[1] . ' 23:59:59'
				]
			]);
		if ($historyOwner) {
			$query->andWhere(['vtiger_modtracker_basic.whodid' => $historyOwner]);
		}
		if ($owner) {
			$query->innerJoin('vtiger_crmentity', 'vtiger_modtracker_basic.crmid=vtiger_crmentity.crmid')
				->andWhere(['or',
					['vtiger_crmentity.smownerid' => $owner],
					['vtiger_crmentity.crmid' => (new \App\Db\Query())->select(['crmid'])->from('u_#__crmentity_showners')->where(['userid' => $owner])]
				]);
		}
		$query->distinct(true);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$moduleName = $row['module'];
			$queryGenerator = self::getQueryGenerator($moduleName, $actions, $dateRange, $owner, $historyOwner);
			$queryGenerator->setCustomColumn(['vtiger_modtracker_basic.status', 'counter' => new \yii\db\Expression('COUNT(*)')])->setCustomGroup(['vtiger_modtracker_basic.status']);
			$result = $queryGenerator->createQuery()->createCommand()->queryAllByGroup(0);
			if ($result) {
				$updates[$moduleName] = $result;
				$usedActions = array_unique(array_merge($usedActions, array_keys($result)));
			}
		}
		$dataReader->close();
		return [$updates, $usedActions];
	}

	/**
	 * Gets QueryGenerator object.
	 *
	 * @param string                   $moduleName
	 * @param array                    $actions
	 * @param array                    $dateRange
	 * @param int|null                 $owner
	 * @param int|null                 $historyOwner
	 * @param Vtiger_Paging_Model|null $pagingModel
	 *
	 * @return App\QueryGenerator
	 */
	public static function getQueryGenerator(string $moduleName, array $actions, array $dateRange, ?int $owner, ?int $historyOwner, ?Vtiger_Paging_Model $pagingModel = null): App\QueryGenerator
	{
		$queryGenerator = (new \App\QueryGenerator($moduleName))
			->setFields([])
			->addJoin([
				'INNER JOIN',
				'vtiger_modtracker_basic',
				'vtiger_crmentity.crmid = vtiger_modtracker_basic.crmid'
			])
			->addNativeCondition(['vtiger_modtracker_basic.status' => $actions])
			->addNativeCondition([
				'between',
				'vtiger_modtracker_basic.changedon',
				$dateRange[0] . ' 00:00:00', $dateRange[1] . ' 23:59:59'
			])->setStateCondition('All');
		if ($pagingModel) {
			$queryGenerator->setLimit($pagingModel->getPageLimit() + 1)->setOffset($pagingModel->getStartIndex());
		}
		if ($owner) {
			$queryGenerator->addCondition('assigned_user_id', $owner, 'e', false);
			if ($queryGenerator->getModuleField('shownerid')) {
				$queryGenerator->addCondition('shownerid', $owner, 'e', false);
			}
		}
		if ($historyOwner) {
			$queryGenerator->addNativeCondition(['vtiger_modtracker_basic.whodid' => $historyOwner]);
		}
		return $queryGenerator;
	}

	/**
	 *  Gets updates.
	 *
	 * @param string              $moduleName
	 * @param array               $actions
	 * @param array               $dateRange
	 * @param int|null            $owner
	 * @param int|null            $historyOwner
	 * @param Vtiger_Paging_Model $pagingModel
	 *
	 * @return array
	 */
	public static function getUpdates(string $moduleName, array $actions, array $dateRange, ?int $owner, ?int $historyOwner, Vtiger_Paging_Model $pagingModel): array
	{
		$updates = [];
		$queryGenerator = self::getQueryGenerator($moduleName, $actions, $dateRange, $owner, $historyOwner, $pagingModel);
		$queryGenerator->setCustomColumn('vtiger_modtracker_basic.*');
		$dataReader = $queryGenerator->createQuery()->orderBy(['vtiger_modtracker_basic.id' => SORT_DESC])->createCommand()->query();
		while ($row = $dataReader->read()) {
			if (\count($updates) === $pagingModel->getPageLimit()) {
				$pagingModel->set('nextPageExists', true);
				break;
			}
			$recordModel = new ModTracker_Record_Model();
			$recordModel->setData($row)->setParent($row['crmid'], $moduleName);
			$updates[$recordModel->getId()] = $recordModel;
		}
		return $updates;
	}
}
