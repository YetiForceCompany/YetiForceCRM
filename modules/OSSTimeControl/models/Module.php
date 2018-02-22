<?php

/**
 * OSSTimeControl module model class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
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
		$links = Vtiger_Link_Model::getAllByType($this->getId(), ['SIDEBARLINK', 'SIDEBARWIDGET'], $linkParams);
		$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_CALENDAR_VIEW',
				'linkurl' => $this->getCalendarViewUrl(),
				'linkicon' => 'fas fa-calendar-alt',
		]);
		$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_RECORDS_LIST',
				'linkurl' => $this->getListViewUrl(),
				'linkicon' => 'fas fa-list',
		]);
		if ($linkParams['ACTION'] === 'Calendar') {
			$links['SIDEBARWIDGET'][] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'SIDEBARWIDGET',
					'linklabel' => 'LBL_USERS',
					'linkurl' => 'module=' . $this->getName() . '&view=RightPanel&mode=getUsersList',
					'linkicon' => '',
			]);
			$links['SIDEBARWIDGET'][] = Vtiger_Link_Model::getInstanceFromValues([
					'linktype' => 'SIDEBARWIDGET',
					'linklabel' => 'LBL_TYPE',
					'linkurl' => 'module=' . $this->getName() . '&view=RightPanel&mode=getTypesList',
					'linkicon' => '',
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
				->groupBy('vtiger_crmentity.smownerid')->orderBy('vtiger_crmentity.smownerid', 'asc')
				->createCommand()->query();

		$userTime = [
			'labels' => [],
			'datasets' => [
				[
					'data' => [],
					'backgroundColor' => [],
					'borderColor' => []
				]
			],
		];

		// if user doesn't have a color - generate it
		function getColor($num)
		{
			$hash = md5('color' . $num); // modify 'color' to get a different palette
			return "rgb(" .
				hexdec(substr($hash, 0, 2)) . "," .
				hexdec(substr($hash, 2, 2)) . "," .
				hexdec(substr($hash, 4, 2)) . ")";
		}
		while ($row = $dataReader->read()) {
			$color = (new App\Db\Query())->select(['color' => 'cal_color'])->from('vtiger_users')->where(['=', 'id', $row['smownerid']])->one();
			if (empty($color['color'])) {
				$color['color'] = getColor($row['smownerid']);
			}
			//$color = App\Fields\Owner::getColor();
			$smownerid = App\Fields\Owner::getLabel($row['smownerid']);
			$userTime['labels'][] = $smownerid; //vtlib\Functions::getInitials($smownerid);
			$userTime['datasets'][0]['data'][] = (float) $row['sumtime'];
			$userTime['datasets'][0]['backgroundColor'][] = $color['color'];
			$userTime['datasets'][0]['borderColor'][] = $color['color'];
		}
		$dataReader->close();

		return ['totalTime' => $totalTime, 'userTime' => $userTime];
	}

	public function getTimeUsers($id, $moduleName)
	{
		$fieldName = \App\ModuleHierarchy::getMappingRelatedField($moduleName);
		if (empty($id) || empty($fieldName)) {
			$response = false;
		} else {
			$query = (new \App\Db\Query())->select([
					'vtiger_crmentity.smownerid',
					'time' => new \yii\db\Expression('SUM(vtiger_osstimecontrol.sum_time)'),
				])->from('vtiger_osstimecontrol')->innerJoin('vtiger_crmentity', 'vtiger_osstimecontrol.osstimecontrolid = vtiger_crmentity.crmid')
				->where(['vtiger_crmentity.deleted' => 0, "vtiger_osstimecontrol.$fieldName" => $id, 'vtiger_osstimecontrol.osstimecontrol_status' => OSSTimeControl_Record_Model::RECALCULATE_STATUS])
				->groupBy('smownerid');
			App\PrivilegeQuery::getConditions($query, $this->getName());
			$dataReader = $query->createCommand()->query();
			$data = [];
			$ticks = [];
			$i = 0;
			while ($row = $dataReader->read()) {
				$name = App\Fields\Owner::getLabel($row['smownerid']);
				$data[$i]['label'] = $name;
				$ticks[$i][0] = $i;
				$ticks[$i][1] = $name;
				$data[$i]['data'][0][0] = $i;
				$data[$i]['data'][0][1] = $row['time'];
				++$i;
			}
			$dataReader->close();
			$response['ticks'] = $ticks;
			$response['chart'] = $data;
		}

		return $response;
	}
}
