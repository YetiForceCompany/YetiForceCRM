<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce S.A.
 * ********************************************************************************** */

class HelpDesk_TicketsByStatus_Dashboard extends Vtiger_IndexAjax_View
{
	private $conditions = false;

	public function getSearchParams($value, $assignedto = '')
	{
		$listSearchParams = [];
		$conditionsArray = [['ticketstatus', 'e', $value]];
		if (!empty($assignedto)) {
			array_push($conditionsArray, ['assigned_user_id', 'e', $assignedto]);
		}
		$listSearchParams[] = $conditionsArray;
		return '&entityState=Active&viewname=All&search_params=' . urlencode(json_encode($listSearchParams));
	}

	/**
	 * Function returns Tickets grouped by Status.
	 *
	 * @param int $owner
	 *
	 * @return array
	 */
	public function getTicketsByStatus($owner)
	{
		$moduleName = 'HelpDesk';
		$ticketStatus = Settings_SupportProcesses_Module_Model::getTicketStatusNotModify();
		$query = new \App\Db\Query();
		$query->select([
			'vtiger_troubletickets.priority',
			'vtiger_ticketpriorities.ticketpriorities_id',
			'count' => new \yii\db\Expression('COUNT(*)'),
			'statusvalue' => new \yii\db\Expression("CASE WHEN vtiger_troubletickets.status IS NULL OR vtiger_troubletickets.status = '' THEN '' ELSE vtiger_troubletickets.status END"), ])
			->from('vtiger_troubletickets')
			->innerJoin('vtiger_crmentity', 'vtiger_troubletickets.ticketid = vtiger_crmentity.crmid')
			->innerJoin('vtiger_ticketstatus', 'vtiger_troubletickets.status = vtiger_ticketstatus.ticketstatus')
			->leftJoin('vtiger_ticketpriorities', 'vtiger_troubletickets.priority = vtiger_ticketpriorities.ticketpriorities')
			->where(['vtiger_crmentity.deleted' => 0]);

		if (!empty($owner)) {
			$query->andWhere(['smownerid' => $owner]);
		}
		if (!empty($ticketStatus)) {
			$query->andWhere(['not in', 'vtiger_troubletickets.status', $ticketStatus]);
			$this->conditions = ['condition' => ['not in', 'vtiger_troubletickets.status', $ticketStatus]];
		}
		\App\PrivilegeQuery::getConditions($query, $moduleName);
		$query->groupBy(['statusvalue', 'vtiger_troubletickets.priority', 'vtiger_ticketpriorities.ticketpriorities_id', 'vtiger_ticketstatus.sortorderid'])->orderBy('vtiger_ticketstatus.sortorderid');

		$colors = \App\Fields\Picklist::getColors('ticketpriorities');
		$chartData = [
			'show_chart' => false,
		];

		$data = $query->all();
		$statuses = array_values(array_unique(array_column($data, 'statusvalue')));
		$chartData['xAxis']['data'] = array_map(fn ($value) => \App\Language::translate($value, $moduleName), $statuses);
		$priorities = array_values(array_unique(array_column($data, 'priority')));
		$listViewUrl = Vtiger_Module_Model::getInstance($moduleName)->getListViewUrl();

		foreach ($data as $row) {
			$status = $row['statusvalue'];
			$priority = $row['priority'];
			$count = $row['count'];
			$seriesIndex = array_search($priority, $priorities);

			if (empty($chartData['series'][$seriesIndex])) {
				foreach (array_keys($statuses) as $statusIndex) {
					$chartData['series'][$seriesIndex]['data'][$statusIndex] = ['value' => null];
				}
			}

			$statusIndex = array_search($status, $statuses);
			$color = $colors[$row['ticketpriorities_id']] ?? \App\Colors::getRandomColor($priority);
			$link = $listViewUrl . $this->getSearchParams($status, $owner);
			$label = $priority ? \App\Language::translate($priority, $moduleName, null, false) : '(' . \App\Language::translate('LBL_EMPTY', 'Home', null, false) . ')';

			$chartData['series'][$seriesIndex]['name'] = $label;
			$chartData['series'][$seriesIndex]['type'] = 'bar';
			$chartData['series'][$seriesIndex]['stack'] = 'total';
			$chartData['series'][$seriesIndex]['color'] = $color;
			$chartData['series'][$seriesIndex]['label'] = ['show' => true, 'position' => 'inside'];
			$chartData['tooltip'] = ['trigger' => 'axis', 'axisPointer' => ['type' => 'shadow']];
			$chartData['labelLayout'] = ['hideOverlap' => false];
			$chartData['series'][$seriesIndex]['data'][$statusIndex] = ['value' => $count, 'itemStyle' => ['color' => $color], 'link' => $link];

			$chartData['show_chart'] = true;
		}

		return $chartData;
	}

	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$widget = Vtiger_Widget_Model::getInstance($request->getInteger('linkid'), \App\User::getCurrentUserId());
		if (!$request->has('owner')) {
			$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget, $moduleName);
		} else {
			$owner = $request->getByType('owner', 2);
		}
		$ownerForwarded = $owner;
		if ('all' == $owner) {
			$owner = '';
		}
		$data = (false === $owner) ? [] : $this->getTicketsByStatus($owner);

		$viewer->assign('USER_CONDITIONS', $this->conditions);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		$viewer->assign('OWNER', $ownerForwarded);
		if ($request->has('content')) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/TicketsByStatus.tpl', $moduleName);
		}
	}
}
