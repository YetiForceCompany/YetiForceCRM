<?php

/**
 * Widget showing ticket which have closed. We can filter by date 
 * @package YetiForce.Dashboard
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class HelpDesk_ClosedTicketsByUser_Dashboard extends Vtiger_IndexAjax_View
{

	/**
	 * Return search params (use to in building address URL to listview)
	 * @param int $owner numer id of user
	 * @param <Array> $time contain start date and end time
	 * @return string 
	 */
	public function getSearchParams($owner, $time)
	{
		$listSearchParams = [];
		$conditions = [];
		if (!empty($time)) {
			$conditions [] = ['closedtime', 'bw', implode(',', $time)];
		}
		if (!empty($owner)) {
			$conditions [] = ['assigned_user_id', 'e', $owner];
		}
		$listSearchParams[] = $conditions;
		return '&viewname=All&search_params=' . json_encode($listSearchParams);
	}

	/**
	 * Function returns Tickets grouped by users
	 * @param <Array> $time contain start date and end time
	 * @return <Array> data to display chart
	 */
	public function getTicketsByUser($time)
	{
		$db = PearDatabase::getInstance();
		$moduleName = 'HelpDesk';
		$time['start'] = DateTimeField::convertToDBFormat($time['start']);
		$time['end'] = DateTimeField::convertToDBFormat($time['end']);
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$ticketStatus = Settings_SupportProcesses_Module_Model::getTicketStatusNotModify();
		$listViewUrl = $moduleModel->getListViewUrl();
		$paramsQuery = [];
		$sql = 'SELECT COUNT(*) AS `count` , vtiger_crmentity.smownerid
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
		$sql.= \App\PrivilegeQuery::getAccessConditions($moduleName);
		$sql .= ' GROUP BY vtiger_crmentity.smownerid';
		$result = $db->pquery($sql, $paramsQuery);

		$response = [];
		while ($row = $db->getRow($result)) {
			$response[] = [
				$row['count'],
				\includes\fields\Owner::getLabel($row['smownerid']),
				$listViewUrl . $this->getSearchParams($row['smownerid'], $time),
			];
		}
		return $response;
	}

	/**
	 * Main function 
	 * @param <Vtiger_Request> $request 
	 */
	public function process(Vtiger_Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$linkId = $request->get('linkid');
		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		$time = $request->get('time');
		if (empty($time)) {
			$time['start'] = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
			$time['end'] = date('Y-m-d', mktime(23, 59, 59, date('m') + 1, 0, date('Y')));
			$time['start'] = vtlib\Functions::currentUserDisplayDate($time['start']);
			$time['end'] = vtlib\Functions::currentUserDisplayDate($time['end']);
		}
		$data = $this->getTicketsByUser($time);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		$viewer->assign('DTIME', $time);
		$content = $request->get('content');
		if (!empty($content)) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/ClosedTicketsByUser.tpl', $moduleName);
		}
	}
}
