<?php

/**
 * Widget showing ticket which have closed. We can filter by users or date 
 * @package YetiForce.Dashboard
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class HelpDesk_ClosedTicketsByPriority_Dashboard extends Vtiger_IndexAjax_View
{
	/**
	 * Return search params (use to in bulding address URL to listview)
	 * @param string $priority
	 * @param array $time
	 * @param int $owner
	 * @return string
	 */
	public function getSearchParams($priority, $time, $owner)
	{

		$listSearchParams = [];
		$conditions = [['ticketpriorities', 'e', $priority]];
		if (!empty($time)) {
			$conditions [] = ['closedtime', 'bw', implode(',', $time)];
		}
		if (!empty($owner) && $owner != 'all') {
			$conditions [] = ['assigned_user_id', 'e', $owner];
		}
		$listSearchParams[] = $conditions;
		return '&viewname=All&search_params=' . \App\Json::encode($listSearchParams);
	}

	/**
	 * Function returns Tickets grouped by priority
	 * @param array $time
	 * @param int $owner
	 * @return array
	 */
	public function getTicketsByPriority($time, $owner)
	{
		$moduleName = 'HelpDesk';
		$time['start'] = DateTimeField::convertToDBFormat($time['start']);
		$time['end'] = DateTimeField::convertToDBFormat($time['end']);
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$ticketStatus = Settings_SupportProcesses_Module_Model::getTicketStatusNotModify();
		$listViewUrl = $moduleModel->getListViewUrl();
		$query = (new App\Db\Query())->select([
			'count' => new \yii\db\Expression('COUNT(*)'),
			'priority',
			'vtiger_ticketpriorities.color'
		])->from('vtiger_troubletickets')
			->innerJoin('vtiger_crmentity', 'vtiger_troubletickets.ticketid = vtiger_crmentity.crmid')
			->innerJoin('vtiger_ticketstatus', 'vtiger_troubletickets.status = vtiger_ticketstatus.ticketstatus')
			->innerJoin('vtiger_ticketpriorities', 'vtiger_ticketpriorities.ticketpriorities = vtiger_troubletickets.priority')
			->where(['vtiger_crmentity.deleted' => 0]);
		if (!empty($ticketStatus)) {
			$query->andWhere(['vtiger_troubletickets.status' => $ticketStatus]);
		}
		if (!empty($time)) {
			$query->andWhere([
				'and',
				['>=', 'vtiger_crmentity.closedtime', $time['start']],
				['<=', 'vtiger_crmentity.closedtime', $time['end']]
			]);
		}
		if (!empty($owner) && $owner != 'all') {
			$query->andWhere(['vtiger_crmentity.smownerid' => $owner]);
		}
		\App\PrivilegeQuery::getConditions($query, $moduleName);
		$query->groupBy(['priority', 'vtiger_ticketpriorities.color']);
		$dataReader = $query->createCommand()->query();
		$response = [];
		while ($row = $dataReader->read()) {
			$response[] = [
				'name' => \App\Language::translate($row['priority'], $moduleName),
				'count' => $row['count'],
				'color' => $row['color'],
				'url' => $listViewUrl . $this->getSearchParams($row['priority'], $time, $owner),
			];
		}
		return $response;
	}

	public function process(Vtiger_Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$linkId = $request->get('linkid');
		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		$time = $request->get('time');
		$owner = $request->get('owner');
		if (empty($owner)) {
			$owner = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget);
		}
		if (empty($time)) {
			$time = Settings_WidgetsManagement_Module_Model::getDefaultDate($widget);
			if($time === false) {
				$time['start'] = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
				$time['end'] = date('Y-m-d', mktime(23, 59, 59, date('m') + 1, 0, date('Y')));
			}
			$time['start'] = \App\Fields\DateTime::currentUserDisplayDate($time['start']);
			$time['end'] = \App\Fields\DateTime::currentUserDisplayDate($time['end']);
		}
		$data = $this->getTicketsByPriority($time, $owner);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('CURRENTUSER', $currentUser);
		$viewer->assign('DATA', $data);
		$viewer->assign('DTIME', $time);
		$content = $request->get('content');
		if (!empty($content)) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/ClosedTicketsByPriority.tpl', $moduleName);
		}
	}
}
