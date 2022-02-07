<?php

/**
 * FInvoice Summation By Months Dashboard Class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class FInvoice_SummationByMonths_Dashboard extends Vtiger_IndexAjax_View
{
	private $conditions = false;

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$currentUserId = \App\User::getCurrentUserId();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$widget = Vtiger_Widget_Model::getInstance($request->getInteger('linkid'), $currentUserId);
		if (!$request->has('owner')) {
			$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget);
		} else {
			$owner = $request->getByType('owner', \App\Purifier::TEXT);
		}
		$fields = $this->getFilterFields($moduleName);
		foreach ($fields as $fieldModel) {
			if ($request->has($fieldModel->getName()) && '' !== ($value = $request->getForSql($fieldModel->getName())) && isset($fieldModel->getPicklistValues()[$value])) {
				$fieldModel->set('fieldvalue', $value);
			}
		}
		$data = $fields ? $this->getWidgetData($moduleName, $owner) : [];
		$viewer->assign('OWNER', $owner);
		$viewer->assign('DATA', $data);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$accessibleUsers = \App\Fields\Owner::getInstance($moduleName, $currentUserId)->getAccessibleUsersForModule();
		$accessibleGroups = \App\Fields\Owner::getInstance($moduleName, $currentUserId)->getAccessibleGroupForModule();
		$viewer->assign('ACCESSIBLE_USERS', $accessibleUsers);
		$viewer->assign('ACCESSIBLE_GROUPS', $accessibleGroups);
		$viewer->assign('USER_CONDITIONS', $this->conditions);
		$viewer->assign('FILTER_FIELDS', $fields);
		if ($request->has('content')) {
			$viewer->view('dashboards/SummationByMonthsContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/SummationByMonths.tpl', $moduleName);
		}
	}

	/**
	 * Gets filter fields.
	 *
	 * @param string $moduleName
	 *
	 * @return \Vtiger_Field_Model[]
	 */
	public function getFilterFields($moduleName): array
	{
		if (!isset($this->filterFields)) {
			$this->filterFields = [];
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			$fieldModel = $moduleModel->getFieldByName('sum_total');
			$fieldModelGross = $moduleModel->getFieldByName('sum_gross');
			$field = new \Vtiger_Field_Model();
			$field->set('name', 'sum_field')
				->set('column', 'sum_field')
				->set('label', 'DW_SUM_FIELD')
				->set('fromOutsideList', true)
				->set('uitype', 16)
				->set('icon', 'mdi mdi-sigma')
				->setModule($moduleModel);
			$picklistValues = [];
			if ($fieldModel && $fieldModel->isActiveField()) {
				$picklistValues[$fieldModel->getColumnName()] = \App\Language::translate($fieldModel->getFieldLabel(), $fieldModel->getModuleName());
				$field->set('fieldvalue', $fieldModel->getColumnName());
			}
			if ($fieldModelGross && $fieldModelGross->isActiveField()) {
				$picklistValues[$fieldModelGross->getColumnName()] = \App\Language::translate($fieldModelGross->getFieldLabel(), $fieldModelGross->getModuleName());
				$field->set('fieldvalue', $fieldModelGross->getColumnName());
			}
			if ($picklistValues) {
				$field->set('picklistValues', $picklistValues);
				$this->filterFields[$field->getName()] = $field;
			}
		}
		return $this->filterFields;
	}

	/**
	 * Get widget data.
	 *
	 * @param string     $moduleName
	 * @param int|string $owner
	 *
	 * @return array
	 */
	public function getWidgetData($moduleName, $owner)
	{
		$rawData = $data = $years = [];
		$dateStart = ((int) date('Y') - 2) . '-01-01';
		$dateEnd = date('Y-m-d', strtotime('last day of december'));
		$date = "{$dateStart},{$dateEnd}";
		$queryGenerator = new \App\QueryGenerator($moduleName);
		$sumColumnName = $this->getFilterFields($moduleName)['sum_field']->get('fieldvalue') ?: 'sum_gross';
		$y = new \yii\db\Expression('extract(year FROM saledate)');
		$m = new \yii\db\Expression('extract(month FROM saledate)');
		$s = new \yii\db\Expression("SUM({$sumColumnName})");
		$fieldList = ['y' => $y, 'm' => $m, 's' => $s];
		$queryGenerator->setCustomColumn($fieldList);
		$queryGenerator->addCondition('saledate', $date, 'bw');
		if ('all' !== $owner) {
			$queryGenerator->addCondition('assigned_user_id', $owner, 'e');
		}
		$queryGenerator->setCustomGroup([new \yii\db\Expression('y'), new \yii\db\Expression('m')]);
		$query = $queryGenerator->createQuery();
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$rawData[$row['y']][$row['m']] = [round((float) $row['s'], 2)];
		}
		$dataReader->close();
		$chartData = [
			'labels' => [],
			'datasets' => [],
			'show_chart' => false,
		];
		$this->conditions = ['condition' => ['between', 'saledate', $dateStart, $dateEnd]];
		$yearsData = $tempData = [];
		$chartData['show_chart'] = (bool) \count($rawData);
		$shortMonth = ['LBL_Jan', 'LBL_Feb', 'LBL_Mar', 'LBL_Apr', 'LBL_May', 'LBL_Jun',
			'LBL_Jul', 'LBL_Aug', 'LBL_Sep', 'LBL_Oct', 'LBL_Nov', 'LBL_Dec', ];
		for ($i = 0; $i < 12; ++$i) {
			$chartData['labels'][] = App\Language::translate($shortMonth[$i]);
		}
		foreach ($rawData as $y => $raw) {
			$years[] = $y;
			if (!isset($yearsData[$y])) {
				$yearsData[$y] = [
					'data' => [],
					'label' => \App\Language::translate('LBL_YEAR', $moduleName) . ' ' . $y,
					'backgroundColor' => [],
				];
				for ($m = 0; $m < 12; ++$m) {
					$tempData[$y][$m] = 0;
					$yearsData[$y]['backgroundColor'][] = \App\Colors::getRandomColor($y * 10);
				}
			}
			foreach ($raw as $m => $value) {
				$tempData[$y][$m - 1] = $value[0];
				$yearsData[$y]['stack'] = (string) $y;
			}
		}
		foreach ($tempData as $year => $yearData) {
			$yearsData[$year]['data'] = $yearData;
		}
		$years = array_values(array_unique($years));
		$chartData['years'] = $years;
		foreach ($yearsData as $data) {
			$chartData['datasets'][] = $data;
		}
		return $chartData;
	}
}
