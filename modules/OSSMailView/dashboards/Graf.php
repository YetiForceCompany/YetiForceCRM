<?php

/**
 * OSSMailView graf dashboard class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class OSSMailView_Graf_Dashboard extends Vtiger_IndexAjax_View
{
	/**
	 * Retrieves css styles that need to loaded in the page.
	 *
	 * @param \App\Request $request - request model
	 *
	 * @return <array> - array of Vtiger_CssScript_Model
	 */
	public function getHeaderCss(\App\Request $request)
	{
		return $this->checkAndConvertCssStyles([
			//Place your widget specific css files here
		]);
	}

	public function getSearchParams($stage, $assignedto, $dates)
	{
		$listSearchParams = [];
		$conditions = [];
		array_push($conditions, ['ossmailview_sendtype', 'e', $stage]);
		if ($assignedto == '') {
			$currenUserModel = Users_Record_Model::getCurrentUserModel();
			$assignedto = $currenUserModel->getId();
		}
		if ($assignedto != 'all') {
			$ownerType = \App\Fields\Owner::getType($assignedto);
			if ($ownerType == 'Users') {
				array_push($conditions, ['assigned_user_id', 'e', \App\Fields\Owner::getUserLabel($assignedto)]);
			} else {
				$groupName = \App\Fields\Owner::getGroupName($assignedto);
				array_push($conditions, ['assigned_user_id', 'e', $groupName]);
			}
		}
		if (!empty($dates)) {
			array_push($conditions, ['createdtime', 'bw', $dates['start'] . ',' . $dates['end']]);
		}
		$listSearchParams[] = $conditions;

		return '&search_params=' . json_encode($listSearchParams);
	}

	public function process(\App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$linkId = $request->getInteger('linkid');
		$owner = $request->getByType('owner', 2);
		$dates = $request->get('dateFilter');

		$today = date('Y-m-d');
		if ($dates === 'Yesterday') {
			$data = strtotime('-1 day', strtotime($today));
			$dateFilter['start'] = date('Y-m-d', $data);
			$dateFilter['end'] = date('Y-m-d', $data);
		} elseif ($dates === 'Current week') {
			if (date('D') == 'Mon') {
				$dateFilter['start'] = date('Y-m-d');
			} else {
				$data = strtotime('last Monday', strtotime($today));
				$dateFilter['start'] = date('Y-m-d', $data);
			}
			$dateFilter['end'] = date('Y-m-d');
		} elseif ($dates === 'Previous week') {
			$data = strtotime('last Monday', strtotime($today));
			if (date('D') != 'Mon') {
				$data = strtotime('last Monday', strtotime(date('Y-m-d', $data)));
			}
			$dateFilter['start'] = date('Y-m-d', $data);
			$data = strtotime('last Sunday', strtotime($today));
			$dateFilter['end'] = date('Y-m-d', $data);
		} elseif ($dates === 'Current month') {
			$dateFilter['start'] = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
			$dateFilter['end'] = $today;
		} elseif ($dates === 'Previous month') {
			$dateFilter['start'] = date('Y-m-d', mktime(0, 0, 0, date('m') - 1, 1, date('Y')));
			$dateFilter['end'] = date('Y-m-d', mktime(23, 59, 59, date('m'), 0, date('Y')));
		} elseif ($dates === 'All') {
			$dateFilter = '';
		} else {
			$dateFilter['start'] = $today;
			$dateFilter['end'] = $today;
		}

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$data = $moduleModel->getMailCount($owner, $dateFilter);
		$listViewUrl = $moduleModel->getListViewUrl();
		$countData = count($data);
		for ($i = 0; $i < $countData; ++$i) {
			$data[$i][] = $listViewUrl . $this->getSearchParams($data[$i][0], $owner, $dateFilter);
		}
		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());

		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('CURRENTUSER', $currentUser);
		$viewer->assign('DATA', $data);
		if ($request->has('content')) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/Graf.tpl', $moduleName);
		}
	}
}
