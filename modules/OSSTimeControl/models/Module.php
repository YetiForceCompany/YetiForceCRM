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
		$totalTime = $query->limit(null)->orderBy('')->sum('vtiger_osstimecontrol.sum_time');

		$chartData = [
			'show_chart' => false,
		];

		$datasetIndex = 0;
		$dataReader = $query->select(['sumtime' => new \yii\db\Expression('SUM(vtiger_osstimecontrol.sum_time)'), 'vtiger_crmentity.smownerid'])
			->groupBy('vtiger_crmentity.smownerid')->orderBy(['vtiger_crmentity.smownerid' => SORT_ASC])->createCommand()
			->query();
		$chartData['title'] = [
			'text' => \App\Language::translate('LBL_SUM', $this->getName()) . ': ' . \App\Fields\RangeTime::displayElapseTime($totalTime, 'i', 'hi', false),
			'textStyle' => [
				'fontSize' => 12
			]
		];
		while ($row = $dataReader->read()) {
			$color = \App\Fields\Owner::getColor($row['smownerid']);
			$fullName = trim(\App\Fields\Owner::getLabel($row['smownerid']));
			$label = \App\Utils::getInitials($fullName);

			$chartData['series'][$datasetIndex]['data'][] = ['value' => round((float) $row['sumtime'] / 60, 2), 'name' => $label, 'itemStyle' => ['color' => $color], 'link' => '', 'fullName' => $fullName, 'fullValue' => \App\Fields\RangeTime::displayElapseTime((float) $row['sumtime'])];
			$chartData['series'][$datasetIndex]['type'] = 'bar';
			$chartData['xAxis']['data'][] = $label;
			$chartData['show_chart'] = true;
		}
		$dataReader->close();

		return ['totalTime' => $totalTime, 'userTime' => $chartData];
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
