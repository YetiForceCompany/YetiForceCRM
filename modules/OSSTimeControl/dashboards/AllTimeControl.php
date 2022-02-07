<?php

/**
 * Wdiget to show work time.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class OSSTimeControl_AllTimeControl_Dashboard extends Vtiger_IndexAjax_View
{
	public function getSearchParams($assignedto, $date)
	{
		$conditions = [];
		$listSearchParams = [];
		if ('' != $assignedto) {
			array_push($conditions, ['assigned_user_id', 'e', $assignedto]);
		}
		if (!empty($date)) {
			array_push($conditions, ['due_date', 'bw', implode(',', $date)]);
		}
		$listSearchParams[] = $conditions;
		return '&search_params=' . json_encode($listSearchParams);
	}

	public function getWidgetTimeControl($user, $time)
	{
		if (!$time) {
			return ['show_chart' => false];
		}
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if ('all' == $user) {
			$user = array_keys(\App\Fields\Owner::getInstance(false, $currentUser)->getAccessibleUsers());
		}
		if (!\is_array($user)) {
			$user = [$user];
		}
		$colors = \App\Fields\Picklist::getColors('timecontrol_type');
		$moduleName = 'OSSTimeControl';
		$query = (new App\Db\Query())->select(['sum_time', 'due_date', 'vtiger_osstimecontrol.timecontrol_type', 'vtiger_crmentity.smownerid', 'timecontrol_typeid'])
			->from('vtiger_osstimecontrol')
			->innerJoin('vtiger_crmentity', 'vtiger_osstimecontrol.osstimecontrolid = vtiger_crmentity.crmid')
			->innerJoin('vtiger_timecontrol_type', 'vtiger_osstimecontrol.timecontrol_type = vtiger_timecontrol_type.timecontrol_type')
			->where(['vtiger_crmentity.setype' => $moduleName, 'vtiger_crmentity.smownerid' => $user]);
		\App\PrivilegeQuery::getConditions($query, $moduleName);
		$query->andWhere([
			'and',
			['>=', 'vtiger_osstimecontrol.due_date', $time[0]],
			['<=', 'vtiger_osstimecontrol.due_date', $time[1]],
			['vtiger_osstimecontrol.deleted' => 0],
		]);
		$timeTypes = [];
		$smOwners = [];
		$dataReader = $query->createCommand()->query();
		$chartData = [
			'labels' => [],
			'fullLabels' => [],
			'datasets' => [],
			'show_chart' => false,
		];
		$time = \App\Fields\Date::formatRangeToDisplay($time);
		$workingTimeByType = $workingTime = [];
		while ($row = $dataReader->read()) {
			$label = \App\Language::translate($row['timecontrol_type'], 'OSSTimeControl');
			if (isset($workingTimeByType[$label])) {
				$workingTimeByType[$label] += (float) $row['sum_time'];
			} else {
				$workingTimeByType[$label] = (float) $row['sum_time'];
			}
			if (isset($workingTime[$row['smownerid']][$row['timecontrol_type']])) {
				$workingTime[$row['smownerid']][$row['timecontrol_type']] += (float) $row['sum_time'];
			} else {
				$workingTime[$row['smownerid']][$row['timecontrol_type']] = (float) $row['sum_time'];
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
			if (!\in_array($row['smownerid'], $smOwners)) {
				$smOwners[] = $row['smownerid'];
				$ownerName = \App\Fields\Owner::getUserLabel($row['smownerid']);
				$chartData['labels'][] = \App\Utils::getInitials($ownerName);
				$chartData['fullLabels'][] = $ownerName;
			}
		}
		foreach ($chartData['datasets'] as &$dataset) {
			$dataset['label'] .= ': ' . \App\Fields\RangeTime::displayElapseTime($workingTimeByType[$dataset['label']]);
		}
		if ($dataReader->count() > 0) {
			$chartData['show_chart'] = true;
			foreach ($workingTime as $ownerId => $timeValue) {
				foreach ($timeTypes as $timeTypeId => $timeType) {
					// if owner has this kind of type
					if (!empty($timeValue[$timeType])) {
						$userTime = $timeValue[$timeType];
					} else {
						$userTime = 0;
					}
					foreach ($chartData['datasets'] as &$dataset) {
						if ($dataset['_type'] === $timeType) {
							// each data item is an different owner time in this dataset/time type
							$dataset['data'][] = round($userTime / 60, 2);
							$dataset['backgroundColor'][] = $colors[$timeTypeId];
							$dataset['dataFormatted'][] = \App\Fields\RangeTime::displayElapseTime($userTime);
						}
					}
				}
			}
			foreach ($smOwners as $ownerId) {
				foreach ($chartData['datasets'] as &$dataset) {
					$dataset['links'][] = 'index.php?module=OSSTimeControl&view=List&viewname=All&entityState=Active' . $this->getSearchParams($ownerId, $time);
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
		$user = $request->getByType('owner', 2);
		$time = $request->getDateRange('time');
		$widget = Vtiger_Widget_Model::getInstance($request->getInteger('linkid'), $currentUserId);
		if (empty($time)) {
			$time = Settings_WidgetsManagement_Module_Model::getDefaultDateRange($widget);
		}
		if (empty($user)) {
			$user = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget);
		}
		$viewer->assign('TCPMODULE_MODEL', Settings_TimeControlProcesses_Module_Model::getCleanInstance()->getConfigInstance());
		$viewer->assign('OWNER', $user);
		$viewer->assign('DTIME', \App\Fields\Date::formatRangeToDisplay($time));
		$viewer->assign('DATA', $this->getWidgetTimeControl($user, $time));
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('LOGGEDUSERID', $currentUserId);
		$viewer->assign('ACCESSIBLE_USERS', \App\Fields\Owner::getInstance($moduleName, $currentUserId)->getAccessibleUsersForModule());
		$viewer->assign('ACCESSIBLE_GROUPS', \App\Fields\Owner::getInstance($moduleName, $currentUserId)->getAccessibleGroupForModule());
		if ($request->has('content')) {
			$viewer->view('dashboards/TimeControlContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/AllTimeControl.tpl', $moduleName);
		}
	}
}
