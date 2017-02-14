<?php

/**
 * FInvoice Summation By Months Dashboard Class
 * @package YetiForce.Dashboard
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class FInvoice_SummationByMonths_Dashboard extends Vtiger_IndexAjax_View
{

	private $conditions = false;

	/**
	 * Process
	 * @param Vtiger_Request $request
	 */
	public function process(Vtiger_Request $request)
	{
		$linkId = $request->get('linkid');

		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userId = $currentUser->getId();

		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$widget = Vtiger_Widget_Model::getInstance($linkId, $userId);
		if (!$request->has('owner'))
			$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget);
		else
			$owner = $request->get('owner');
		$data = $this->getWidgetData($moduleName, $owner);

		$viewer->assign('USERID', $owner);
		$viewer->assign('DATA', $data);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('CURRENTUSER', $currentUser);
		$accessibleUsers = \App\Fields\Owner::getInstance($moduleName, $currentUser)->getAccessibleUsersForModule();
		$accessibleGroups = \App\Fields\Owner::getInstance($moduleName, $currentUser)->getAccessibleGroupForModule();
		$viewer->assign('ACCESSIBLE_USERS', $accessibleUsers);
		$viewer->assign('ACCESSIBLE_GROUPS', $accessibleGroups);
		$viewer->assign('USER_CONDITIONS', $this->conditions);
		$content = $request->get('content');
		if (!empty($content)) {
			$viewer->view('dashboards/SummationByMonthsContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/SummationByMonths.tpl', $moduleName);
		}
	}

	/**
	 * Get widget data
	 * @param string $moduleName
	 * @param int|string $owner
	 * @return array
	 */
	public function getWidgetData($moduleName, $owner)
	{
		$rawData = $data = $response = $ticks = $years = [];
		$date = date('Y-m-01', strtotime('-23 month', strtotime(date('Y-m-d'))));
		$displayDate = \App\Fields\DateTime::currentUserDisplayDate($date);
		$queryGenerator = new \App\QueryGenerator($moduleName);
		$y = new \yii\db\Expression('extract(year FROM saledate)');
		$m = new \yii\db\Expression('extract(month FROM saledate)');
		$s = new \yii\db\Expression('sum(sum_gross)');
		$fieldList = ['y' => $y, 'm' => $m, 's' => $s];
		$queryGenerator->setCustomColumn($fieldList);
		$queryGenerator->addCondition('saledate', $displayDate, 'a');
		if ($owner !== 'all') {
			$queryGenerator->addCondition('assigned_user_id', $owner, 'e');
		}
		$queryGenerator->setCustomGroup([new \yii\db\Expression('y'), new \yii\db\Expression('m')]);
		$query = $queryGenerator->createQuery();
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$rawData[$row['y']][] = [$row['m'], (int) $row['s']];
		}

		$this->conditions = ['condition' => ['>', 'saledate', $date]];
		foreach ($rawData as $y => $raw) {
			$years[] = $y;
		}
		$years = array_values(array_unique($years));
		foreach ($rawData as $y => $raw) {
			$values = [];
			foreach ($raw as $m => &$value) {
				$plus = array_search($y, $years) % 2 == 0 ? 0.45 : 0;
				$value[0] = $value[0] - $plus;
				$values[] = $value;
			}
			$data[] = [
				'data' => $values,
				'bars' => ['order' => (array_search($y, $years) + 1)],
				'label' => vtranslate('LBL_YEAR', $moduleName) . ' ' . $y,
			];
		}
		$response['chart'] = $data;
		$response['ticks'] = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
		return $response;
	}
}
