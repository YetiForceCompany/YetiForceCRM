<?php

/**
 * Wdiget to show work time
 * @package YetiForce.Dashboard
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class OSSTimeControl_AllTimeControl_Dashboard extends Vtiger_IndexAjax_View
{

	public function getSearchParams($assignedto = '', $dateStart, $dateEnd)
	{
		$conditions = [];
		$listSearchParams = [];
		if ($assignedto != '')
			array_push($conditions, ['assigned_user_id', 'e', $assignedto]);
		if (!empty($dateStart) && !empty($dateEnd)) {
			array_push($conditions, ['due_date', 'bw', $dateStart . ',' . $dateEnd . '']);
		}
		$listSearchParams[] = $conditions;
		return '&search_params=' . json_encode($listSearchParams) . '&viewname=All';
	}

	public function getWidgetTimeControl($user, $time)
	{
		if (!$time) {
			return array();
		}
		$timeDatabase['start'] = DateTimeField::convertToDBFormat($time['start']);
		$timeDatabase['end'] = DateTimeField::convertToDBFormat($time['end']);
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if ($user == 'all') {
			$accessibleUsers = \App\Fields\Owner::getInstance(false, $currentUser)->getAccessibleUsers();
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
		$module = 'OSSTimeControl';
		$param[] = $module;
		$param = array_merge($param, $user);
		$query = (new App\Db\Query())->select(['daytime' => 'sum_time', 'due_date', 'timecontrol_type', 'vtiger_crmentity.smownerid'])
			->from('vtiger_osstimecontrol')
			->innerJoin('vtiger_crmentity', 'vtiger_osstimecontrol.osstimecontrolid = vtiger_crmentity.crmid')
			->where(['vtiger_crmentity.setype' => $module, 'vtiger_crmentity.smownerid' => $user]);
		\App\PrivilegeQuery::getConditions($query, $module);
		$query->andWhere([
			'and',
			['>=', 'vtiger_osstimecontrol.due_date', $timeDatabase['start']],
			['<=', 'vtiger_osstimecontrol.due_date', $timeDatabase['end']],
			['vtiger_osstimecontrol.deleted' => 0]
		]);
		$timeTypes = [];
		$response = [];
		$smOwners = [];
		$counter = 0;
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$workingTimeByType[vtranslate($row['timecontrol_type'], 'OSSTimeControl')] += $row['daytime'];
			$workingTime[$row['smownerid']][$row['timecontrol_type']] += $row['daytime'];
			if (!array_key_exists($row['timecontrol_type'], $timeTypes)) {
				$timeTypes[$row['timecontrol_type']] = $counter++;
			}
			if (!in_array($row['smownerid'], $smOwners))
				$smOwners[] = $row['smownerid'];
		}
		if ($dataReader->count() > 0) {
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
			$listViewUrl = 'index.php?module=OSSTimeControl&view=List&viewname=All';
			$counter = 0;
			foreach ($ticks as $key => $value) {
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
		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		if (empty($time)) {
			$time = Settings_WidgetsManagement_Module_Model::getDefaultDate($widget);
			if ($time === false) {
				$time['start'] = vtlib\Functions::currentUserDisplayDateNew();
				$time['end'] = vtlib\Functions::currentUserDisplayDateNew();
			} else {
				$time['start'] = \App\Fields\DateTime::currentUserDisplayDate($time['start']);
				$time['end'] = \App\Fields\DateTime::currentUserDisplayDate($time['end']);
			}
		}
		if (empty($user)) {
			$user = Settings_WidgetsManagement_Module_Model::getDefaultUserId($widget);
		}
		$data = $this->getWidgetTimeControl($user, $time);
		$TCPModuleModel = Settings_TimeControlProcesses_Module_Model::getCleanInstance();
		$accessibleUsers = \App\Fields\Owner::getInstance($moduleName, $currentUser)->getAccessibleUsersForModule();
		$accessibleGroups = \App\Fields\Owner::getInstance($moduleName, $currentUser)->getAccessibleGroupForModule();
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
