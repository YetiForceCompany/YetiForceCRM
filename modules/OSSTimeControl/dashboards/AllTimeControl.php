<?php
/**
 * Wdiget to show work time
 * @package YetiForce.Dashboard
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class OSSTimeControl_AllTimeControl_Dashboard extends Vtiger_IndexAjax_View
{

	function getSearchParams($assignedto = '', $dateStart, $dateEnd)
	{
		$conditions = [];
		$listSearchParams = [];
		if ($assignedto != '')
			array_push($conditions, ['assigned_user_id', 'e', $assignedto]);
		if (!empty($dateStart) && !empty($dateEnd)) {
			array_push($conditions,['due_date', 'bw', $dateStart . ',' . $dateEnd . '']);
		}
		$listSearchParams[] = $conditions;
		return '&search_params=' . json_encode($listSearchParams);
	}

	public function getWidgetTimeControl($user, $time)
	{
		if (!$time) {
			return array();
		}
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if ($user == 'all') {
			$accessibleUsers = $currentUser->getAccessibleUsers();
			$user = array_keys($accessibleUsers);
		}
		if (!is_array($user)) {
			$accessibleUsers[$user] = Users_Record_Model::getInstanceById($user, 'Users')->getName();
			$user = [$user];
		}
		$db = PearDatabase::getInstance();
		$sql = "SELECT timecontrol_type, color FROM vtiger_timecontrol_type";
		$result = $db->query($sql);
		while ($row = $db->fetch_array($result)) {
			$colors[$row['timecontrol_type']] = $row['color'];
		}
		$module = 'HelpDesk';
		$instance = CRMEntity::getInstance($module);
		$securityParameter = $instance->getUserAccessConditionsQuerySR($module, $currentUser);
		$param[] = 'OSSTimeControl';
		$param = array_merge($param, $user);
		$sql = "SELECT sum_time AS daytime, due_date, timecontrol_type, vtiger_crmentity.smownerid FROM vtiger_osstimecontrol
					INNER JOIN vtiger_crmentity ON vtiger_osstimecontrol.osstimecontrolid = vtiger_crmentity.crmid
					WHERE vtiger_crmentity.setype = ? AND vtiger_crmentity.smownerid IN (" . generateQuestionMarks($user) . ") ";
		if ($securityParameter != '')
			$sql.= $securityParameter;
		$sql .= "AND (vtiger_osstimecontrol.date_start >= ? AND vtiger_osstimecontrol.due_date <= ?) AND vtiger_osstimecontrol.deleted = 0 ";
		$param[] = $time['start'];
		$param[] = $time['end'];
		$result = $db->pquery($sql, $param);
		$timeTypes = [];
		$response = [];
		$numRows = $db->num_rows($result);
		$smOwners = [];
		$counter = 0;
		for ($i = 0; $i < $numRows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$workingTimeByType[vtranslate($row['timecontrol_type'], 'OSSTimeControl')] += $row['daytime'];
			$workingTime[$row['smownerid']][$row['timecontrol_type']] += $row['daytime'];
			if (!array_key_exists($row['timecontrol_type'], $timeTypes)) {
				$timeTypes[$row['timecontrol_type']] = $counter++;
			}
			if (!in_array($row['smownerid'], $smOwners))
				$smOwners[] = $row['smownerid'];
		}
		if ($numRows > 0) {
			$counter = 0;
			$result = [];
			foreach ($workingTime as $timeKey => $timeValue) {
				foreach ($timeTypes as $timeTypeKey => $timeTypeKey) {
					$result[$timeTypeKey]['data'][$counter][0] = $counter;
					$result[$timeTypeKey]['label'] = vtranslate($timeTypeKey, 'OSSTimeControl');
					$result[$timeTypeKey]['color'] = $colors[$timeTypeKey];
					if ($timeValue[$timeTypeKey]) {
						$result[$timeTypeKey]['data'][$counter][1] = $timeValue[$timeTypeKey];
					} else {
						$result[$timeTypeKey]['data'][$counter][1] = 0;
					}
				}
				$counter++;
			}
			$ticks = [];
			foreach ($smOwners as $key => $value) {
				$newArray = [$key, $accessibleUsers[$value]];
				array_push($ticks, $newArray);
			}
			$listViewUrl = 'index.php?module=OSSTimeControl&view=List';
			$counter = 0;
			foreach($ticks as $key => $value){
				$response['links'][$counter][0] = $counter;
				$response['links'][$counter][1] = $listViewUrl . $this->getSearchParams($value[1], $time['start'], $time['end']);
				$counter++;
			}
			$response['legend'] = $workingTimeByType;
			$response['chart'] = $result;
			$response['ticks'] = $ticks;
		}
		return $response;
	}

	public function process(Vtiger_Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$loggedUserId = $currentUser->get('id');
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$linkId = $request->get('linkid');
		$user = $request->get('owner');
		$time = $request->get('time');
		if ($time == NULL) {
			$time['start'] = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
			$time['end'] = date('Y-m-d', mktime(23, 59, 59, date('m') + 1, 0, date('Y')));
		}
		$time['start'] = Vtiger_Functions::currentUserDisplayDate($time['start']);
		$time['end'] = Vtiger_Functions::currentUserDisplayDate($time['end']);
		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		if ($user == NULL){
			$user = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget);
		}
		$data = $this->getWidgetTimeControl($user, $time);
		$TCPModuleModel = Settings_TimeControlProcesses_Module_Model::getCleanInstance();
		$accessibleUsers = $currentUser->getAccessibleUsersForModule($moduleName);
		$accessibleGroups = $currentUser->getAccessibleGroupForModule($moduleName);
		$viewer->assign('TCPMODULE_MODEL', $TCPModuleModel->getConfigInstance());
		$viewer->assign('USERID', $user);
		$viewer->assign('DTIME', $time);
		$viewer->assign('DATA', $data);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('CURRENTUSER', $currentUser);
		$viewer->assign('LOGGEDUSERID', $loggedUserId);
		$viewer->assign('ACCESSIBLE_USERS', $accessibleUsers);
		$viewer->assign('ACCESSIBLE_GROUPS', $accessibleGroups);
		$content = $request->get('content');
		if (!empty($content)) {
			$viewer->view('dashboards/TimeControlContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/AllTimeControl.tpl', $moduleName);
		}
	}
}
