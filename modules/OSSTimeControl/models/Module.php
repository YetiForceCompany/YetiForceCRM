<?php

/**
 * OSSTimeControl module model class.
 *
 * @package   Model
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSTimeControl_Module_Model extends Vtiger_Module_Model
{
	public function getCalendarViewUrl()
	{
		return 'index.php?module=' . $this->getName() . '&view=Calendar';
	}

	/** {@inheritdoc} */
	public function getSideBarLinks($linkParams)
	{
		$links = parent::getSideBarLinks($linkParams);
		array_unshift($links['SIDEBARLINK'], Vtiger_Link_Model::getInstanceFromValues([
			'linktype' => 'SIDEBARLINK',
			'linklabel' => 'LBL_CALENDAR_VIEW',
			'linkurl' => $this->getCalendarViewUrl(),
			'linkicon' => 'fas fa-calendar-alt',
		]));
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
			->groupBy('vtiger_crmentity.smownerid')->orderBy(['vtiger_crmentity.smownerid' => SORT_ASC])->createCommand()
			->query();

		$userTime = [
			'labels' => [],
			'title' => \App\Language::translate('LBL_SUM', $this->getName()) . ': ' . \App\Fields\RangeTime::displayElapseTime($totalTime, 'i', 'i', false),
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
			$ownerName = App\Fields\Owner::getLabel($row['smownerid']) ?? '';
			$color = App\Fields\Owner::getColor($row['smownerid']);
			$userTime['labels'][] = \App\Utils::getInitials($ownerName);
			$userTime['datasets'][0]['tooltips'][] = $ownerName;
			$userTime['datasets'][0]['data'][] = round((float) $row['sumtime'] / 60, 2);
			$userTime['datasets'][0]['dataFormatted'][] = \App\Fields\RangeTime::displayElapseTime($row['sumtime']);
			$userTime['datasets'][0]['backgroundColor'][] = $color;
			$userTime['datasets'][0]['borderColor'][] = $color;
		}
		$dataReader->close();
		return ['totalTime' => $totalTime, 'userTime' => $userTime];
	}

	/** {@inheritdoc} */
	public function getFieldsForSave(Vtiger_Record_Model $recordModel)
	{
		$fields = parent::getFieldsForSave($recordModel);
		if (!\in_array('sum_time', $fields)) {
			$fields[] = 'sum_time';
		}
		return $fields;
	}

	/** {@inheritdoc} */
	public function getLayoutTypeForQuickCreate(): string
	{
		return 'standard';
	}
}
