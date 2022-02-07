<?php

/**
 * OSSTimeControl TimeControl dashboard class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSTimeControl_TimeControl_Dashboard extends Vtiger_IndexAjax_View
{
	/**
	 * Return search params (use to in building address URL to listview).
	 *
	 * @param int|string $owner
	 * @param string     $date
	 * @param mixed      $assignedto
	 *
	 * @return string
	 */
	public function getSearchParams($assignedto, $date)
	{
		$conditions = [];
		$date = \App\Fields\Date::formatToDisplay($date);
		$listSearchParams = [];
		if ('' != $assignedto) {
			array_push($conditions, ['assigned_user_id', 'e', $assignedto]);
		}
		if (!empty($date)) {
			array_push($conditions, ['due_date', 'bw', $date . ',' . $date]);
		}
		$listSearchParams[] = $conditions;
		return '&search_params=' . json_encode($listSearchParams);
	}

	public function getWidgetTimeControl($user, $date)
	{
		if (!$date) {
			return ['show_chart' => false];
		}
		$query = (new App\Db\Query())->select(['sum_time', 'due_date', 'vtiger_osstimecontrol.timecontrol_type', 'timecontrol_typeid'])
			->from('vtiger_osstimecontrol')
			->innerJoin('vtiger_crmentity', 'vtiger_osstimecontrol.osstimecontrolid = vtiger_crmentity.crmid')
			->innerJoin('vtiger_timecontrol_type', 'vtiger_osstimecontrol.timecontrol_type = vtiger_timecontrol_type.timecontrol_type')
			->where(['vtiger_crmentity.setype' => 'OSSTimeControl', 'vtiger_crmentity.smownerid' => $user]);
		\App\PrivilegeQuery::getConditions($query, 'OSSTimeControl');
		$query->andWhere([
			'and',
			['>=', 'vtiger_osstimecontrol.due_date', $date[0]],
			['<=', 'vtiger_osstimecontrol.due_date', $date[1]],
			['vtiger_osstimecontrol.deleted' => 0],
		])->orderBy('due_date');
		$timeTypes = [];
		$colors = \App\Fields\Picklist::getColors('timecontrol_type');
		$chartData = [
			'labels' => [],
			'fullLabels' => [],
			'datasets' => [],
			'show_chart' => false,
			'days' => []
		];
		$dataReader = $query->createCommand()->query();
		$workingTimeByType = $workingTime = [];
		while ($row = $dataReader->read()) {
			$label = \App\Language::translate($row['timecontrol_type'], 'OSSTimeControl');
			if (isset($workingTimeByType[$label])) {
				$workingTimeByType[$label] += (float) $row['sum_time'];
			} else {
				$workingTimeByType[$label] = (float) $row['sum_time'];
			}
			if (isset($workingTime[$row['due_date']][$row['timecontrol_type']])) {
				$workingTime[$row['due_date']][$row['timecontrol_type']] += (float) $row['sum_time'];
			} else {
				$workingTime[$row['due_date']][$row['timecontrol_type']] = (float) $row['sum_time'];
			}
			if (!\in_array($row['timecontrol_type'], $timeTypes)) {
				$timeTypes[$row['timecontrol_typeid']] = $row['timecontrol_type'];
				// one dataset per type
				$chartData['datasets'][] = [
					'label' => $label,
					'original_label' => $label,
					'_type' => $row['timecontrol_type'],
					'data' => [],
					'dataFormatted' => [],
					'backgroundColor' => [],
					'links' => [],
				];
			}
			if (!\in_array($row['due_date'], $chartData['days'])) {
				$chartData['labels'][] = substr($row['due_date'], -2);
				$chartData['fullLabels'][] = \App\Fields\DateTime::formatToDay($row['due_date'], true);
				$chartData['days'][] = $row['due_date'];
			}
		}
		$dataReader->close();
		foreach ($chartData['datasets'] as &$dataset) {
			$dataset['label'] .= ': ' . \App\Fields\RangeTime::displayElapseTime($workingTimeByType[$dataset['label']]);
		}
		if ($dataReader->count() > 0) {
			$chartData['show_chart'] = true;
			foreach ($workingTime as $timeValue) {
				foreach ($timeTypes as $timeTypeId => $timeType) {
					if (isset($timeValue[$timeType]) && !empty($timeValue[$timeType])) {
						$value = $timeValue[$timeType];
					} else {
						$value = 0;
					}
					foreach ($chartData['datasets'] as &$dataset) {
						if ($dataset['_type'] === $timeType) {
							// each data item is an different day in this dataset/time type
							$dataset['data'][] = round($value / 60, 2);
							$dataset['dataFormatted'][] = \App\Fields\RangeTime::displayElapseTime($value);
							$dataset['backgroundColor'][] = $colors[$timeTypeId];
						}
					}
				}
			}
		}
		$dataReader->close();
		return $chartData;
	}

	public function process(App\Request $request)
	{
		$currentUserId = \App\User::getCurrentUserId();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$user = $request->getByType('user', 2);
		$time = $request->getDateRange('time');
		$widget = Vtiger_Widget_Model::getInstance($request->getInteger('linkid'), $currentUserId);
		if (empty($time)) {
			$time = Settings_WidgetsManagement_Module_Model::getDefaultDateRange($widget);
		}
		if (empty($user)) {
			$user = $currentUserId;
		}
		$data = $this->getWidgetTimeControl($user, $time);
		if (!empty($data['datasets'])) {
			foreach ($data['datasets'] as &$dataset) {
				foreach ($dataset['data'] as $index => $dataItem) {
					$dataset['links'][] = 'index.php?module=OSSTimeControl&view=List&viewname=All&entityState=Active' . $this->getSearchParams($user, $data['days'][$index]);
				}
			}
		}
		$TCPModuleModel = Settings_TimeControlProcesses_Module_Model::getCleanInstance();
		$viewer->assign('USER_CONDITIONS', null);
		$viewer->assign('TCPMODULE_MODEL', $TCPModuleModel->getConfigInstance());
		$viewer->assign('USERID', $user);
		$viewer->assign('DTIME', \App\Fields\Date::formatRangeToDisplay($time));
		$viewer->assign('DATA', $data);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('LOGGEDUSERID', $currentUserId);
		$viewer->assign('SOURCE_MODULE', 'OSSTimeControl');
		if ($request->has('content')) {
			$viewer->view('dashboards/TimeControlContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/TimeControl.tpl', $moduleName);
		}
	}
}
