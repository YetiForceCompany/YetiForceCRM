<?php

/**
 * Notifications Dashboard Class
 * @package YetiForce.Dashboard
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Notification_NotificationsBySender_Dashboard extends Vtiger_IndexAjax_View
{

	/**
	 * Return search params (use to in building address URL to listview)
	 * @param string $owner Name of user
	 * @param array $time
	 * @return string
	 */
	public function getSearchParams($owner, $time)
	{
		$listSearchParams = [];
		$conditions = [];
		if (!empty($time)) {
			$conditions [] = ['createdtime', 'bw', implode(',', $time)];
		}
		if (!empty($owner)) {
			$conditions [] = ['smcreatorid', 'e', $owner];
		}
		$listSearchParams[] = $conditions;
		return '&viewname=All&search_params=' . json_encode($listSearchParams);
	}

	/**
	 * Function to get data for chart Return number notification by sender
	 * @param array $time Contains start and end created time of natification
	 * @return array
	 */
	private function getNotificationBySender($time)
	{
		$accessibleUsers = \includes\fields\Owner::getInstance()->getAccessibleUsers();
		$moduleName = 'Notification';
		$listView = Vtiger_Module_Model::getInstance($moduleName)->getListViewUrl();
		$db = PearDatabase::getInstance();
		$time['start'] = DateTimeField::convertToDBFormat($time['start']);
		$time['end'] = DateTimeField::convertToDBFormat($time['end']);
		$query = 'SELECT COUNT(*) AS `count`, smcreatorid
			FROM vtiger_crmentity 
			WHERE setype = ? AND deleted = ? AND createdtime BETWEEN ? AND ? AND smcreatorid IN (%s) ' .
			\App\PrivilegeQuery::getAccessConditions($moduleName) .
			' GROUP BY smcreatorid';
		$query = sprintf($query, generateQuestionMarks($accessibleUsers));
		$params = array_merge([$moduleName, 0, $time['start'], $time['end']], array_keys($accessibleUsers));
		$result = $db->pquery($query, $params);
		$data = [];
		while ($row = $db->getRow($result)) {
			$data [] = [
				$row['count'],
				$accessibleUsers[$row['smcreatorid']],
				$listView . $this->getSearchParams($accessibleUsers[$row['smcreatorid']], $time)
			];
		}
		return $data;
	}

	public function process(Vtiger_Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$widget = Vtiger_Widget_Model::getInstance($request->get('linkid'), Users_Record_Model::getCurrentUserModel()->getId());
		$time = $request->get('time');
		if (empty($time)) {
			$time['start'] = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
			$time['end'] = date('Y-m-d', mktime(23, 59, 59, date('m') + 1, 0, date('Y')));
			$time['start'] = vtlib\Functions::currentUserDisplayDate($time['start']);
			$time['end'] = vtlib\Functions::currentUserDisplayDate($time['end']);
		}
		$data = $this->getNotificationBySender($time);
		$viewer->assign('DATA', $data);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DTIME', $time);
		$content = $request->get('content');
		if (!empty($content)) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/NotificationsBySender.tpl', $moduleName);
		}
	}
}
