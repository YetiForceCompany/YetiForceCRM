<?php

/**
 * OSSTimeControl module model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSTimeControl_Module_Model extends Vtiger_Module_Model
{
	public function getCalendarViewUrl()
	{
		return 'index.php?module=' . $this->getName() . '&view=Calendar';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSideBarLinks($linkParams)
	{
		$links = parent::getSideBarLinks($linkParams);
		array_unshift($links['SIDEBARLINK'], Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'SIDEBARLINK',
			'linklabel' => 'LBL_CALENDAR_VIEW',
			'linkurl' => $this->getCalendarViewUrl(),
			'linkicon' => 'fas fa-calendar-alt'
		]));
		if (isset($linkParams['ACTION']) && $linkParams['ACTION'] === 'Calendar') {
			$links['SIDEBARWIDGET'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'SIDEBARWIDGET',
				'linklabel' => 'LBL_USERS',
				'linkurl' => 'module=' . $this->getName() . '&view=RightPanel&mode=getUsersList',
				'linkicon' => '',
				'linkclass' => 'js-calendar__filter--users',
			]);
			$links['SIDEBARWIDGET'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'SIDEBARWIDGET',
				'linklabel' => 'LBL_TYPE',
				'linkurl' => 'module=' . $this->getName() . '&view=RightPanel&mode=getTypesList',
				'linkicon' => '',
				'linkclass' => 'js-calendar__filter--types',
			]);
		}
		return $links;
	}

	/**
	 * Function to get the Default View Component Name.
	 *
	 * @return string
	 */
	public function getDefaultViewName()
	{
		return 'Calendar';
	}

	/**
	 * Function to get data of charts.
	 *
	 * @param App\Db\Query $query
	 *
	 * @return array
	 */
	public function getRelatedSummary(App\Db\Query $query)
	{
		// Calculate total working time
		$totalTime = $query->limit(null)->orderBy('')->sum('vtiger_osstimecontrol.sum_time');
		// Calculate total working time divided into users
		$dataReader = $query->select(['sumtime' => new \yii\db\Expression('SUM(vtiger_osstimecontrol.sum_time)'), 'vtiger_crmentity.smownerid'])
			->groupBy('vtiger_crmentity.smownerid')
			->orderBy(['vtiger_crmentity.smownerid' => SORT_ASC])
			->createCommand()
			->query();

		$userTime = [
			'labels' => [],
			'title' => \App\Language::translate('LBL_SUM', $this->getName()) . ': ' . \App\Fields\Time::formatToHourText($totalTime, 'full'),
			'datasets' => [
				[
					'data' => [],
					'backgroundColor' => [],
					'borderColor' => [],
					'tooltips' => [],
				],
			],
		];

		while ($row = $dataReader->read()) {
			$ownerName = App\Fields\Owner::getLabel($row['smownerid']);
			$color = App\Fields\Owner::getColor($row['smownerid']);
			$userTime['labels'][] = \App\Utils::getInitials($ownerName);
			$userTime['datasets'][0]['tooltips'][] = $ownerName;
			$userTime['datasets'][0]['data'][] = (float) $row['sumtime'];
			$userTime['datasets'][0]['backgroundColor'][] = $color;
			$userTime['datasets'][0]['borderColor'][] = $color;
		}
		$dataReader->close();

		return ['totalTime' => $totalTime, 'userTime' => $userTime];
	}

	/**
	 * Get working time of users.
	 *
	 * @param $id
	 * @param $moduleName
	 *
	 * @return bool
	 */
	public function getTimeUsers($id, $moduleName)
	{
		$fieldName = \App\ModuleHierarchy::getMappingRelatedField($moduleName);
		if (empty($id) || empty($fieldName)) {
			$chartData = ['show_chart' => false];
		} else {
			$query = (new \App\Db\Query())->select([
				'vtiger_crmentity.smownerid',
				'time' => new \yii\db\Expression('SUM(vtiger_osstimecontrol.sum_time)'),
			])->from('vtiger_osstimecontrol')->innerJoin('vtiger_crmentity', 'vtiger_osstimecontrol.osstimecontrolid = vtiger_crmentity.crmid')
				->where(['vtiger_crmentity.deleted' => 0, "vtiger_osstimecontrol.$fieldName" => $id, 'vtiger_osstimecontrol.osstimecontrol_status' => OSSTimeControl_Record_Model::RECALCULATE_STATUS])
				->groupBy('smownerid');
			App\PrivilegeQuery::getConditions($query, $this->getName());
			$dataReader = $query->createCommand()->query();
			$chartData = [
				'labels' => [],
				'fullLabels' => [],
				'datasets' => [],
				'show_chart' => false,
			];
			while ($row = $dataReader->read()) {
				$ownerName = App\Fields\Owner::getLabel($row['smownerid']);
				$color = App\Fields\Owner::getColor($row['smownerid']);
				$chartData['labels'][] = \App\Utils::getInitials($ownerName);
				$chartData['datasets'][0]['tooltips'][] = $ownerName;
				$chartData['datasets'][0]['data'][] = (float) $row['time'];
				$chartData['datasets'][0]['backgroundColor'][] = $color;
				$chartData['datasets'][0]['borderColor'][] = $color;
			}
			$chartData['show_chart'] = count($chartData['datasets']) && count($chartData['datasets'][0]['data']);
			$dataReader->close();
		}
		return $chartData;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFieldsForSave(\Vtiger_Record_Model $recordModel)
	{
		$fields = parent::getFieldsForSave($recordModel);
		if (!in_array('sum_time', $fields)) {
			$fields[] = 'sum_time';
		}
		return $fields;
	}
}
