<?php

/**
 * FInvoice Summation By User Dashboard Class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class FInvoice_SummationByUser_Dashboard extends Vtiger_IndexAjax_View
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$widget = Vtiger_Widget_Model::getInstance($request->getInteger('linkid'), \App\User::getCurrentUserId());
		$time = $request->getDateRange('time');
		if (empty($time)) {
			$time = Settings_WidgetsManagement_Module_Model::getDefaultDateRange($widget);
		}
		$moduleName = $request->getModule();
		$param = \App\Json::decode($widget->get('data'));
		$data = $this->getWidgetData($moduleName, $param, $time);
		$viewer = $this->getViewer($request);
		$viewer->assign('DTIME', \App\Fields\Date::formatRangeToDisplay($time));
		$viewer->assign('DATA', $data);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('PARAM', $param);
		$viewer->assign('MODULE_NAME', $moduleName);
		if ($request->has('content')) {
			$viewer->view('dashboards/SummationByUserContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/SummationByUser.tpl', $moduleName);
		}
	}

	/**
	 * Get widget data.
	 *
	 * @param string $moduleName
	 * @param array  $widgetParam
	 * @param array  $time
	 *
	 * @return array
	 */
	public function getWidgetData($moduleName, $widgetParam, $time)
	{
		$currentUserId = \App\User::getCurrentUserId();

		$s = new \yii\db\Expression('sum(sum_gross)');
		$queryGenerator = new \App\QueryGenerator($moduleName);
		$queryGenerator->setField('assigned_user_id')
			->setCustomColumn(['s' => $s])
			->addCondition('saledate', implode(',', $time), 'bw')
			->setGroup('assigned_user_id');
		$dataReader = $queryGenerator->createQuery()
			->orderBy(['s' => SORT_DESC])
			->having(['>', $s, 0])
			->createCommand()->query();

		$chartData = [
			'show_chart' => false,
		];

		$datasetIndex = 0;
		while ($row = $dataReader->read()) {
			$label = trim(\App\Fields\Owner::getLabel($row['assigned_user_id']));
			$fullName = '';

			$name = empty($widgetParam['showUsers']) ? ' ' : \App\Utils::getInitials($label);
			if (!empty($widgetParam['showUsers']) || $currentUserId === (int) $row['assigned_user_id']) {
				$name = \App\Utils::getInitials($label);
				$fullName = $label;
			}

			$color = $currentUserId === (int) $row['assigned_user_id'] ? \App\Fields\Owner::getColor($row['assigned_user_id']) : 'rgba(0,0,0,0.25)';
			$chartData['series'][$datasetIndex]['data'][] = ['value' => round((float) $row['s'], 2), 'name' => $name, 'itemStyle' => ['color' => $color], 'link' => '', 'fullName' => $fullName];
			$chartData['series'][$datasetIndex]['type'] = 'bar';
			$chartData['xAxis']['data'][] = $name;
			$chartData['show_chart'] = true;
		}

		$dataReader->close();
		return $chartData;
	}
}
