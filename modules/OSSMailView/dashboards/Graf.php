<?php

/**
 * OSSMailView graf dashboard class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMailView_Graf_Dashboard extends Vtiger_IndexAjax_View
{
	/**
	 * Return params to search.
	 *
	 * @param string $stage
	 * @param int    $assignedTo
	 * @param string $dates
	 *
	 * @return void
	 */
	public function getSearchParams($stage, $assignedTo, $dates)
	{
		$conditions = [];
		$conditions[] = ['ossmailview_sendtype', 'e', $stage];
		if ('all' !== $assignedTo) {
			$conditions[] = ['assigned_user_id', 'e', $assignedTo];
		}
		if (!empty($dates)) {
			$conditions[] = ['createdtime', 'bw', \App\Fields\DateTime::formatToDisplay($dates['start']) . ',' . \App\Fields\DateTime::formatToDisplay($dates['end'])];
		}

		return '&search_params=' . json_encode([$conditions]);
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$userId = \App\User::getCurrentUserId();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$linkId = $request->getInteger('linkid');
		$owner = $request->getByType('owner', 'Alnum');
		$dates = $request->getByType('dateFilter', 'Text');
		$userList = \App\Fields\Owner::getInstance($moduleName, $userId)->getAccessibleUsersForModule();
		$groupList = \App\Fields\Owner::getInstance($moduleName, $userId)->getAccessibleGroupForModule();
		if (!$owner) {
			$owner = $userId;
		} elseif (is_numeric($owner) && !isset($userList[$owner]) && !isset($groupList[$owner])) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}

		$today = date('Y-m-d');
		if ('Yesterday' === $dates) {
			$data = strtotime('-1 day', strtotime($today));
			$dateFilter['start'] = date('Y-m-d', $data);
			$dateFilter['end'] = date('Y-m-d', $data);
		} elseif ('Current week' === $dates) {
			if ('Mon' == date('D')) {
				$dateFilter['start'] = date('Y-m-d');
			} else {
				$data = strtotime('last Monday', strtotime($today));
				$dateFilter['start'] = date('Y-m-d', $data);
			}
			$dateFilter['end'] = date('Y-m-d');
		} elseif ('Previous week' === $dates) {
			$data = strtotime('last Monday', strtotime($today));
			if ('Mon' != date('D')) {
				$data = strtotime('last Monday', strtotime(date('Y-m-d', $data)));
			}
			$dateFilter['start'] = date('Y-m-d', $data);
			$data = strtotime('last Sunday', strtotime($today));
			$dateFilter['end'] = date('Y-m-d', $data);
		} elseif ('Current month' === $dates) {
			$dateFilter['start'] = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
			$dateFilter['end'] = $today;
		} elseif ('Previous month' === $dates) {
			$dateFilter['start'] = date('Y-m-d', mktime(0, 0, 0, date('m') - 1, 1, date('Y')));
			$dateFilter['end'] = date('Y-m-d', mktime(23, 59, 59, date('m'), 0, date('Y')));
		} elseif ('All' === $dates) {
			$dateFilter = '';
		} else {
			$dateFilter['start'] = $today;
			$dateFilter['end'] = $today;
		}
		$dateFilter['start'] .= ' 00:00:00';
		$dateFilter['end'] .= ' 23:59:59';

		$dataSets = [[
			'data' => [],
			'backgroundColor' => [],
			'borderColor' => [],
			'tooltips' => [],
			'names' => [],
			'links' => []
		]];
		$data = [
			'labels' => [],
			'datasets' => &$dataSets,
			'show_chart' => false,
		];

		$fieldName = 'ossmailview_sendtype';
		$colors = \App\Fields\Picklist::getValues($fieldName);
		$colors = array_column($colors, 'color', $fieldName);
		$listViewUrl = Vtiger_Module_Model::getInstance($moduleName)->getListViewUrl();

		$queryGenerator = (new \App\QueryGenerator($moduleName))
			->setFields([$fieldName])
			->setCustomColumn(['count' => new \yii\db\Expression('COUNT(*)')])
			->setGroup($fieldName)
			->addCondition($fieldName, '', 'ny')
			->addCondition('createdtime', $dateFilter['start'] . ',' . $dateFilter['end'], 'bw');
		if ('all' !== $owner) {
			$queryGenerator->addCondition('assigned_user_id', $owner, 'e');
		}
		$i = 0;
		$dataReader = $queryGenerator->createQuery()->createCommand()->query();
		while ($row = $dataReader->read()) {
			$color = !empty($colors[$row[$fieldName]]) ? '#' . $colors[$row[$fieldName]] : \App\Colors::getRandomColor($i);
			$data['labels'][$i] = \App\Language::translate($row[$fieldName], $moduleName);
			$dataSets[0]['data'][$i] = $row['count'];
			$dataSets[0]['names'][$i] = $row[$fieldName];
			$dataSets[0]['backgroundColor'][$i] = $color;
			$dataSets[0]['borderColor'][$i] = $color;
			$dataSets[0]['links'][$i] = $listViewUrl . $this->getSearchParams($row[$fieldName], $owner, $dateFilter);
			$data['show_chart'] = true;
			++$i;
		}
		$dataReader->close();

		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('WIDGET', Vtiger_Widget_Model::getInstance($linkId, $userId));
		$viewer->assign('ACCESSIBLE_USERS', $userList);
		$viewer->assign('ACCESSIBLE_GROUPS', $groupList);
		$viewer->assign('DATA', $data);
		if ($request->has('content')) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/Graf.tpl', $moduleName);
		}
	}
}
