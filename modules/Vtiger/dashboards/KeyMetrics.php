<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_KeyMetrics_Dashboard extends Vtiger_IndexAjax_View
{
	public function process(App\Request $request)
	{
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$linkId = $request->getInteger('linkid');
		$data = $request->getAll();
		$widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());
		$keyMetrics = $this->getKeyMetricsWithCount();
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('KEYMETRICS', $keyMetrics);
		$viewer->assign('DATA', $data);
		if ($request->has('content')) {
			$viewer->view('dashboards/KeyMetricsContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/KeyMetrics.tpl', $moduleName);
		}
	}

	// NOTE: Move this function to appropriate model.
	protected function getKeyMetricsWithCount()
	{
		$metriclists = $this->getMetricList();
		foreach ($metriclists as &$metriclist) {
			$queryGenerator = new \App\QueryGenerator($metriclist['module']);
			$queryGenerator->initForCustomViewById($metriclist['id']);
			$metriclist['count'] = $queryGenerator->createQuery()->count();
		}
		return $metriclists;
	}

	/**
	 * To get the details of a customview entries.
	 *
	 * @returns  $metriclists Array in the following format
	 * $customviewlist []= Array('id'=>custom view id,
	 * 							'name'=>custom view name,
	 * 							'module'=>modulename,
	 * 							'count'=>''
	 * 							)
	 */
	public function getMetricList()
	{
		$privilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$query = (new App\Db\Query())->select(['cvid', 'viewname', 'entitytype', 'userid'])->from('vtiger_customview')
			->where(['setmetrics' => 1]);
		if (!$privilegesModel->isAdminUser()) {
			$query->andWhere([
				'or',
				['userid' => $privilegesModel->getId()],
				['status' => 0],
				['status' => 3],
				['userid' => (new App\Db\Query())->select(['vtiger_user2role.userid'])
					->from('vtiger_user2role')
					->innerJoin('vtiger_users', 'vtiger_users.id = vtiger_user2role.userid')
					->innerJoin('vtiger_role', 'vtiger_role.roleid = vtiger_user2role.roleid')
					->where(['like', 'vtiger_role.parentrole', "{$privilegesModel->getId('parent_role_seq')}::%", false]),
				],
			]);
		}
		$dataReader = $query->orderBy(['entitytype' => SORT_ASC])->createCommand()->query();
		$metriclists = [];
		while ($row = $dataReader->read()) {
			if (\App\Module::isModuleActive($row['entitytype']) && \App\Privilege::isPermitted($row['entitytype'])) {
				$metriclists[] = [
					'id' => $row['cvid'],
					'name' => $row['viewname'],
					'module' => $row['entitytype'],
					'count' => '',
				];
			}
		}
		$dataReader->close();

		return $metriclists;
	}
}
