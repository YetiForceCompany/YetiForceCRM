<?php

/**
 * FInvoice Summation By User Dashboard Class
 * @package YetiForce.Dashboard
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class FInvoice_SummationByUser_Dashboard extends Vtiger_IndexAjax_View
{

	/**
	 * Process
	 * @param Vtiger_Request $request
	 */
	public function process(Vtiger_Request $request)
	{
		$linkId = $request->get('linkid');
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userId = $currentUser->getId();
		$widget = Vtiger_Widget_Model::getInstance($linkId, $userId);
		if ($request->has('time')) {
			$time = $request->get('time');
		} else {
			$time = Settings_WidgetsManagement_Module_Model::getDefaultDate($widget);
			if ($time === false) {
				$time['start'] = date('Y-m-01');
				$time['end'] = date('Y-m-t');
			}
			// date parameters passed, convert them to YYYY-mm-dd
			$time['start'] = \App\Fields\DateTime::currentUserDisplayDate($time['start']);
			$time['end'] = \App\Fields\DateTime::currentUserDisplayDate($time['end']);
		}

		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$param = \App\Json::decode($widget->get('data'));
		$data = $this->getWidgetData($moduleName, $param, $time);

		$viewer->assign('DTIME', $time);
		$viewer->assign('DATA', $data);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('PARAM', $param);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('CURRENTUSER', $currentUser);
		$content = $request->get('content');
		if (!empty($content)) {
			$viewer->view('dashboards/SummationByUserContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/SummationByUser.tpl', $moduleName);
		}
	}

	/**
	 * Get widget data
	 * @param string $moduleName
	 * @param array $widgetParam
	 * @param string $time
	 * @return array
	 */
	public function getWidgetData($moduleName, $widgetParam, $time)
	{
		$rawData = $response = $ticks = [];
		$currentUserId = \App\User::getCurrentUserId();
		$param = $time['start'] . ',' . $time['end'];

		$s = new \yii\db\Expression('sum(sum_gross)');
		$queryGenerator = new \App\QueryGenerator($moduleName);
		$queryGenerator->setField('assigned_user_id');
		$queryGenerator->setCustomColumn(['s' => $s]);
		$queryGenerator->addCondition('saledate', $param, 'bw');
		$queryGenerator->setGroup('assigned_user_id');
		$query = $queryGenerator->createQuery();
		$query->orderBy(['s' => SORT_DESC]);
		$query->having(['>', $s, 0]);
		$dataReader = $query->createCommand()->query();

		$i = 0;
		while ($row = $dataReader->read()) {
			$color = '#EDC240';
			if ($currentUserId === $row['assigned_user_id']) {
				$color = '#4979aa';
			}
			$owner = \App\Fields\Owner::getLabel($row['assigned_user_id']);
			$rawData[] = [
				'data' => [[++$i, (int) $row['s']]],
				'label' => $owner,
				'color' => $color
			];
		}
		$response['ticks'] = $ticks;
		$response['chart'] = $rawData;
		return $response;
	}
}
