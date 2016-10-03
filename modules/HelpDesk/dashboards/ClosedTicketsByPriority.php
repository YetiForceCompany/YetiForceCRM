<?php

/**
 * Save issue to github
 * @package YetiForce.Github
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class HelpDesk_ClosedTicketsByPriority_Dashboard extends Vtiger_IndexAjax_View
{

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
		return '&viewname=All&search_params=' . json_encode($listSearchParams);
	}

	/**
	 * Function returns Tickets grouped by Status
	 * @param type $data
	 * @return <Array>
	 */
	public function getTicketsByPriority($time, $owner)
	{
		$db = PearDatabase::getInstance();
		$moduleName = 'HelpDesk';
		$time['start'] = DateTimeField::convertToDBFormat($time['start']);
		$time['end'] = DateTimeField::convertToDBFormat($time['end']);
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$ticketStatus = Settings_SupportProcesses_Module_Model::getTicketStatusNotModify();
		$listViewUrl = $moduleModel->getListViewUrl();
		$paramsQuery = [];
		$sql = 'SELECT COUNT(*) AS `count` , priority, vtiger_ticketpriorities.color 
				FROM vtiger_troubletickets 
				INNER JOIN vtiger_crmentity ON vtiger_troubletickets.ticketid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted=0 
				INNER JOIN vtiger_ticketstatus ON vtiger_troubletickets.status = vtiger_ticketstatus.ticketstatus 
				INNER JOIN vtiger_ticketpriorities ON vtiger_ticketpriorities.`ticketpriorities` = vtiger_troubletickets.`priority` 
				WHERE vtiger_crmentity.`deleted` = 0 ';
		if (!empty($ticketStatus)) {
			$paramsQuery = $ticketStatus;
			$sql .= ' AND vtiger_troubletickets.status IN (' . generateQuestionMarks($ticketStatus) . ')';
		}
		if (!empty($time)) {
			$sql .= ' AND vtiger_crmentity.closedtime >= ? AND vtiger_crmentity.closedtime <= ?';
			$paramsQuery [] = $time['start'];
			$paramsQuery [] = $time['end'];
		}
		if (!empty($owner) && $owner != 'all') {
			$sql .= ' AND vtiger_crmentity.smownerid = ?';
			$paramsQuery [] = $owner;
		}
		$sql.= \App\PrivilegeQuery::getAccessConditions($moduleName);
		$sql .= ' GROUP BY priority';
		$result = $db->pquery($sql, $paramsQuery);

		$response = [];
		while ($row = $db->getRow($result)) {
			$response[] = [
				'name' => \includes\Language::translate($row['priority'], $moduleName),
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
			$time['start'] = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
			$time['end'] = date('Y-m-d', mktime(23, 59, 59, date('m') + 1, 0, date('Y')));
			$time['start'] = vtlib\Functions::currentUserDisplayDate($time['start']);
			$time['end'] = vtlib\Functions::currentUserDisplayDate($time['end']);
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
